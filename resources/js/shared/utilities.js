/**
 * Shared JavaScript utilities for the e-commerce platform
 */

// Notification system
export const notifications = {
    show(type, message, title = null, duration = 5000) {
        const notification = document.createElement('div');
        notification.className = this.getNotificationClasses(type);
        
        const icon = this.getNotificationIcon(type);
        const titleHtml = title ? `<h4 class="font-medium">${title}</h4>` : '';
        
        notification.innerHTML = `
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    ${icon}
                </div>
                <div class="ml-3 flex-1">
                    ${titleHtml}
                    <p class="${title ? 'mt-1 ' : ''}text-sm">${message}</p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 flex-shrink-0">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove after duration
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, duration);
    },

    getNotificationClasses(type) {
        const baseClasses = 'fixed top-4 right-4 z-50 max-w-sm w-full bg-white rounded-lg shadow-lg border p-4 transform translate-x-full transition-transform duration-300';
        
        const typeClasses = {
            success: 'border-green-200 bg-green-50 text-green-800',
            error: 'border-red-200 bg-red-50 text-red-800',
            warning: 'border-yellow-200 bg-yellow-50 text-yellow-800',
            info: 'border-blue-200 bg-blue-50 text-blue-800'
        };

        return `${baseClasses} ${typeClasses[type] || typeClasses.info}`;
    },

    getNotificationIcon(type) {
        const icons = {
            success: '<svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
            error: '<svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
            warning: '<svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>',
            info: '<svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
        };

        return icons[type] || icons.info;
    }
};

// Cart utilities
export const cart = {
    addAnimation(productName) {
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300';
        notification.textContent = `${productName} added to cart!`;
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => notification.classList.remove('translate-x-full'), 100);
        
        // Animate out and remove
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    },

    updateCounter(count) {
        const counters = document.querySelectorAll('[data-cart-count]');
        counters.forEach(counter => {
            counter.textContent = count;
            counter.style.display = count > 0 ? 'inline' : 'none';
        });
    }
};

// Form utilities
export const forms = {
    validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },

    validatePhone(phone) {
        // Lithuanian phone number validation
        const cleaned = phone.replace(/[^0-9+]/g, '');
        return /^(\+370|370|8)[0-9]{8}$/.test(cleaned);
    },

    formatPhone(phone) {
        const cleaned = phone.replace(/[^0-9+]/g, '');
        
        if (/^(\+370|370)([0-9]{8})$/.test(cleaned)) {
            const number = cleaned.replace(/^(\+370|370)/, '');
            return `+370 ${number.substr(0, 3)} ${number.substr(3, 2)} ${number.substr(5)}`;
        }
        
        if (/^8([0-9]{8})$/.test(cleaned)) {
            const number = cleaned.substr(1);
            return `+370 ${number.substr(0, 3)} ${number.substr(3, 2)} ${number.substr(5)}`;
        }
        
        return phone;
    },

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};

// UI utilities
export const ui = {
    smoothScroll(target) {
        const element = document.querySelector(target);
        if (element) {
            element.scrollIntoView({ behavior: 'smooth' });
        }
    },

    toggleClass(element, className) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        if (element) {
            element.classList.toggle(className);
        }
    },

    fadeIn(element, duration = 300) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        if (element) {
            element.style.opacity = '0';
            element.style.display = 'block';
            
            const start = performance.now();
            const animate = (currentTime) => {
                const elapsed = currentTime - start;
                const progress = Math.min(elapsed / duration, 1);
                element.style.opacity = progress;
                
                if (progress < 1) {
                    requestAnimationFrame(animate);
                }
            };
            requestAnimationFrame(animate);
        }
    },

    fadeOut(element, duration = 300) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        if (element) {
            const start = performance.now();
            const initialOpacity = parseFloat(getComputedStyle(element).opacity);
            
            const animate = (currentTime) => {
                const elapsed = currentTime - start;
                const progress = Math.min(elapsed / duration, 1);
                element.style.opacity = initialOpacity * (1 - progress);
                
                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    element.style.display = 'none';
                }
            };
            requestAnimationFrame(animate);
        }
    }
};

// Price formatting
export const price = {
    format(amount, currency = 'EUR', locale = 'lt') {
        const formatter = new Intl.NumberFormat(locale === 'lt' ? 'lt-LT' : 'en-US', {
            style: 'currency',
            currency: currency,
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
        
        return formatter.format(amount);
    },

    formatCompact(amount, currency = 'EUR', locale = 'lt') {
        if (amount >= 1000000) {
            return this.format(amount / 1000000, currency, locale).replace(/[.,]00/, '') + 'M';
        } else if (amount >= 1000) {
            return this.format(amount / 1000, currency, locale).replace(/[.,]00/, '') + 'K';
        }
        return this.format(amount, currency, locale);
    }
};

// Global initialization
document.addEventListener('DOMContentLoaded', function() {
    // Initialize smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            ui.smoothScroll(this.getAttribute('href'));
        });
    });

    // Initialize cart animations
    window.addEventListener('cart:updated', function(e) {
        if (e.detail.product) {
            cart.addAnimation(e.detail.product);
        }
        if (e.detail.count !== undefined) {
            cart.updateCounter(e.detail.count);
        }
    });

    // Initialize notifications
    window.addEventListener('notify', function(e) {
        notifications.show(e.detail.type, e.detail.message, e.detail.title);
    });
});
