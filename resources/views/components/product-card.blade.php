@props([
    'product',
    'showBadge' => true,
    'showBrand' => true,
    'showRating' => true,
    'showStock' => true,
    'showAddToCart' => true,
])

@php
    $product = $product ?? new \App\Models\Product();
    $imageUrl = $product->getFirstMediaUrl('images', 'image-md') ?: $product->getFirstMediaUrl('images');
    $isNew = $product->created_at && $product->created_at->diffInDays() < 30;
    $isOnSale = $product->sale_price && $product->sale_price < $product->price;
@endphp

<div
     class="group bg-white rounded-3xl border border-gray-200 hover:border-blue-300 transition-all duration-300 overflow-hidden shadow-soft hover:shadow-large animate-on-scroll">
    {{-- Product Image --}}
    <div class="aspect-w-1 aspect-h-1 bg-gradient-to-br from-gray-100 to-gray-200 relative overflow-hidden">
        @if ($imageUrl)
            <img src="{{ $imageUrl }}"
                 alt="{{ $product->name }}"
                 class="w-full h-64 object-cover group-hover:scale-105 transition-transform duration-300"
                 loading="lazy">
        @else
            <div class="w-full h-64 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        @endif

        {{-- Badges --}}
        @if ($showBadge)
            <div class="absolute top-4 left-4 flex flex-col gap-2">
                @if ($isNew)
                    <span
                          class="bg-gradient-to-r from-green-500 to-green-600 text-white px-3 py-1.5 rounded-full text-xs font-semibold shadow-soft">
                        {{ __('New') }}
                    </span>
                @endif
                @if ($isOnSale)
                    <span
                          class="bg-gradient-to-r from-red-500 to-red-600 text-white px-3 py-1.5 rounded-full text-xs font-semibold shadow-soft">
                        {{ __('Sale') }}
                    </span>
                @endif
            </div>
        @endif

        {{-- Brand --}}
        @if ($showBrand && $product->brand)
            <div
                 class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm text-gray-700 px-2 py-1 rounded-lg text-xs font-medium shadow-soft">
                {{ $product->brand->name }}
            </div>
        @endif

        {{-- Quick Actions --}}
        <div
             class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-all duration-300 flex items-center justify-center opacity-0 group-hover:opacity-100">
            <div class="flex gap-2">
                <button class="bg-white/90 backdrop-blur-sm text-gray-700 p-2 rounded-full shadow-soft hover:bg-white hover:scale-110 transition-all duration-200"
                        title="{{ __('Quick View') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                        </path>
                    </svg>
                </button>
                <button class="bg-white/90 backdrop-blur-sm text-gray-700 p-2 rounded-full shadow-soft hover:bg-white hover:scale-110 transition-all duration-200"
                        title="{{ __('Add to Wishlist') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                        </path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Product Info --}}
    <div class="p-6">
        {{-- Product Name --}}
        <h3
            class="font-semibold text-gray-900 text-lg mb-3 line-clamp-2 group-hover:text-blue-700 transition-colors duration-300">
            <a href="{{ route('product.show', $product->slug ?? $product) }}" class="hover:text-blue-700">
                {{ $product->name }}
            </a>
        </h3>

        {{-- Rating --}}
        @if ($showRating && $product->reviews_count > 0)
            <div class="flex items-center mb-3">
                <div class="flex items-center">
                    @for ($i = 1; $i <= 5; $i++)
                        <svg class="w-4 h-4 {{ $i <= $product->avg_rating ? 'text-yellow-400' : 'text-gray-300' }}"
                             fill="currentColor" viewBox="0 0 20 20">
                            <path
                                  d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    @endfor
                </div>
                <span class="ml-2 text-sm text-gray-600">({{ $product->reviews_count }})</span>
            </div>
        @endif

        {{-- Price and Stock --}}
        <div class="flex justify-between items-center mb-4">
            <div class="flex flex-col">
                <div class="flex items-center gap-2">
                    @if ($isOnSale)
                        <span
                              class="text-2xl font-bold text-gray-900">{{ \Illuminate\Support\Number::currency($product->sale_price, current_currency(), app()->getLocale()) }}</span>
                        <span
                              class="text-lg text-gray-500 line-through">{{ \Illuminate\Support\Number::currency($product->price, current_currency(), app()->getLocale()) }}</span>
                    @else
                        <span
                              class="text-2xl font-bold text-gray-900">{{ \Illuminate\Support\Number::currency($product->price, current_currency(), app()->getLocale()) }}</span>
                    @endif
                </div>

                @if ($showStock)
                    @if ($product->stock_quantity > 0)
                        <span class="text-sm text-green-600 font-medium flex items-center gap-1 mt-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5 13l4 4L19 7"></path>
                            </svg>
                            {{ __('In Stock') }}
                        </span>
                    @else
                        <span class="text-sm text-red-600 font-medium flex items-center gap-1 mt-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            {{ __('Out of Stock') }}
                        </span>
                    @endif
                @endif
            </div>
        </div>

        {{-- Add to Cart Button --}}
        @if ($showAddToCart)
            <button wire:click="addToCart({{ $product->id }})"
                    @if ($product->stock_quantity <= 0) disabled @endif
                    class="w-full btn-gradient text-sm px-6 py-3 rounded-xl font-semibold disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-105 transition-all duration-300">
                {{ __('Add to Cart') }}
                <svg class="w-4 h-4 ml-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01">
                    </path>
                </svg>
            </button>
        @endif
    </div>
</div>
