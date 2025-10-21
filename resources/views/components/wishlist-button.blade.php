@props([
    'product' => null,
    'productId' => null,
    'size' => 'md',
    'showText' => false,
    'variant' => 'default', // default, minimal, icon-only
    'variantId' => null,
])

@php
    $productId = $productId ?? ($product ? $product->id : null);
    $variantId = $variantId ?? ($product?->pivot?->variant_id ?? null);
    $defaultWishlist = auth()->check()
        ? auth()->user()->wishlists()->where('is_default', true)->first()
        : null;
    $isInWishlist = $defaultWishlist && $productId
        ? $defaultWishlist
            ->items()
            ->where('product_id', $productId)
            ->when($variantId, fn ($query) => $query->where('variant_id', $variantId))
            ->exists()
        : false;

    $sizes = [
        'sm' => 'w-8 h-8',
        'md' => 'w-10 h-10',
        'lg' => 'w-12 h-12',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<div class="wishlist-button" x-data='wishlistButton(@json([
    "productId" => $productId,
    "variantId" => $variantId,
    "isInWishlist" => $isInWishlist,
    "isAuthenticated" => auth()->check(),
]))'>
    <button @click="toggleWishlist()"
            :disabled="loading"
            class="group relative {{ $sizeClass }} flex items-center justify-center rounded-full transition-all duration-200 transform hover:scale-110"
            :class="{
                'bg-red-50 text-red-600 hover:bg-red-100': isInWishlist,
                'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200 hover:border-gray-300': !isInWishlist
            }"
            :title="isInWishlist ? '{{ __('Remove from Wishlist') }}' : '{{ __('Add to Wishlist') }}'"
            aria-label="isInWishlist ? '{{ __('Remove from Wishlist') }}' : '{{ __('Add to Wishlist') }}'">

        {{-- Heart Icon --}}
        <svg class="w-5 h-5 transition-all duration-200"
             :class="{ 'scale-110': isInWishlist }"
             fill="currentColor"
             viewBox="0 0 20 20">
            <path fill-rule="evenodd"
                  d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                  clip-rule="evenodd" />
        </svg>

        {{-- Loading Spinner --}}
        <svg x-show="loading"
             class="absolute w-4 h-4 animate-spin text-gray-400"
             fill="none"
             stroke="currentColor"
             viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
            </path>
        </svg>

        {{-- Text (if enabled) --}}
        @if ($showText)
            <span class="ml-2 text-sm font-medium"
                  x-text="isInWishlist ? '{{ __('Saved') }}' : '{{ __('Save') }}'"></span>
        @endif

        {{-- Pulse Animation for Add Action --}}
        <div x-show="!isInWishlist && !loading"
             class="absolute inset-0 rounded-full bg-red-500 opacity-0 group-hover:opacity-20 transition-opacity duration-200">
        </div>
    </button>

    {{-- Success/Error Notification --}}
    <div x-show="message"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="fixed top-4 right-4 z-50 p-4 rounded-xl shadow-large"
         :class="messageType === 'success' ? 'bg-green-50 text-green-800 border border-green-200' :
             'bg-red-50 text-red-800 border border-red-200'"
         x-cloak>
        <div class="flex items-center gap-2">
            <svg x-show="messageType === 'success'" class="w-5 h-5 text-green-600" fill="none" stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <svg x-show="messageType === 'error'" class="w-5 h-5 text-red-600" fill="none" stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <span x-text="message"></span>
        </div>
    </div>
</div>

<script>
    function wishlistButton(config) {
        return {
            productId: config.productId,
            variantId: config.variantId,
            isInWishlist: Boolean(config.isInWishlist),
            isAuthenticated: Boolean(config.isAuthenticated),
            loading: false,
            message: '',
            messageType: 'success',

            async toggleWishlist() {
                if (this.loading || !this.productId) {
                    return;
                }

                if (!this.isAuthenticated) {
                    window.location.href = '{{ route('login') }}';
                    return;
                }

                this.loading = true;
                this.message = '';

                const isRemoving = this.isInWishlist;
                const endpoint = isRemoving
                    ? '{{ route('frontend.wishlist.remove') }}'
                    : '{{ route('frontend.wishlist.add') }}';

                const payload = {
                    product_id: this.productId,
                };

                if (this.variantId) {
                    payload.variant_id = this.variantId;
                }

                if (!isRemoving) {
                    payload.quantity = 1;
                }

                try {
                    const response = await fetch(endpoint, {
                        method: isRemoving ? 'DELETE' : 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(payload),
                    });

                    let data = {};

                    try {
                        data = await response.json();
                    } catch (error) {
                        data = {};
                    }

                    if (response.ok) {
                        this.isInWishlist = !isRemoving;
                        this.messageType = 'success';
                        this.message = data.message || (this.isInWishlist
                            ? '{{ __('Added to wishlist!') }}'
                            : '{{ __('Removed from wishlist!') }}');

                        if (data.item && Object.prototype.hasOwnProperty.call(data.item, 'variant_id')) {
                            this.variantId = data.item.variant_id;
                        }

                        if (Object.prototype.hasOwnProperty.call(data, 'wishlist_count')) {
                            this.updateWishlistCount(data.wishlist_count);
                        }
                    } else {
                        this.messageType = 'error';
                        this.message = data.message || '{{ __('Something went wrong. Please try again.') }}';
                    }
                } catch (error) {
                    this.messageType = 'error';
                    this.message = '{{ __('Network error. Please check your connection and try again.') }}';
                } finally {
                    this.loading = false;

                    setTimeout(() => {
                        this.message = '';
                    }, 3000);
                }
            },

            updateWishlistCount(count) {
                const countElement = document.querySelector('[data-wishlist-count]');
                if (countElement) {
                    countElement.textContent = count;
                }
            }
        }
    }
</script>

