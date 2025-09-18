@if($this->relatedProducts->isNotEmpty())
    <section class="bg-white py-16 {{ $class }}" aria-labelledby="related-products-heading">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if($showTitle)
                <div class="text-center">
                    <h2 id="related-products-heading" class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                        {{ $this->sectionTitle }}
                    </h2>
                    <p class="mt-4 text-lg text-gray-600">
                        {{ __('ecommerce.related_products_description') }}
                    </p>
                </div>
            @endif

            <div class="mt-12">
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($this->relatedProducts as $relatedProduct)
                        <div class="group relative">
                            <div class="aspect-h-1 aspect-w-1 w-full overflow-hidden rounded-lg bg-gray-200 lg:aspect-none group-hover:opacity-75 lg:h-80">
                                @if($relatedProduct->getFirstMediaUrl('images'))
                                    <img 
                                        src="{{ $relatedProduct->getFirstMediaUrl('images', 'medium') }}" 
                                        alt="{{ $relatedProduct->trans('name') ?? $relatedProduct->name }}"
                                        class="h-full w-full object-cover object-center lg:h-full lg:w-full"
                                        loading="lazy"
                                    />
                                @else
                                    <div class="flex h-full w-full items-center justify-center bg-gray-100">
                                        <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="mt-4 flex justify-between">
                                <div class="flex-1">
                                    <h3 class="text-sm text-gray-900">
                                        <a href="{{ route('products.show', ['locale' => app()->getLocale(), 'product' => $relatedProduct->trans('slug') ?? $relatedProduct->slug]) }}" 
                                           class="group-hover:text-indigo-600 transition-colors duration-200">
                                            <span aria-hidden="true" class="absolute inset-0"></span>
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
                            
                            <div class="mt-2 flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    @if($relatedProduct->getPrice())
                                        <span class="text-lg font-semibold text-gray-900">
                                            {{ format_price($relatedProduct->getPrice()) }}
                                        </span>
                                        @if($relatedProduct->compare_price && $relatedProduct->compare_price > $relatedProduct->getPrice()?->value?->amount)
                                            <span class="text-sm text-gray-500 line-through">
                                                {{ format_price($relatedProduct->compare_price) }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-lg font-semibold text-gray-900">
                                            {{ format_price($relatedProduct->price) }}
                                        </span>
                                        @if($relatedProduct->compare_price && $relatedProduct->compare_price > $relatedProduct->price)
                                            <span class="text-sm text-gray-500 line-through">
                                                {{ format_price($relatedProduct->compare_price) }}
                                            </span>
                                        @endif
                                    @endif
                                </div>
                                
                                @if($relatedProduct->isInStock())
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                        {{ __('ecommerce.in_stock') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                        {{ __('ecommerce.out_of_stock') }}
                                    </span>
                                @endif
                            </div>
                            
                            @if($relatedProduct->average_rating > 0)
                                <div class="mt-2 flex items-center">
                                    <div class="flex items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= floor($relatedProduct->average_rating))
                                                <svg class="h-4 w-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            @else
                                                <svg class="h-4 w-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            @endif
                                        @endfor
                                    </div>
                                    <span class="ml-2 text-sm text-gray-600">
                                        ({{ $relatedProduct->reviews_count }})
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif
