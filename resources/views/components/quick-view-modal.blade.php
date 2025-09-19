@props([
    'product' => null,
    'show' => false,
])

@php
    $product = $product ?? new \App\Models\Product();
@endphp

<div x-data="quickViewModal()" x-show="show" x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
     x-cloak @click="closeModal()" @keydown.escape="closeModal()">

    <div class="bg-white rounded-2xl max-w-6xl w-full max-h-[90vh] overflow-y-auto" @click.stop>
        {{-- Header --}}
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-900">{{ __('Quick View') }}</h2>
            <button @click="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        {{-- Content --}}
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Product Images --}}
                <div class="space-y-4">
                    {{-- Main Image --}}
                    <div class="aspect-w-1 aspect-h-1 bg-gray-100 rounded-xl overflow-hidden">
                        <img x-ref="mainImage" :src="currentImage.url" :alt="currentImage.alt"
                             class="w-full h-96 object-cover">
                    </div>

                    {{-- Thumbnail Images --}}
                    @if ($product->getMedia('images')->count() > 1)
                        <div class="grid grid-cols-4 gap-2">
                            @foreach ($product->getMedia('images') as $index => $image)
                                <button @click="setCurrentImage({{ $index }})"
                                        :class="currentIndex === {{ $index }} ? 'ring-2 ring-blue-500' :
                                            'hover:ring-2 hover:ring-gray-300'"
                                        class="aspect-w-1 aspect-h-1 bg-gray-100 rounded-lg overflow-hidden transition-all duration-200">
                                    <img src="{{ $image->url }}" alt="{{ $product->name }}"
                                         class="w-full h-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Product Details --}}
                <div class="space-y-6">
                    {{-- Product Name --}}
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $product->name }}</h1>
                        @if ($product->brand)
                            <p class="text-lg text-gray-600">{{ $product->brand->name }}</p>
                        @endif
                    </div>

                    {{-- Rating --}}
                    @if ($product->avg_rating > 0)
                        <div class="flex items-center gap-3">
                            <div class="flex items-center">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg class="w-5 h-5 {{ $i <= $product->avg_rating ? 'text-yellow-400' : 'text-gray-300' }}"
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                              d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @endfor
                            </div>
                            <span class="text-sm text-gray-600">
                                {{ number_format($product->avg_rating, 1) }} ({{ $product->reviews_count ?? 0 }}
                                {{ __('reviews') }})
                            </span>
                        </div>
                    @endif

                    {{-- Price --}}
                    <div class="flex items-center gap-4">
                        @if ($product->sale_price && $product->sale_price < $product->price)
                            <span class="text-3xl font-bold text-gray-900">
                                {{ \Illuminate\Support\Number::currency($product->sale_price, current_currency(), app()->getLocale()) }}
                            </span>
                            <span class="text-xl text-gray-500 line-through">
                                {{ \Illuminate\Support\Number::currency($product->price, current_currency(), app()->getLocale()) }}
                            </span>
                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-sm font-medium">
                                {{ __('Sale') }}
                            </span>
                        @else
                            <span class="text-3xl font-bold text-gray-900">
                                {{ \Illuminate\Support\Number::currency($product->price, current_currency(), app()->getLocale()) }}
                            </span>
                        @endif
                    </div>

                    {{-- Stock Status --}}
                    <div class="flex items-center gap-2">
                        @if ($product->stock_quantity > 0)
                            <span class="inline-flex items-center gap-1 text-sm text-green-600 font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('In Stock') }}
                            </span>
                            <span class="text-sm text-gray-600">
                                ({{ $product->stock_quantity }} {{ __('available') }})
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-sm text-red-600 font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                {{ __('Out of Stock') }}
                            </span>
                        @endif
                    </div>

                    {{-- Description --}}
                    @if ($product->description)
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('Description') }}</h3>
                            <p class="text-gray-700 leading-relaxed">{{ $product->description }}</p>
                        </div>
                    @endif

                    {{-- Product Options --}}
                    @if ($product->variants && $product->variants->count() > 0)
                        <div class="space-y-4">
                            @foreach ($product->variants as $variant)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">{{ $variant->name }}</h4>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($variant->options as $option)
                                            <button @click="selectOption('{{ $variant->name }}', '{{ $option->value }}')"
                                                    :class="selectedOptions[
                                                        '{{ $variant->name }}'] === '{{ $option->value }}' ?
                                                        'bg-blue-600 text-white' :
                                                        'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                                    class="px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                                                {{ $option->value }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Quantity --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">{{ __('Quantity') }}</label>
                        <div class="flex items-center gap-2">
                            <button @click="decreaseQuantity()" :disabled="quantity <= 1"
                                    class="w-10 h-10 border border-gray-300 rounded-lg flex items-center justify-center text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4">
                                    </path>
                                </svg>
                            </button>
                            <input type="number" x-model="quantity" min="1"
                                   :max="{{ $product->stock_quantity }}"
                                   class="w-20 h-10 border border-gray-300 rounded-lg text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <button @click="increaseQuantity()" :disabled="quantity >= {{ $product->stock_quantity }}"
                                    class="w-10 h-10 border border-gray-300 rounded-lg flex items-center justify-center text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="space-y-3">
                        <button wire:click="addToCart({{ $product->id }})" @click="addToCart()"
                                :disabled="!canAddToCart"
                                class="w-full btn-gradient py-3 rounded-xl font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!loading">{{ __('Add to Cart') }}</span>
                            <span x-show="loading" class="flex items-center justify-center gap-2">
                                <svg class="animate-spin w-5 h-5" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                    </path>
                                </svg>
                                {{ __('Adding...') }}
                            </span>
                        </button>

                        <div class="grid grid-cols-2 gap-3">
                            <button wire:click="toggleWishlist({{ $product->id }})" @click="toggleWishlist()"
                                    wire:confirm="{{ __('translations.confirm_toggle_wishlist') }}"
                                    class="flex items-center justify-center gap-2 py-3 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-colors duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                    </path>
                                </svg>
                                {{ __('Wishlist') }}
                            </button>

                            <button @click="compareProduct()"
                                    class="flex items-center justify-center gap-2 py-3 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-colors duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                    </path>
                                </svg>
                                {{ __('Compare') }}
                            </button>
                        </div>

                        <a href="{{ route('product.show', $product->slug ?? $product) }}"
                           class="w-full text-center py-3 text-blue-600 hover:text-blue-700 font-medium">
                            {{ __('View Full Details') }}
                        </a>
                    </div>

                    {{-- Product Features --}}
                    @if ($product->features && $product->features->count() > 0)
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">{{ __('Key Features') }}</h3>
                            <ul class="space-y-2">
                                @foreach ($product->features as $feature)
                                    <li class="flex items-start gap-2">
                                        <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none"
                                             stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span class="text-gray-700">{{ $feature->name }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Shipping Info --}}
                    <div class="bg-gray-50 rounded-xl p-4">
                        <h4 class="font-semibold text-gray-900 mb-2">{{ __('Shipping Information') }}</h4>
                        <div class="space-y-2 text-sm text-gray-600">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('Free shipping on orders over €50') }}
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('2-3 business days delivery') }}
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('30-day return policy') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function quickViewModal() {
        return {
            show: {{ $show ? 'true' : 'false' }},
            currentIndex: 0,
            quantity: 1,
            loading: false,
            selectedOptions: {},

            get images() {
                return {{ $product->getMedia('images')->map(function ($img) {return ['url' => $img->url, 'alt' => $product->name];})->toJson() }};
            },

            get currentImage() {
                return this.images[this.currentIndex] || this.images[0];
            },

            get canAddToCart() {
                return {{ $product->stock_quantity }} > 0 && this.quantity > 0;
            },

            setCurrentImage(index) {
                this.currentIndex = index;
            },

            increaseQuantity() {
                if (this.quantity < {{ $product->stock_quantity }}) {
                    this.quantity++;
                }
            },

            decreaseQuantity() {
                if (this.quantity > 1) {
                    this.quantity--;
                }
            },

            selectOption(variant, value) {
                this.selectedOptions[variant] = value;
            },

            async addToCart() {
                if (!this.canAddToCart) return;

                this.loading = true;

                try {
                    const response = await fetch('/cart/add', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            product_id: {{ $product->id }},
                            quantity: this.quantity,
                            options: this.selectedOptions
                        })
                    });

                    if (response.ok) {
                        this.showNotification('{{ __('Product added to cart successfully!') }}', 'success');
                        this.closeModal();
                    } else {
                        this.showNotification('{{ __('Failed to add product to cart') }}', 'error');
                    }
                } catch (error) {
                    this.showNotification('{{ __('Network error. Please try again.') }}', 'error');
                } finally {
                    this.loading = false;
                }
            },

            async toggleWishlist() {
                try {
                    const response = await fetch('/wishlist/toggle', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            product_id: {{ $product->id }}
                        })
                    });

                    if (response.ok) {
                        const data = await response.json();
                        this.showNotification(data.message, 'success');
                    }
                } catch (error) {
                    this.showNotification('{{ __('Network error. Please try again.') }}', 'error');
                }
            },

            compareProduct() {
                const url = new URL(window.location);
                url.searchParams.append('compare[]', {{ $product->id }});
                window.location.href = url.toString();
            },

            closeModal() {
                this.show = false;
                document.body.style.overflow = '';
            },

            showNotification(message, type) {
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 z-50 p-4 rounded-xl shadow-large ${
                type === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200'
            }`;
                notification.innerHTML = `
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 ${type === 'success' ? 'text-green-600' : 'text-red-600'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>${message}</span>
                </div>
            `;

                document.body.appendChild(notification);

                setTimeout(() => {
                    notification.remove();
                }, 3000);
            }
        }
    }
</script>
