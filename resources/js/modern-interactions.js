// Modern Frontend Interactions and Animations
document.addEventListener('DOMContentLoaded', function() {
    
    // Enhanced scroll animations with Intersection Observer
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);

    // Observe all elements with animate-on-scroll class
    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        observer.observe(el);
    });

    // Enhanced loading overlay with modern animations
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) {
        // Show loading overlay
        window.addEventListener('beforeunload', () => {
            loadingOverlay.classList.remove('opacity-0', 'pointer-events-none');
            loadingOverlay.classList.add('opacity-100');
        });

        // Hide loading overlay when page is loaded
        window.addEventListener('load', () => {
            setTimeout(() => {
                loadingOverlay.classList.add('opacity-0', 'pointer-events-none');
                loadingOverlay.classList.remove('opacity-100');
            }, 500);
        });
    }

    // Enhanced back to top button
    const backToTopButton = document.getElementById('back-to-top');
    if (backToTopButton) {
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.remove('opacity-0', 'pointer-events-none');
                backToTopButton.classList.add('opacity-100');
            } else {
                backToTopButton.classList.add('opacity-0', 'pointer-events-none');
                backToTopButton.classList.remove('opacity-100');
            }
        });

        backToTopButton.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // Enhanced search functionality with modern UX
    const searchInputs = document.querySelectorAll('input[type="search"]');
    searchInputs.forEach(input => {
        let searchTimeout;
        
        input.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Add search suggestions logic here
                const query = e.target.value;
                if (query.length > 2) {
                    // Show search suggestions
                    showSearchSuggestions(query, e.target);
                }
            }, 300);
        });

        input.addEventListener('focus', (e) => {
            e.target.parentElement.classList.add('ring-2', 'ring-blue-500/20');
        });

        input.addEventListener('blur', (e) => {
            e.target.parentElement.classList.remove('ring-2', 'ring-blue-500/20');
        });
    });

    // Modern card hover effects
    const cards = document.querySelectorAll('.card-hover, .product-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-8px) scale(1.02)';
        });

        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Enhanced button interactions
    const buttons = document.querySelectorAll('.btn-gradient, .btn-glass, .btn-floating');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', () => {
            button.style.transform = 'scale(1.05)';
        });

        button.addEventListener('mouseleave', () => {
            button.style.transform = 'scale(1)';
        });

        button.addEventListener('mousedown', () => {
            button.style.transform = 'scale(0.98)';
        });

        button.addEventListener('mouseup', () => {
            button.style.transform = 'scale(1.05)';
        });
    });

    // Modern parallax effect for hero sections
    const parallaxElements = document.querySelectorAll('.parallax');
    if (parallaxElements.length > 0) {
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            
            parallaxElements.forEach(element => {
                element.style.transform = `translateY(${rate}px)`;
            });
        });
    }

    // Enhanced form interactions
    const formInputs = document.querySelectorAll('input, textarea, select');
    formInputs.forEach(input => {
        input.addEventListener('focus', () => {
            input.parentElement.classList.add('ring-2', 'ring-blue-500/20');
        });

        input.addEventListener('blur', () => {
            input.parentElement.classList.remove('ring-2', 'ring-blue-500/20');
        });
    });

    // Modern notification system
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-2xl shadow-large backdrop-blur-xl transition-all duration-300 transform translate-x-full`;
        
        const colors = {
            success: 'bg-green-500/90 text-white',
            error: 'bg-red-500/90 text-white',
            warning: 'bg-yellow-500/90 text-white',
            info: 'bg-blue-500/90 text-white'
        };
        
        notification.className += ` ${colors[type]}`;
        notification.innerHTML = `
            <div class="flex items-center gap-3">
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white/80 hover:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 5000);
    }

    // Make notification function globally available
    window.showNotification = showNotification;

    // Enhanced mobile menu interactions
    const mobileMenuButtons = document.querySelectorAll('[wire\\:click="toggleMobileMenu"]');
    mobileMenuButtons.forEach(button => {
        button.addEventListener('click', () => {
            button.classList.add('animate-pulse');
            setTimeout(() => {
                button.classList.remove('animate-pulse');
            }, 200);
        });
    });

    // Modern image lazy loading with fade-in effect
    const images = document.querySelectorAll('img[loading="lazy"]');
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.style.opacity = '0';
                img.style.transition = 'opacity 0.5s ease-in-out';
                
                img.onload = () => {
                    img.style.opacity = '1';
                };
                
                imageObserver.unobserve(img);
            }
        });
    });

    images.forEach(img => {
        imageObserver.observe(img);
    });

    // Enhanced scroll progress indicator
    const progressBar = document.createElement('div');
    progressBar.className = 'fixed top-0 left-0 w-0 h-1 bg-gradient-to-r from-blue-500 to-purple-500 z-50 transition-all duration-300';
    document.body.appendChild(progressBar);

    window.addEventListener('scroll', () => {
        const scrollTop = window.pageYOffset;
        const docHeight = document.body.scrollHeight - window.innerHeight;
        const scrollPercent = (scrollTop / docHeight) * 100;
        progressBar.style.width = scrollPercent + '%';
    });

    // Modern cursor effects for interactive elements
    const interactiveElements = document.querySelectorAll('a, button, [role="button"]');
    interactiveElements.forEach(element => {
        element.addEventListener('mouseenter', () => {
            document.body.style.cursor = 'pointer';
        });

        element.addEventListener('mouseleave', () => {
            document.body.style.cursor = 'default';
        });
    });

    // Enhanced keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            // Close any open modals or dropdowns
            const openModals = document.querySelectorAll('.modal-open');
            openModals.forEach(modal => {
                modal.classList.remove('modal-open');
            });
        }
    });

    // Modern page transitions
    const links = document.querySelectorAll('a[href^="/"], a[href^="./"]');
    links.forEach(link => {
        link.addEventListener('click', (e) => {
            if (!e.ctrlKey && !e.metaKey) {
                e.preventDefault();
                const href = link.getAttribute('href');
                
                // Show loading overlay
                if (loadingOverlay) {
                    loadingOverlay.classList.remove('opacity-0', 'pointer-events-none');
                    loadingOverlay.classList.add('opacity-100');
                }
                
                // Navigate after a short delay for smooth transition
                setTimeout(() => {
                    window.location.href = href;
                }, 300);
            }
        });
    });

    // Enhanced accessibility features
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Tab') {
            document.body.classList.add('keyboard-navigation');
        }
    });

    document.addEventListener('mousedown', () => {
        document.body.classList.remove('keyboard-navigation');
    });

    // Modern error handling for failed image loads
    const allImages = document.querySelectorAll('img');
    allImages.forEach(img => {
        img.addEventListener('error', () => {
            img.style.display = 'none';
            const placeholder = document.createElement('div');
            placeholder.className = 'w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center';
            placeholder.innerHTML = `
                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            `;
            img.parentNode.replaceChild(placeholder, img);
        });
    });

    console.log('🚀 Modern frontend interactions loaded successfully!');
});

// Utility function for search suggestions
function showSearchSuggestions(query, inputElement) {
    // This would typically make an AJAX request to get search suggestions
    // For now, we'll just show a placeholder
    console.log('Search suggestions for:', query);
}

// Modern utility functions
window.ModernUI = {
    // Smooth scroll to element
    scrollTo: (element, offset = 0) => {
        const target = typeof element === 'string' ? document.querySelector(element) : element;
        if (target) {
            const targetPosition = target.offsetTop - offset;
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        }
    },

    // Show loading state
    showLoading: () => {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.classList.remove('opacity-0', 'pointer-events-none');
            overlay.classList.add('opacity-100');
        }
    },

    // Hide loading state
    hideLoading: () => {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.classList.add('opacity-0', 'pointer-events-none');
            overlay.classList.remove('opacity-100');
        }
    },

    // Animate element
    animate: (element, animation, duration = 500) => {
        element.style.animation = `${animation} ${duration}ms ease-in-out`;
        setTimeout(() => {
            element.style.animation = '';
        }, duration);
    }
};
