import './bootstrap';
import './shared/utilities.js';
// Register Alpine data factories that keep search/autocomplete widgets CSP-compliant.
import './alpine/search-components.js';
// Bind cart badge helpers that avoid inline Alpine expressions.
import './alpine/cart.js';
// import '../../vendor/shopper/framework/resources/js/index.js'; // Temporarily disabled
// Local fonts are now loaded via CSS @font-face declarations

// -----------------------------------------------------------------------------
// CSP-friendly frontend enhancements
// -----------------------------------------------------------------------------

// Reusable class names keep JavaScript expressive while delegating presentation
// to static styles that satisfy the strict Content Security Policy requirements.
const ANIMATE_CLASS = 'csp-animate';
const ANIMATE_VISIBLE_CLASS = 'csp-animate--visible';
const CARD_BASE_CLASS = 'csp-interactive-card';
const CARD_ACTIVE_CLASS = 'csp-interactive-card--active';
const BUTTON_BASE_CLASS = 'csp-interactive-button';
const BUTTON_HOVER_CLASS = 'csp-button--hover';
const BUTTON_PRESSED_CLASS = 'csp-button--pressed';
const NOTIFICATION_BASE_CLASS = 'csp-notification';
const NOTIFICATION_VISIBLE_CLASS = 'csp-notification--visible';

document.addEventListener('DOMContentLoaded', () => {
    // Initialise core UX behaviours once the DOM is ready.
    initializeScrollAnimations();
    initializeEnhancedInteractions();
    initializeCartNotifications();
    initializeSearchEnhancements();
    initializeLoadingStates();
    initializeThemeSystem();
});

// Scroll-triggered animations rely on class toggles instead of inline styles so
// CSP rules remain satisfied on every page.
function initializeScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px',
    };

    if (!('IntersectionObserver' in window)) {
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            entry.target.classList.add(ANIMATE_VISIBLE_CLASS);
        });
    }, observerOptions);

    document.querySelectorAll('.animate-on-scroll').forEach((element) => {
        element.classList.add(ANIMATE_CLASS);
        observer.observe(element);
    });
}

// Hover and focus feedback for cards/buttons is now expressed purely through
// CSS class toggles so no inline style mutations are required.
function initializeEnhancedInteractions() {
    document.querySelectorAll('.product-card, .card-hover').forEach((card) => {
        card.classList.add(CARD_BASE_CLASS);

        card.addEventListener('mouseenter', () => {
            card.classList.add(CARD_ACTIVE_CLASS);
        });

        card.addEventListener('mouseleave', () => {
            card.classList.remove(CARD_ACTIVE_CLASS);
        });
    });

    document.querySelectorAll('.btn-gradient, .btn-primary').forEach((button) => {
        button.classList.add(BUTTON_BASE_CLASS);

        const reset = () => {
            button.classList.remove(BUTTON_HOVER_CLASS);
            button.classList.remove(BUTTON_PRESSED_CLASS);
        };

        button.addEventListener('mouseenter', () => {
            button.classList.add(BUTTON_HOVER_CLASS);
        });

        button.addEventListener('mouseleave', reset);
        button.addEventListener('blur', reset);

        button.addEventListener('mousedown', () => {
            button.classList.add(BUTTON_PRESSED_CLASS);
        });

        button.addEventListener('mouseup', () => {
            button.classList.remove(BUTTON_PRESSED_CLASS);
        });
    });
}

// Enhanced cart notifications with modern design
function initializeCartNotifications() {
    window.addEventListener('cart:added', function (e) {
        createNotification({
            type: 'success',
            title: 'Product Added!',
            message: `${e.detail.product} has been added to your cart`,
            duration: 4000,
        });
    });

    window.addEventListener('cart:removed', function (e) {
        createNotification({
            type: 'info',
            title: 'Product Removed',
            message: `${e.detail.product} has been removed from your cart`,
            duration: 3000,
        });
    });

    window.addEventListener('cart:updated', function (e) {
        createNotification({
            type: 'success',
            title: 'Cart Updated',
            message: 'Your cart has been updated successfully',
            duration: 2000,
        });
    });
}

// Modern notification system
function createNotification({ type = 'info', title, message, duration = 3000 }) {
    const notification = document.createElement('div');
    notification.classList.add(NOTIFICATION_BASE_CLASS, `csp-notification--${type}`);

    const iconWrapper = document.createElement('div');
    iconWrapper.classList.add('csp-notification__icon');
    iconWrapper.innerHTML = getNotificationIcon(type);

    const contentWrapper = document.createElement('div');
    contentWrapper.classList.add('csp-notification__content');

    if (title) {
        const titleElement = document.createElement('h4');
        titleElement.classList.add('csp-notification__title');
        titleElement.textContent = title;
        contentWrapper.appendChild(titleElement);
    }

    const messageElement = document.createElement('p');
    messageElement.classList.add('csp-notification__message');
    messageElement.textContent = message;
    contentWrapper.appendChild(messageElement);

    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.classList.add('csp-notification__close');
    closeButton.setAttribute('aria-label', 'Dismiss notification');
    closeButton.innerHTML = `
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
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
}

// Centralised helper to close and remove notifications without inline events.
function dismissNotification(notification) {
    if (!notification?.classList) {
        return;
    }

    notification.classList.remove(NOTIFICATION_VISIBLE_CLASS);
    window.setTimeout(() => notification.remove(), 250);
}

function getNotificationIcon(type) {
    const icons = {
        success: `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        `,
        error: `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        `,
        info: `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        `,
    };

    return icons[type] || icons.info;
}

// Enhanced search functionality
function initializeSearchEnhancements() {
    const searchInputs = document.querySelectorAll(
        'input[type="search"], input[placeholder*="search" i]',
    );

    searchInputs.forEach((input) => {
        // Add search icon animation
        input.addEventListener('focus', function () {
            this.parentElement.classList.add('ring-2', 'ring-primary-500', 'ring-offset-2');
        });

        input.addEventListener('blur', function () {
            this.parentElement.classList.remove('ring-2', 'ring-primary-500', 'ring-offset-2');
        });

        // Add search suggestions (if needed)
        input.addEventListener(
            'input',
            debounce(function (e) {
                const query = e.target.value;
                if (query.length > 2) {
                    // Implement search suggestions here
                    console.log('Searching for:', query);
                }
            }, 300),
        );
    });
}

// Loading states and skeleton screens
function initializeLoadingStates() {
    // Add loading states to forms
    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', function () {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Loading...
                `;
            }
        });
    });
}

// Theme system (for future dark mode support)
function initializeThemeSystem() {
    // Check for saved theme preference or default to light mode
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);

    // Theme toggle functionality (if theme toggle exists)
    const themeToggle = document.querySelector('[data-theme-toggle]');
    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';

            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });
    }
}

// Utility functions
function debounce(func, wait) {
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

// Smooth scroll to element
function smoothScrollTo(element, offset = 0) {
    const top = element?.offsetTop ?? 0;
    window.scrollTo({
        top: Math.max(top - offset, 0),
        behavior: 'smooth',
    });
}
// Make functions globally available
window.smoothScrollTo = smoothScrollTo;
window.createNotification = createNotification;
