@props([
    'product',
    'showBadge' => true,
    'showBrand' => true,
    'showRating' => true,
    'showStock' => true,
    'showAddToCart' => true,
    'showQuickView' => true,
    'showWishlist' => true,
    'showCompare' => true,
])

@php
    $product = $product ?? new \App\Models\Product();
    $imageUrl = $product->getFirstMediaUrl('images', 'image-md') ?: $product->getFirstMediaUrl('images');
    $isNew = $product->created_at && $product->created_at->diffInDays() < 30;
    $isOnSale = $product->sale_price && $product->sale_price < $product->price;
    $discountPercentage = $isOnSale ? round((($product->price - $product->sale_price) / $product->price) * 100) : 0;
@endphp

<div
     class="group bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 hover:border-blue-400 dark:hover:border-blue-500 transition-all duration-300 overflow-hidden shadow-sm hover:shadow-xl dark:hover:shadow-2xl relative transform hover:-translate-y-1">
    <!-- Enhanced gradient overlay -->
    <div
         class="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-purple-500/5 to-pink-500/5 opacity-0 group-hover:opacity-100 transition-all duration-500 rounded-2xl z-0 pointer-events-none">
    </div>

    <!-- Glassmorphism effect -->
    <div
         class="absolute inset-0 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-2xl z-0 pointer-events-none">
    </div>
    {{-- Enhanced Product Image with modern effects --}}
    <div
         class="aspect-w-1 aspect-h-1 bg-gradient-to-br from-gray-100 dark:from-gray-700 to-gray-200 dark:to-gray-600 relative overflow-hidden rounded-t-2xl">
        @if ($imageUrl)
            <img src="{{ $imageUrl }}"
                 alt="{{ $product->name }}"
                 class="w-full h-64 object-cover group-hover:scale-110 transition-transform duration-700 ease-out"
                 loading="lazy">
            <!-- Enhanced image overlay with parallax effect -->
            <div
                 class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-all duration-500">
            </div>
            <!-- Shine effect -->
            <div
                 class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000 ease-out">
            </div>
        @else
            <div
                 class="w-full h-64 bg-gradient-to-br from-gray-100 dark:from-gray-700 to-gray-200 dark:to-gray-600 flex items-center justify-center">
                <svg class="w-16 h-16 text-gray-400 dark:text-gray-500 group-hover:text-blue-500 dark:group-hover:text-blue-400 transition-colors duration-300"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        @endif

        {{-- Enhanced Badges with modern design --}}
        @if ($showBadge)
            <div class="absolute top-4 left-4 flex flex-col gap-2 z-30">
                @if ($isNew)
                    <span
                          class="bg-gradient-to-r from-emerald-500 to-emerald-600 text-white px-3 py-1.5 rounded-full text-xs font-bold shadow-lg backdrop-blur-sm border border-white/20 animate-pulse">
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                      d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            {{ __('New') }}
                        </span>
                    </span>
                @endif
                @if ($isOnSale)
                    <span
                          class="bg-gradient-to-r from-red-500 to-pink-600 text-white px-3 py-1.5 rounded-full text-xs font-bold shadow-lg backdrop-blur-sm border border-white/20">
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                      d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732L14.146 12.8l-1.179 4.456a1 1 0 01-1.856.416L9.5 15.134 6.146 13.2a1 1 0 010-1.732L9.5 9.134l1.179-4.456A1 1 0 0112 2z"
                                      clip-rule="evenodd" />
                            </svg>
                            -{{ $discountPercentage }}%
                        </span>
                    </span>
                @endif
            </div>
        @endif

        {{-- Enhanced Brand Badge --}}
        @if ($showBrand && $product->brand)
            <div
                 class="absolute top-4 right-4 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md text-gray-700 dark:text-gray-200 px-3 py-1.5 rounded-xl text-xs font-semibold shadow-lg border border-white/20 dark:border-gray-600/20 z-30">
                {{ $product->brand->name }}
            </div>
        @endif

        {{-- Enhanced Quick Actions with modern design --}}
        <div
             class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-all duration-500 flex items-center justify-center z-20">
            <div class="flex gap-3 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                @if ($showQuickView)
                    <button class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-md text-gray-700 dark:text-gray-200 p-3 rounded-full shadow-lg hover:bg-white dark:hover:bg-gray-700 hover:scale-110 transition-all duration-200 border border-white/20 dark:border-gray-600/20"
                            title="{{ __('Quick View') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                            </path>
                        </svg>
                    </button>
                @endif
                @if ($showWishlist)
                    <button class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-md text-gray-700 dark:text-gray-200 p-3 rounded-full shadow-lg hover:bg-white dark:hover:bg-gray-700 hover:scale-110 transition-all duration-200 border border-white/20 dark:border-gray-600/20"
                            title="{{ __('Add to Wishlist') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                            </path>
                        </svg>
                    </button>
                @endif
                @if ($showCompare)
                    <button class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-md text-gray-700 dark:text-gray-200 p-3 rounded-full shadow-lg hover:bg-white dark:hover:bg-gray-700 hover:scale-110 transition-all duration-200 border border-white/20 dark:border-gray-600/20"
                            title="{{ __('Compare') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Enhanced Product Info --}}
    <div class="p-6 dark:bg-gray-800/50">
        {{-- Product Name with enhanced styling --}}
        <h3
            class="font-bold text-gray-900 dark:text-white text-lg mb-3 line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300">
            <a href="{{ route('product.show', $product->slug) }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                {{ $product->name }}
            </a>
        </h3>

        {{-- Enhanced Rating with modern design --}}
        @if ($showRating && $product->reviews_count > 0)
            <div class="flex items-center mb-3">
                <div class="flex items-center bg-yellow-50 dark:bg-yellow-900/20 px-2 py-1 rounded-lg">
                    @for ($i = 1; $i <= 5; $i++)
                        <svg class="w-4 h-4 {{ $i <= $product->avg_rating ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}"
                             fill="currentColor" viewBox="0 0 20 20">
                            <path
                                  d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    @endfor
                    <span
                          class="ml-1 text-sm font-semibold text-yellow-600 dark:text-yellow-400">{{ number_format($product->avg_rating, 1) }}</span>
                </div>
                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">({{ $product->reviews_count }})</span>
            </div>
        @endif

        {{-- Enhanced Price and Stock --}}
        <div class="flex justify-between items-center mb-4">
            <div class="flex flex-col">
                <div class="flex items-center gap-2">
                    @if ($isOnSale)
                        <span
                              class="text-2xl font-bold text-gray-900 dark:text-white">{{ \Illuminate\Support\Number::currency($product->sale_price, current_currency(), app()->getLocale()) }}</span>
                        <span
                              class="text-lg text-gray-500 dark:text-gray-400 line-through">{{ \Illuminate\Support\Number::currency($product->price, current_currency(), app()->getLocale()) }}</span>
                    @else
                        <span
                              class="text-2xl font-bold text-gray-900 dark:text-white">{{ \Illuminate\Support\Number::currency($product->price, current_currency(), app()->getLocale()) }}</span>
                    @endif
                </div>

                @if ($showStock)
                    @if ($product->stock_quantity > 0)
                        <span
                              class="text-sm text-green-600 dark:text-green-400 font-semibold flex items-center gap-1 mt-1 bg-green-50 dark:bg-green-900/20 px-2 py-1 rounded-lg w-fit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5 13l4 4L19 7"></path>
                            </svg>
                            {{ __('In Stock') }}
                        </span>
                    @else
                        <span
                              class="text-sm text-red-600 dark:text-red-400 font-semibold flex items-center gap-1 mt-1 bg-red-50 dark:bg-red-900/20 px-2 py-1 rounded-lg w-fit">
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

        {{-- Enhanced Add to Cart Button --}}
        @if ($showAddToCart)
            <button wire:click="addToCart({{ $product->id }})"
                    @if ($product->stock_quantity <= 0) disabled @endif
                    class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 dark:from-blue-500 dark:to-blue-600 dark:hover:from-blue-600 dark:hover:to-blue-700 text-white text-sm px-6 py-3 rounded-xl font-bold disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl disabled:transform-none">
                <span class="flex items-center justify-center gap-2">
                    {{ __('Add to Cart') }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01">
                        </path>
                    </svg>
                </span>
            </button>
        @endif
    </div>
</div>
