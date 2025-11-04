/**
 * Live Notifications JavaScript
 * Handles real-time notification updates using Server-Sent Events
 */

class LiveNotifications {
    constructor() {
        this.eventSource = null;
        this.isConnected = false;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 1000; // 1 second
        this.init();
    }

    init() {
        this.connect();
        this.setupEventListeners();
    }

    connect() {
        if (this.isConnected) {
            return;
        }

        try {
            // Create Server-Sent Events connection
            this.eventSource = new EventSource('/api/notifications/stream');
            
            this.eventSource.onopen = () => {
                console.log('Live notifications connected');
                this.isConnected = true;
                this.reconnectAttempts = 0;
            };

            this.eventSource.onmessage = (event) => {
                try {
                    const data = JSON.parse(event.data);
                    this.handleNotification(data);
                } catch (error) {
                    console.error('Error parsing notification data:', error);
                }
            };

            this.eventSource.onerror = (error) => {
                console.error('Live notifications connection error:', error);
                this.isConnected = false;
                this.handleReconnect();
            };

        } catch (error) {
            console.error('Failed to create SSE connection:', error);
            this.handleReconnect();
        }
    }

    handleReconnect() {
        if (this.reconnectAttempts >= this.maxReconnectAttempts) {
            console.error('Max reconnection attempts reached');
            return;
        }

        this.reconnectAttempts++;
        const delay = this.reconnectDelay * Math.pow(2, this.reconnectAttempts - 1);
        
        console.log(`Reconnecting in ${delay}ms (attempt ${this.reconnectAttempts})`);
        
        setTimeout(() => {
            this.connect();
        }, delay);
    }

    handleNotification(data) {
        // Dispatch custom event for Livewire components to listen to
        window.dispatchEvent(new CustomEvent('new-notification', {
            detail: data
        }));

        // Show browser notification if permission is granted
        if (Notification.permission === 'granted') {
            this.showBrowserNotification(data);
        }
    }

    showBrowserNotification(data) {
        const notification = new Notification(data.title, {
            body: data.message,
            icon: '/images/logo-admin.svg',
            badge: '/images/logo-admin.svg',
            tag: `notification-${data.id || Date.now()}`,
            requireInteraction: false,
            silent: false
        });

        notification.onclick = () => {
            window.focus();
            notification.close();
        };

        // Auto-close after 5 seconds
        setTimeout(() => {
            notification.close();
        }, 5000);
    }

    setupEventListeners() {
        // Request notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }

        // Handle page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                // Page is hidden, keep connection alive
                return;
            } else {
                // Page is visible, refresh notifications
                this.refreshNotifications();
            }
        });

        // Handle window focus
        window.addEventListener('focus', () => {
            this.refreshNotifications();
        });
    }

    refreshNotifications() {
        // Trigger Livewire component refresh
        if (window.Livewire) {
            window.Livewire.dispatch('refreshNotifications');
        }
    }

    disconnect() {
        if (this.eventSource) {
            this.eventSource.close();
            this.isConnected = false;
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Only initialize on admin pages
    if (window.location.pathname.includes('/admin')) {
        window.liveNotifications = new LiveNotifications();
    }
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.liveNotifications) {
        window.liveNotifications.disconnect();
    }
});
