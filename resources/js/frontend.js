// Enhanced Frontend JavaScript for E-commerce
document.addEventListener('DOMContentLoaded', function () {
    // Initialize all frontend features
    initializeScrollAnimations();
    initializeParallaxEffects();
    initializeEnhancedInteractions();
    initializeCartNotifications();
    initializeSearchEnhancements();
    initializeLoadingStates();
    initializeThemeSystem();
    initializeImageLazyLoading();
    initializeProductQuickView();
    initializeWishlist();
    initializeFilters();
    initializeInfiniteScroll();
    initializeAccessibility();
});

// Scroll-triggered animations with Intersection Observer
function initializeScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');

                // Add stagger effect for multiple elements
                const siblings = entry.target.parentElement.children;
                Array.from(siblings).forEach((sibling, index) => {
                    if (sibling.classList.contains('animate-on-scroll')) {
                        setTimeout(() => {
                            sibling.classList.add('visible');
                        }, index * 100);
                    }
                });
            }
        });
    }, observerOptions);

    // Observe all elements with animate-on-scroll class
    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        observer.observe(el);
    });
}

// Parallax effects for hero sections
function initializeParallaxEffects() {
    const parallaxElements = document.querySelectorAll('.parallax');

    if (parallaxElements.length === 0) return;

    const handleScroll = () => {
        const scrolled = window.pageYOffset;

        parallaxElements.forEach(element => {
            const rate = scrolled * -0.5;
            const rect = element.getBoundingClientRect();

            if (rect.bottom >= 0 && rect.top <= window.innerHeight) {
                element.style.transform = `translateY(${rate}px)`;
            }
        });
    };

    window.addEventListener('scroll', handleScroll, { passive: true });
}

// Enhanced interactions for cards and buttons
function initializeEnhancedInteractions() {
    // Product card hover effects
    document.querySelectorAll('.product-card, .card-hover').forEach(card => {
        card.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-8px) scale(1.02)';
            this.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        });

        card.addEventListener('mouseleave', function () {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Button ripple effect
    document.querySelectorAll('.btn-gradient, .btn-primary').forEach(button => {
        button.addEventListener('click', function (e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s linear;
                pointer-events: none;
            `;

            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);

            setTimeout(() => ripple.remove(), 600);
        });
    });

    // Add ripple animation CSS
    if (!document.querySelector('#ripple-styles')) {
        const style = document.createElement('style');
        style.id = 'ripple-styles';
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
}

// Enhanced cart notifications with modern design
function initializeCartNotifications() {
    window.addEventListener('cart:added', function (e) {
        createNotification({
            type: 'success',
            title: 'Product Added!',
            message: `${e.detail.product} has been added to your cart`,
            duration: 4000
        });
    });

    window.addEventListener('cart:removed', function (e) {
        createNotification({
            type: 'info',
            title: 'Product Removed',
            message: `${e.detail.product} has been removed from your cart`,
            duration: 3000
        });
    });

    window.addEventListener('cart:updated', function (e) {
        createNotification({
            type: 'success',
            title: 'Cart Updated',
            message: 'Your cart has been updated successfully',
            duration: 2000
        });
    });
}

// Modern notification system
function createNotification({ type = 'info', title, message, duration = 3000 }) {
    const notification = document.createElement('div');
    const icons = {
        success: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>`,
        error: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>`,
        info: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>`
    };

    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500'
    };

    notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-4 rounded-xl shadow-large z-50 transform translate-x-full transition-all duration-300 flex items-start gap-3 max-w-sm`;
    notification.innerHTML = `
        <div class="flex-shrink-0 mt-0.5">
            ${icons[type]}
        </div>
        <div class="flex-1">
            <h4 class="font-semibold text-sm">${title}</h4>
            <p class="text-sm opacity-90 mt-1">${message}</p>
        </div>
        <button class="flex-shrink-0 ml-2 opacity-70 hover:opacity-100 transition-opacity" onclick="this.parentElement.remove()">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;

    document.body.appendChild(notification);

    // Animate in
    requestAnimationFrame(() => {
        notification.classList.remove('translate-x-full');
    });

    // Auto remove
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => notification.remove(), 300);
    }, duration);
}

