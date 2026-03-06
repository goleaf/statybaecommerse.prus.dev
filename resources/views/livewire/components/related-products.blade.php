<section class="bg-white py-16 {{ $class }}" aria-labelledby="related-products-heading">
    @if($this->relatedProducts->isNotEmpty())
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if($showTitle)
                <div class="text-center">
                    <h2 id="related-products-heading" class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                        {{ $title ?: __('translations.related_products') }}
                    </h2>
                    <p class="mt-4 text-lg text-gray-600">
                        {{ __('translations.related_products_description') }}
                    </p>
                </div>
            @endif

            <div class="mt-12">
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($this->relatedProducts as $relatedProduct)
                        @php($productRouteKey = $relatedProduct->trans('slug') ?? $relatedProduct->slug ?? $relatedProduct->getKey())
                        @php(
                            $productUrl = \Illuminate\Support\Facades\Route::has('localized.products.show')
                                ? route('localized.products.show', ['product' => $productRouteKey])
                                : (
                                    \Illuminate\Support\Facades\Route::has('frontend.products.show')
                                        ? route('frontend.products.show', ['product' => $productRouteKey])
                                        : (
                                            \Illuminate\Support\Facades\Route::has('products.show')
                                                ? route('products.show', ['product' => $productRouteKey])
                                                : '#'
                                        )
                                )
                        )
                        <div class="group relative">
                            <div class="aspect-h-1 aspect-w-1 flex w-full items-center justify-center overflow-hidden rounded-lg bg-gray-200 lg:aspect-none group-hover:opacity-75 lg:h-80">
                                <a href="{{ $productUrl }}" class="block h-full w-full">
                                    <img 
                                        src="{{ $relatedProduct->getImageUrl('preview') ?: asset('images/placeholder-product.jpg') }}" 
                                        alt="{{ $relatedProduct->trans('name') ?? $relatedProduct->name }}"
                                        class="h-auto w-auto max-h-full max-w-full object-contain"
                                        loading="lazy"
                                    />
                                </a>
                            </div>
                            
                            <div class="mt-4 flex justify-between">
                                <div class="flex-1">
                                    <h3 class="text-sm text-gray-900">
                                        <a href="{{ $productUrl }}" 
                                           class="group-hover:text-indigo-600 transition-colors duration-200">
                                            {{ $relatedProduct->trans('name') ?? $relatedProduct->name }}
                                        </a>
                                    </h3>
                                    
                                    @if($relatedProduct->brand)
                                        <p class="mt-1 text-sm text-gray-500">
                                            {{ $relatedProduct->brand->trans('name') ?? $relatedProduct->brand->name }}
                                        </p>
                                    @endif
                                    
                                    @if($relatedProduct->trans('short_description') ?? $relatedProduct->short_description)
                                        <p class="mt-1 text-sm text-gray-600 line-clamp-2">
                                            {{ Str::limit($relatedProduct->trans('short_description') ?? $relatedProduct->short_description, 80) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="mt-2 flex items-center">
                                <div class="flex items-center space-x-2">
                                    @if($relatedProduct->getPrice())
                                        <span class="text-lg font-semibold text-gray-900">
                                            {{ format_price($relatedProduct->getPrice()) }}
                                        </span>
                                    @else
                                        <span class="text-lg font-semibold text-gray-900">
                                            {{ format_price($relatedProduct->price) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</section>
