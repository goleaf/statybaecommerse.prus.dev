@props([
    'class' => '',
    'placeholder' => null,
    'maxResults' => 10,
    'minQueryLength' => 2,
])

<div class="search-module {{ $class }}">
    @livewire('components.live-search', [
        'maxResults' => $maxResults,
        'minQueryLength' => $minQueryLength,
        'placeholder' => $placeholder
    ])
</div>

<style>
.search-module {
    position: relative;
}

/* Custom styles for the search module */
.search-module .live-search {
    width: 100%;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .search-module {
        margin: 0 1rem;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .search-module {
        color-scheme: dark;
    }
}

/* Focus styles */
.search-module input:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Animation for dropdown */
.search-module .search-results {
    animation: slideDown 0.2s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Loading animation */
.search-module .loading-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

/* Hover effects */
.search-module .search-result-item:hover {
    background-color: rgba(249, 250, 251, 0.8);
    transition: background-color 0.15s ease-in-out;
}

/* Type badges */
.search-module .type-badge {
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.125rem 0.5rem;
    border-radius: 0.375rem;
}

.search-module .type-badge.product {
    background-color: rgba(59, 130, 246, 0.1);
    color: rgba(59, 130, 246, 0.8);
}

.search-module .type-badge.category {
    background-color: rgba(34, 197, 94, 0.1);
    color: rgba(34, 197, 94, 0.8);
}

.search-module .type-badge.brand {
    background-color: rgba(168, 85, 247, 0.1);
    color: rgba(168, 85, 247, 0.8);
}

/* Price styling */
.search-module .price {
    font-weight: 600;
    color: rgba(59, 130, 246, 1);
}

/* Image styling */
.search-module .result-image {
    width: 3rem;
    height: 3rem;
    object-fit: cover;
    border-radius: 0.5rem;
    background-color: rgba(243, 244, 246, 1);
}

/* No results styling */
.search-module .no-results {
    text-align: center;
    padding: 2rem 1rem;
    color: rgba(107, 114, 128, 1);
}

/* View all results button */
.search-module .view-all-button {
    border-top: 1px solid rgba(229, 231, 235, 1);
    background-color: rgba(249, 250, 251, 0.5);
    transition: background-color 0.15s ease-in-out;
}

.search-module .view-all-button:hover {
    background-color: rgba(239, 246, 255, 1);
}

/* Accessibility improvements */
.search-module input:focus-visible {
    outline: 2px solid rgba(59, 130, 246, 1);
    outline-offset: 2px;
}

.search-module .search-result-item:focus {
    outline: 2px solid rgba(59, 130, 246, 1);
    outline-offset: -2px;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .search-module {
        border: 2px solid currentColor;
    }
    
    .search-module .type-badge {
        border: 1px solid currentColor;
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .search-module .search-results,
    .search-module .loading-spinner {
        animation: none;
    }
    
    .search-module .search-result-item {
        transition: none;
    }
}
</style>