// Enhanced search functionality
function initializeSearchEnhancements() {
    const searchInputs = document.querySelectorAll('input[type="search"], input[placeholder*="search" i]');

    searchInputs.forEach(input => {
        // Add search icon animation
        input.addEventListener('focus', function () {
            this.parentElement.classList.add('ring-2', 'ring-blue-500', 'ring-offset-2');
        });

        input.addEventListener('blur', function () {
            this.parentElement.classList.remove('ring-2', 'ring-blue-500', 'ring-offset-2');
        });

        // Add search suggestions (if needed)
        input.addEventListener('input', debounce(function (e) {
            const query = e.target.value;
            if (query.length > 2) {
                // Implement search suggestions here
                console.log('Searching for:', query);
            }
        }, 300));
    });
}

// Loading states and skeleton screens
function initializeLoadingStates() {
    // Add loading states to forms
    document.querySelectorAll('form').forEach(form => {
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

// Image lazy loading
function initializeImageLazyLoading() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
}

// Product quick view modal
function initializeProductQuickView() {
    document.querySelectorAll('[data-quick-view]').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            // Implement quick view modal here
            console.log('Quick view for product:', productId);
        });
    });
}

// Wishlist functionality
function initializeWishlist() {
    document.querySelectorAll('[data-wishlist-toggle]').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            const isInWishlist = this.classList.contains('in-wishlist');

            if (isInWishlist) {
                this.classList.remove('in-wishlist');
                this.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>';
            } else {
                this.classList.add('in-wishlist');
                this.innerHTML = '<svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>';
            }

            // Send AJAX request to update wishlist
            fetch('/wishlist/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ product_id: productId })
            });
        });
    });
}

// Filter functionality
function initializeFilters() {
    const filterForms = document.querySelectorAll('[data-filter-form]');

    filterForms.forEach(form => {
        form.addEventListener('change', debounce(function () {
            // Implement filter functionality
            console.log('Filter changed');
        }, 300));
    });
}

// Infinite scroll for product listings
function initializeInfiniteScroll() {
    const loadMoreButton = document.querySelector('[data-load-more]');

    if (loadMoreButton) {
        loadMoreButton.addEventListener('click', function () {
            const nextPage = parseInt(this.dataset.page) + 1;
            const url = this.dataset.url;

            fetch(`${url}?page=${nextPage}`)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newProducts = doc.querySelectorAll('.product-card');

                    const container = document.querySelector('[data-products-container]');
                    newProducts.forEach(product => {
                        container.appendChild(product);
                    });

                    this.dataset.page = nextPage;
                });
        });
    }
}

// Accessibility enhancements
function initializeAccessibility() {
    // Skip to content link
    const skipLink = document.querySelector('a[href="#main-content"]');
    if (skipLink) {
        skipLink.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector('#main-content');
            if (target) {
                target.focus();
                target.scrollIntoView();
            }
        });
    }

    // Keyboard navigation for dropdowns
    document.querySelectorAll('[data-dropdown]').forEach(dropdown => {
        const button = dropdown.querySelector('[data-dropdown-toggle]');
        const menu = dropdown.querySelector('[data-dropdown-menu]');

        if (button && menu) {
            button.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    menu.classList.toggle('hidden');
                }
            });
        }
    });
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
    const targetPosition = element.offsetTop - offset;
    window.scrollTo({
        top: targetPosition,
        behavior: 'smooth'
    });
}

// Format currency
function formatCurrency(amount, currency = 'EUR', locale = 'en') {
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency: currency
    }).format(amount);
}

// Make functions globally available
window.smoothScrollTo = smoothScrollTo;
window.createNotification = createNotification;
window.formatCurrency = formatCurrency;

