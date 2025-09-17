{{-- Notification Container --}}
<div id="notifications" class="fixed top-4 right-4 z-50 space-y-2" aria-live="polite" aria-label="{{ __('Notifications') }}"></div>

{{-- Notification Handler Script --}}
<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('notify', (event) => {
        const notification = event[0] || event;
        showNotification(notification.type, notification.message, notification.title);
    });

    Livewire.on('cart-updated', () => {
        updateCartCount();
    });
});

function showNotification(type, message, title = '') {
    const container = document.getElementById('notifications');
    if (!container) return;
    
    const notification = document.createElement('div');

    const colors = {
        success: 'bg-green-50 border-green-200 text-green-800 dark:bg-green-900/20 dark:border-green-800 dark:text-green-200',
        error: 'bg-red-50 border-red-200 text-red-800 dark:bg-red-900/20 dark:border-red-800 dark:text-red-200',
        warning: 'bg-yellow-50 border-yellow-200 text-yellow-800 dark:bg-yellow-900/20 dark:border-yellow-800 dark:text-yellow-200',
        info: 'bg-blue-50 border-blue-200 text-blue-800 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-200'
    };

    notification.className =
        `max-w-sm w-full ${colors[type] || colors.info} border rounded-lg shadow-lg p-4 transform transition-all duration-300 translate-x-full`;
    notification.innerHTML = `
        <div class="flex">
            <div class="flex-1">
                ${title ? `<div class="font-medium text-sm">${title}</div>` : ''}
                <div class="text-sm ${title ? 'mt-1' : ''}">${message}</div>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-400" aria-label="{{ __('Close notification') }}">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `;

    container.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);

    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 300);
    }, 5000);
}

function updateCartCount() {
    // Cart count update logic can be implemented here
    // This would typically fetch the current cart count and update the UI
}
</script>
