/**
 * Shared JavaScript utilities for the e-commerce platform.
 * All helpers avoid inline styles so they remain compatible with strict CSP headers.
 */

const NOTIFICATION_BASE_CLASS = 'csp-notification';
const NOTIFICATION_VISIBLE_CLASS = 'csp-notification--visible';
const HIDDEN_CLASS = 'csp-hidden';
const FADE_BASE_CLASS = 'csp-fade';
const FADE_VISIBLE_CLASS = 'csp-fade--visible';

// Notification system
export const notifications = {
    show(type, message, title = null, duration = 5000) {
        const variant = this.resolveVariant(type);
        const notification = document.createElement('div');
        notification.classList.add(NOTIFICATION_BASE_CLASS, `csp-notification--${variant}`);
        notification.setAttribute('role', 'status');
        notification.setAttribute('aria-live', 'polite');

        const iconWrapper = document.createElement('div');
        iconWrapper.classList.add('csp-notification__icon');
        iconWrapper.innerHTML = this.getNotificationIcon(variant);

        const contentWrapper = document.createElement('div');
        contentWrapper.classList.add('csp-notification__content');

        if (title) {
            const heading = document.createElement('h4');
            heading.classList.add('csp-notification__title');
            heading.textContent = title;
            contentWrapper.appendChild(heading);
        }

        const messageParagraph = document.createElement('p');
        messageParagraph.classList.add('csp-notification__message');
        messageParagraph.textContent = message;
        contentWrapper.appendChild(messageParagraph);

        const closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.classList.add('csp-notification__close');
        closeButton.setAttribute('aria-label', 'Dismiss notification');
        closeButton.innerHTML = `
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        `;

        closeButton.addEventListener('click', () => dismissNotification(notification));

        notification.appendChild(iconWrapper);
        notification.appendChild(contentWrapper);
        notification.appendChild(closeButton);

        document.body.appendChild(notification);

        requestAnimationFrame(() => {
            notification.classList.add(NOTIFICATION_VISIBLE_CLASS);
        });

        window.setTimeout(() => dismissNotification(notification), duration);
    },

    resolveVariant(type) {
        return ['success', 'error', 'warning', 'info'].includes(type) ? type : 'info';
    },

    getNotificationIcon(type) {
        const icons = {
            success:
                '<svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
            error: '<svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
            warning:
                '<svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>',
            info: '<svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
        };

        return icons[type] || icons.info;
    },
};

// Cart utilities
export const cart = {
    addAnimation(productName) {
        const notification = document.createElement('div');
        notification.classList.add(NOTIFICATION_BASE_CLASS, 'csp-notification--success');
        notification.textContent = `${productName} added to cart!`;

        document.body.appendChild(notification);
        requestAnimationFrame(() => notification.classList.add(NOTIFICATION_VISIBLE_CLASS));
        window.setTimeout(() => dismissNotification(notification), 3000);
    },

    updateCounter(count) {
        const counters = document.querySelectorAll('[data-cart-count]');
        counters.forEach((counter) => {
            counter.textContent = count;
            if (counter instanceof HTMLElement) {
                counter.classList.toggle(HIDDEN_CLASS, count <= 0);
            }
        });
    },
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
    },
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

    fadeIn(element, _duration = 300) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        if (!element) {
            return;
        }

        element.classList.add(FADE_BASE_CLASS);
        element.classList.remove(HIDDEN_CLASS);

        requestAnimationFrame(() => {
            element.classList.add(FADE_VISIBLE_CLASS);
        });
    },

    fadeOut(element, _duration = 300) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        if (!element) {
            return;
        }

        element.classList.add(FADE_BASE_CLASS);
        element.classList.remove(FADE_VISIBLE_CLASS);

        const handleTransitionEnd = () => {
            element.classList.add(HIDDEN_CLASS);
            element.removeEventListener('transitionend', handleTransitionEnd);
        };

        element.addEventListener('transitionend', handleTransitionEnd);
    },
};

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
    },
};

// Global initialization
document.addEventListener('DOMContentLoaded', function () {
    // Initialize smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            ui.smoothScroll(this.getAttribute('href'));
        });
    });

    // Initialize cart animations
    window.addEventListener('cart:updated', function (e) {
        if (e.detail.product) {
            cart.addAnimation(e.detail.product);
        }
        if (e.detail.count !== undefined) {
            cart.updateCounter(e.detail.count);
        }
    });

    // Initialize notifications
    window.addEventListener('notify', function (e) {
        notifications.show(e.detail.type, e.detail.message, e.detail.title);
    });
});
