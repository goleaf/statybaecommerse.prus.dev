const CART_COUNT_SELECTOR = '#cart-count';
const CART_COUNT_ATTRIBUTE = '[data-cart-count]';
const NOTIFICATION_CONTAINER_ID = 'notifications';
const NOTIFICATION_BASE_CLASS = 'csp-notification';
const NOTIFICATION_VISIBLE_CLASS = 'csp-notification--visible';
const NOTIFICATION_STACK_CLASS = 'csp-notification-stack';
const HIDDEN_CLASS = 'csp-hidden';

function resolveNotificationHandler() {
    if (typeof window.showNotification === 'function') {
        return window.showNotification;
    }

    return (type, message, title = '') => {
        const container = ensureNotificationContainer();
        const notification = document.createElement('div');
        const variant = ['success', 'error', 'warning', 'info'].includes(type) ? type : 'info';
        notification.classList.add(NOTIFICATION_BASE_CLASS, `csp-notification--${variant}`);

        const content = document.createElement('div');
        content.classList.add('csp-notification__content');

        if (title) {
            const heading = document.createElement('div');
            heading.classList.add('csp-notification__title');
            heading.textContent = title;
            content.appendChild(heading);
        }

        const messageElement = document.createElement('div');
        messageElement.classList.add('csp-notification__message');
        messageElement.textContent = message;
        content.appendChild(messageElement);

        const closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.classList.add('csp-notification__close');
        closeButton.setAttribute('aria-label', 'Close notification');
        closeButton.innerHTML = `
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        `;

        closeButton.addEventListener('click', () => dismissNotification(notification));

        notification.appendChild(content);
        notification.appendChild(closeButton);

        container.appendChild(notification);

        window.requestAnimationFrame(() => {
            notification.classList.add(NOTIFICATION_VISIBLE_CLASS);
        });

        window.setTimeout(() => dismissNotification(notification), 5000);
    };
}

function ensureNotificationContainer() {
    let container = document.getElementById(NOTIFICATION_CONTAINER_ID);

    if (!container) {
        container = document.createElement('div');
        container.id = NOTIFICATION_CONTAINER_ID;
        container.classList.add(NOTIFICATION_STACK_CLASS);
        document.body.appendChild(container);
    }

    return container;
}

const notify = resolveNotificationHandler();

const fallbackUpdateCartCount = () => {
    try {
        const storedCart = window.sessionStorage.getItem('cart');
        const parsedCart = storedCart ? JSON.parse(storedCart) : [];
        // Normalise potential object payloads so legacy keyed snapshots still work.
        const cartItems = Array.isArray(parsedCart) ? parsedCart : Object.values(parsedCart || {});
        const count = cartItems.reduce((total, item) => total + (Number(item.quantity) || 0), 0);

        const badge = document.querySelector(CART_COUNT_SELECTOR);
        if (badge) {
            badge.textContent = String(count);
            if (badge instanceof HTMLElement) {
                badge.classList.toggle(HIDDEN_CLASS, count <= 0);
            }
        }

        document.querySelectorAll(CART_COUNT_ATTRIBUTE).forEach((node) => {
            node.textContent = String(count);
            if (node instanceof HTMLElement) {
                node.classList.toggle(HIDDEN_CLASS, count <= 0);
            }
        });

        // Emit a browser event so Alpine/vanilla listeners stay in sync with Livewire broadcasts.
        window.dispatchEvent(
            new CustomEvent('cart-updated', {
                detail: { quantity: count },
            }),
        );

        return count;
    } catch (error) {
        // Ignore errors (e.g. malformed JSON) to avoid breaking unrelated pages
        if (import.meta.env.DEV) {
            console.debug('Unable to update cart count', error);
        }

        return 0;
    }
};

const updateCartCount =
    typeof window.updateCartCount === 'function' ? window.updateCartCount : fallbackUpdateCartCount;

if (typeof window.updateCartCount !== 'function') {
    window.updateCartCount = updateCartCount;
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

export { updateCartCount };

function dismissNotification(notification) {
    if (!notification?.classList) {
        return;
    }

    notification.classList.remove(NOTIFICATION_VISIBLE_CLASS);
    window.setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 250);
}
