<?php
use function Livewire\Volt\{layout, title};

layout('components.layouts.templates.frontend');
title(__('users.wishlist'));

?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumbs -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ localized_route('home') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        {{ __('nav.home') }}
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ route('users.dashboard') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">{{ __('users.dashboard') }}</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ __('users.wishlist') }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('users.my_wishlist') }}</h1>
                    <p class="mt-2 text-gray-600">{{ __('users.wishlist_description') }}</p>
                </div>
                @if($wishlistItems->count() > 0)
                    <div class="mt-4 sm:mt-0 flex space-x-3">
                        <button 
                            type="button"
                            onclick="clearWishlist()"
                            class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            {{ __('users.clear_wishlist') }}
                        </button>
                        
                        <button 
                            type="button"
                            onclick="addAllToCart()"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                            </svg>
                            {{ __('users.add_all_to_cart') }}
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Wishlist Items -->
        @if($wishlistItems->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($wishlistItems as $item)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden group hover:shadow-md transition-shadow duration-200">
                        <!-- Product Image -->
                        <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden bg-gray-200 relative">
                            @if($item->productVariant && $item->productVariant->product && $item->productVariant->product->featured_image)
                                <img 
                                    src="{{ Storage::disk('public')->url($item->productVariant->product->featured_image) }}" 
                                    alt="{{ $item->productVariant->product->name }}"
                                    class="h-64 w-full object-cover object-center group-hover:scale-105 transition-transform duration-200"
                                >
                            @else
                                <div class="h-64 w-full flex items-center justify-center">
                                    <svg class="h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                            
                            <!-- Remove from Wishlist Button -->
                            <button 
                                type="button"
                                onclick="removeFromWishlist({{ $item->id }})"
                                class="absolute top-2 right-2 bg-white rounded-full p-2 shadow-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 hover:bg-red-50"
                                title="{{ __('users.remove_from_wishlist') }}"
                            >
                                <svg class="h-4 w-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Product Info -->
                        <div class="p-4">
                            <h3 class="text-sm font-medium text-gray-900 mb-2 line-clamp-2">
                                <a href="{{ $item->productVariant ? localized_route('products.show', $item->productVariant->product) : '#' }}" class="hover:text-blue-600">
                                    {{ $item->productVariant ? $item->productVariant->product->name : $item->product_name }}
                                </a>
                            </h3>
                            
                            @if($item->productVariant && $item->productVariant->name)
                                <p class="text-sm text-gray-500 mb-2">{{ $item->productVariant->name }}</p>
                            @endif

                            <!-- Price -->
                            <div class="mb-3">
                                @if($item->productVariant && $item->productVariant->product)
                                    @if($item->productVariant->product->compare_price && $item->productVariant->product->compare_price > $item->productVariant->product->price)
                                        <div class="flex items-center space-x-2">
                                            <span class="text-lg font-bold text-gray-900">€{{ number_format($item->productVariant->product->price, 2) }}</span>
                                            <span class="text-sm text-gray-500 line-through">€{{ number_format($item->productVariant->product->compare_price, 2) }}</span>
                                        </div>
                                    @else
                                        <span class="text-lg font-bold text-gray-900">€{{ number_format($item->productVariant->product->price, 2) }}</span>
                                    @endif
                                @else
                                    <span class="text-lg font-bold text-gray-900">€{{ number_format($item->price, 2) }}</span>
                                @endif
                            </div>

                            <!-- Stock Status -->
                            @if($item->productVariant && $item->productVariant->product)
                                @if($item->productVariant->product->stock_quantity > 0)
                                    <div class="mb-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ __('users.in_stock') }}
                                        </span>
                                    </div>
                                @else
                                    <div class="mb-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            {{ __('users.out_of_stock') }}
                                        </span>
                                    </div>
                                @endif
                            @endif

                            <!-- Actions -->
                            <div class="space-y-2">
                                @if($item->productVariant && $item->productVariant->product && $item->productVariant->product->stock_quantity > 0)
                                    <button 
                                        type="button"
                                        onclick="addToCart({{ $item->productVariant->id }})"
                                        class="w-full inline-flex justify-center items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                                        </svg>
                                        {{ __('users.add_to_cart') }}
                                    </button>
                                @else
                                    <button 
                                        type="button"
                                        disabled
                                        class="w-full inline-flex justify-center items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-100 cursor-not-allowed"
                                    >
                                        {{ __('users.out_of_stock') }}
                                    </button>
                                @endif
                                
                                <a 
                                    href="{{ $item->productVariant ? localized_route('products.show', $item->productVariant->product) : '#' }}"
                                    class="w-full inline-flex justify-center items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                >
                                    {{ __('users.view_product') }}
                                </a>
                            </div>

                            <!-- Added Date -->
                            <div class="mt-3 pt-3 border-t border-gray-200">
                                <p class="text-xs text-gray-500">
                                    {{ __('users.added_on') }} {{ $item->created_at->format('Y-m-d') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($wishlistItems->hasPages())
                <div class="mt-8">
                    {{ $wishlistItems->links() }}
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('users.wishlist_empty') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('users.wishlist_empty_description') }}</p>
                <div class="mt-6">
                    <a href="{{ localized_route('products.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        {{ __('users.start_shopping') }}
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
// Wishlist Functions
function removeFromWishlist(itemId) {
    if (confirm('{{ __("users.confirm_remove_from_wishlist") }}')) {
        fetch(`{{ route('users.wishlist.remove', '') }}/${itemId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the item from the DOM
                const itemElement = document.querySelector(`[onclick="removeFromWishlist(${itemId})"]`).closest('.bg-white');
                itemElement.remove();
                
                // Show success message
                showNotification('{{ __("users.removed_from_wishlist") }}', 'success');
                
                // Update wishlist count if displayed elsewhere
                updateWishlistCount();
            } else {
                showNotification('{{ __("users.error_removing_item") }}', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('{{ __("users.error_removing_item") }}', 'error');
        });
    }
}

function clearWishlist() {
    if (confirm('{{ __("users.confirm_clear_wishlist") }}')) {
        fetch('{{ route("users.wishlist.clear") }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload the page to show empty state
                window.location.reload();
            } else {
                showNotification('{{ __("users.error_clearing_wishlist") }}', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('{{ __("users.error_clearing_wishlist") }}', 'error');
        });
    }
}

function addToCart(productVariantId, quantity = 1) {
    fetch('{{ route("cart.add") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_variant_id: productVariantId,
            quantity: quantity
        }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('{{ __("users.added_to_cart") }}', 'success');
            updateCartCount();
        } else {
            showNotification(data.message || '{{ __("users.error_adding_to_cart") }}', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('{{ __("users.error_adding_to_cart") }}', 'error');
    });
}

function addAllToCart() {
    const inStockItems = @json($wishlistItems->filter(function($item) {
        return $item->productVariant && $item->productVariant->product && $item->productVariant->product->stock_quantity > 0;
    }));
    
    if (inStockItems.length === 0) {
        showNotification('{{ __("users.no_items_in_stock") }}', 'warning');
        return;
    }
    
    if (confirm(`{{ __("users.confirm_add_all_to_cart") }} ${inStockItems.length} {{ __("users.items") }}?`)) {
        let addedCount = 0;
        let errorCount = 0;
        
        inStockItems.forEach((item, index) => {
            setTimeout(() => {
                addToCart(item.productVariant.id, 1)
                    .then(() => {
                        addedCount++;
                        if (addedCount + errorCount === inStockItems.length) {
                            showNotification(`{{ __("users.added_to_cart_success") }} ${addedCount} {{ __("users.items") }}`, 'success');
                        }
                    })
                    .catch(() => {
                        errorCount++;
                        if (addedCount + errorCount === inStockItems.length) {
                            showNotification(`{{ __("users.added_to_cart_partial") }} ${addedCount}/${inStockItems.length} {{ __("users.items") }}`, 'warning');
                        }
                    });
            }, index * 100); // Stagger requests to avoid overwhelming the server
        });
    }
}

function updateWishlistCount() {
    // Update wishlist count in navigation or other UI elements
    fetch('{{ route("users.wishlist.count") }}')
        .then(response => response.json())
        .then(data => {
            const wishlistCountElements = document.querySelectorAll('.wishlist-count');
            wishlistCountElements.forEach(element => {
                element.textContent = data.count;
            });
        });
}

function updateCartCount() {
    // Update cart count in navigation or other UI elements
    fetch('{{ route("cart.count") }}')
        .then(response => response.json())
        .then(data => {
            const cartCountElements = document.querySelectorAll('.cart-count');
            cartCountElements.forEach(element => {
                element.textContent = data.count;
            });
        });
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden ${
        type === 'success' ? 'border-l-4 border-green-400' :
        type === 'error' ? 'border-l-4 border-red-400' :
        type === 'warning' ? 'border-l-4 border-yellow-400' :
        'border-l-4 border-blue-400'
    }`;
    
    notification.innerHTML = `
        <div class="p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    ${type === 'success' ? 
                        '<svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>' :
                        type === 'error' ?
                        '<svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>' :
                        type === 'warning' ?
                        '<svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>' :
                        '<svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>'
                    }
                </div>
                <div class="ml-3 w-0 flex-1 pt-0.5">
                    <p class="text-sm font-medium text-gray-900">${message}</p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" onclick="this.parentElement.parentElement.parentElement.remove()">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}
</script>
