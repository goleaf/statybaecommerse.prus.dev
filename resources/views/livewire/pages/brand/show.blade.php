<div>
    <div class="container mx-auto px-4 py-8">
        <!-- Brand Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                {{ $brand->name }}
            </h1>
            
            @if($brand->description)
                <div class="prose max-w-none text-gray-600 dark:text-gray-300">
                    {!! nl2br(e($brand->description)) !!}
                </div>
            @endif
        </div>

        <!-- Brand Products -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">
                {{ __('Products by :brand', ['brand' => $brand->name]) }}
            </h2>

            @if($brand->products()->where('is_visible', true)->whereNotNull('published_at')->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($brand->products()->where('is_visible', true)->whereNotNull('published_at')->latest()->limit(12)->get() as $product)
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow">
                            @if($product->getFirstMediaUrl('images'))
                                <img src="{{ $product->getFirstMediaUrl('images') }}" 
                                     alt="{{ $product->name }}"
                                     class="w-full h-48 object-cover">
                            @else
                                <div class="w-full h-48 bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                            
                            <div class="p-4">
                                <h3 class="font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2">
                                    <a href="{{ route('localized.products.show', ['locale' => app()->getLocale(), 'product' => $product->slug]) }}" 
                                       class="hover:text-blue-600 dark:hover:text-blue-400">
                                        {{ $product->name }}
                                    </a>
                                </h3>
                                
                                <div class="flex items-center justify-between">
                                    <div class="text-lg font-bold text-gray-900 dark:text-white">
                                        {{ number_format($product->price, 2) }} €
                                    </div>
                                    
                                    @if($product->compare_price && $product->compare_price > $product->price)
                                        <div class="text-sm text-gray-500 dark:text-gray-400 line-through">
                                            {{ number_format($product->compare_price, 2) }} €
                                        </div>
                                    @endif
                                </div>
                                
                                @if($product->compare_price && $product->compare_price > $product->price)
                                    <div class="mt-2">
                                        @php
                                            $discount = round((($product->compare_price - $product->price) / $product->compare_price) * 100);
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                            -{{ $discount }}%
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                
                @if($brand->products()->where('is_visible', true)->whereNotNull('published_at')->count() > 12)
                    <div class="mt-8 text-center">
                        <a href="{{ route('localized.products.index', ['locale' => app()->getLocale(), 'brand' => $brand->slug]) }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ __('View all products by :brand', ['brand' => $brand->name]) }}
                        </a>
                    </div>
                @endif
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('No products found') }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('No products are available for this brand yet.') }}</p>
                </div>
            @endif
        </div>

        <!-- Brand Information -->
        @if($brand->website || $brand->description)
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('About :brand', ['brand' => $brand->name]) }}
                </h3>
                
                @if($brand->description)
                    <div class="prose max-w-none text-gray-600 dark:text-gray-300 mb-4">
                        {!! nl2br(e($brand->description)) !!}
                    </div>
                @endif
                
                @if($brand->website)
                    <div>
                        <a href="{{ $brand->website }}" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="inline-flex items-center text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                            {{ __('Visit website') }}
                        </a>
                    </div>
                @endif
            </div>
        @endif

        <!-- Back Button -->
        <div class="mt-8 text-center">
            <a href="{{ route('localized.brands.index', ['locale' => app()->getLocale()]) }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-md transition duration-200">
                <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                {{ __('frontend.buttons.back_to_brands') }}
            </a>
        </div>
    </div>
</div>
