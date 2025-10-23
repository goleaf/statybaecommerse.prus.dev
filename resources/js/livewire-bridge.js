const CART_COUNT_SELECTOR = '#cart-count';
const CART_COUNT_ATTRIBUTE = '[data-cart-count]';
const NOTIFICATION_CONTAINER_ID = 'notifications';

function resolveNotificationHandler() {
    if (typeof window.showNotification === 'function') {
        return window.showNotification;
    }

    return (type, message, title = '') => {
        const container = ensureNotificationContainer();
        const notification = document.createElement('div');
        notification.className = `max-w-sm w-full border rounded-lg shadow-lg p-4 transform transition-all duration-300 translate-x-full ${
            type === 'success'
                ? 'bg-green-50 border-green-200 text-green-800'
                : type === 'error'
                  ? 'bg-red-50 border-red-200 text-red-800'
                  : type === 'warning'
                    ? 'bg-yellow-50 border-yellow-200 text-yellow-800'
                    : 'bg-blue-50 border-blue-200 text-blue-800'
        }`;

        notification.innerHTML = `
            <div class="flex">
                <div class="flex-1">
                    ${title ? `<div class="font-medium text-sm">${title}</div>` : ''}
                    <div class="text-sm ${title ? 'mt-1' : ''}">${message}</div>
                </div>
                <button type="button" class="ml-4 text-gray-400 hover:text-gray-600" aria-label="Close notification">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;

        notification.querySelector('button')?.addEventListener('click', () => {
            notification.remove();
        });

        container.appendChild(notification);

        window.requestAnimationFrame(() => {
            notification.classList.remove('translate-x-full');
        });

        window.setTimeout(() => {
            notification.classList.add('translate-x-full');
            window.setTimeout(() => notification.remove(), 300);
        }, 5000);
    };
}

function ensureNotificationContainer() {
    let container = document.getElementById(NOTIFICATION_CONTAINER_ID);

    if (!container) {
        container = document.createElement('div');
        container.id = NOTIFICATION_CONTAINER_ID;
        container.className = 'fixed top-4 right-4 z-50 space-y-2';
        document.body.appendChild(container);
    }

    return container;
}

const notify = resolveNotificationHandler();

function updateCartCount() {
    try {
        const cartItems = JSON.parse(window.sessionStorage.getItem('cart') || '[]');
        const count = cartItems.reduce((total, item) => total + (Number(item.quantity) || 0), 0);

        const badge = document.querySelector(CART_COUNT_SELECTOR);
        if (badge) {
            badge.textContent = String(count);
            badge.style.display = count > 0 ? 'inline' : 'none';
        }

        document.querySelectorAll(CART_COUNT_ATTRIBUTE).forEach((node) => {
            node.textContent = String(count);
            if (node instanceof HTMLElement) {
                node.style.display = count > 0 ? 'inline' : 'none';
            }
        });
    } catch (error) {
        // Ignore errors (e.g. malformed JSON) to avoid breaking unrelated pages
        if (import.meta.env.DEV) {
            console.debug('Unable to update cart count', error);
        }
    }
}

function registerLivewireListeners() {
    if (typeof window.Livewire === 'undefined' || typeof window.Livewire.on !== 'function') {
        return;
    }

    window.Livewire.on('notify', (event) => {
        const payload = Array.isArray(event) ? event[0] : event;
        if (!payload || typeof payload !== 'object') {
            return;
        }

        notify(payload.type, payload.message, payload.title);
    });

    window.Livewire.on('cart-updated', () => {
        updateCartCount();
    });
}

document.addEventListener('DOMContentLoaded', () => {
    ensureNotificationContainer();
    updateCartCount();
});

document.addEventListener('livewire:init', registerLivewireListeners, { once: true });

if (typeof window.Livewire !== 'undefined') {
    registerLivewireListeners();
}

window.updateCartCount = updateCartCount;
export { updateCartCount };
