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
            <h2 class="text-2xl font-bold text-gray-900">{{ __('messages.quick_view') }}</h2>
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

                    {{-- Price --}}
                    <div class="flex items-center gap-4">
                        <span class="text-3xl font-bold text-gray-900">
                            {{ \Illuminate\Support\Number::currency($product->price, current_currency(), app()->getLocale()) }}
                        </span>
                    </div>

                    {{-- Stock Status --}}
                    <div class="flex items-center gap-2">
                        @if ($product->stock_quantity > 0)
                            <span class="inline-flex items-center gap-1 text-sm text-green-600 font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('messages.in_stock') }}
                            </span>
                            <span class="text-sm text-gray-600">
                                ({{ $product->stock_quantity }} {{ __('messages.available') }})
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-sm text-red-600 font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                {{ __('messages.out_of_stock') }}
                            </span>
                        @endif
                    </div>

                    {{-- Description --}}
                    @if ($product->description)
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('messages.description') }}</h3>
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
                        <label class="block text-sm font-medium text-gray-900 mb-2">{{ __('messages.quantity') }}</label>
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
                                class="w-full cursor-pointer btn-gradient py-3 rounded-xl font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!loading">{{ __('frontend.cart.add_to_cart') }}</span>
                            <span x-show="loading" class="flex items-center justify-center gap-2">
                                <svg class="animate-spin w-5 h-5" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                    </path>
                                </svg>
                                {{ __('common.adding') }}
                            </span>
                        </button>

                        <div class="grid grid-cols-1 gap-3">
                            <button @click="compareProduct()"
                                    class="flex items-center justify-center gap-2 py-3 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-colors duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                    </path>
                                </svg>
                                {{ __('common.compare') }}
                            </button>
                        </div>

                        <a href="{{ route('localized.products.show', ['locale' => app()->getLocale(), 'product' => $product->trans('slug') ?? $product->slug ?? $product->getKey()]) }}"
                           class="w-full text-center py-3 text-blue-600 hover:text-blue-700 font-medium">
                            {{ __('messages.view_product') }}
                        </a>
                    </div>

                    {{-- Product Features --}}
                    @if ($product->features && $product->features->count() > 0)
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">{{ __('messages.product_specifications') }}</h3>
                            <ul class="space-y-2">
                                @foreach ($product->features as $feature)
                                    @php
                                        $rawLabel = $feature->feature_key ?? $feature->key ?? $feature->name ?? null;
                                        $label = $rawLabel ? \Illuminate\Support\Str::of($rawLabel)->replace(['_', '-'], ' ')->headline() : null;
                                        $value = $feature->feature_value ?? $feature->value ?? null;
                                    @endphp

                                    @if ($label || $value)
                                        <li class="flex items-start gap-2">
                                            <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none"
                                                 stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <span class="text-gray-700">
                                                @if ($label)
                                                    <span class="font-medium text-gray-900">{{ $label }}</span>
                                                @endif

                                                @if ($value)
                                                    <span class="text-gray-600 @if ($label) ml-1 @endif">{{ $value }}</span>
                                                @endif
                                            </span>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Shipping Info --}}
                    <div class="bg-gray-50 rounded-xl p-4">
                        <h4 class="font-semibold text-gray-900 mb-2">{{ __('messages.shipping') }}</h4>
                        <div class="space-y-2 text-sm text-gray-600">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('translations.free_shipping_on_orders_over') }} €50
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('products.page.delivery_eta_2_weeks') }}
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('legal.return_policy') }}
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
                    const response = await fetch('/cart/items', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
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
                        this.showNotification('{{ __('frontend.cart.add_success') }}', 'success');
                        this.closeModal();
                    } else {
                        this.showNotification('{{ __('frontend.cart.add_error') }}', 'error');
                    }
                } catch (error) {
                    this.showNotification('{{ __('frontend.cart.network_error') }}', 'error');
                } finally {
                    this.loading = false;
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
