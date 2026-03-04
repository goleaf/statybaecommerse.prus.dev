<section class="bg-white py-16 {{ $class }}" aria-labelledby="related-products-heading">
    @if($this->relatedProducts->isNotEmpty())
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if($showTitle)
                <div class="text-center">
                    <h2 id="related-products-heading" class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                        {{ $this->sectionTitle }}
                    </h2>
                    <p class="mt-4 text-lg text-gray-600">
                        {{ __('messages.ecommerce') }}
                    </p>
                </div>
            @endif

            <div class="mt-12">
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($this->relatedProducts as $relatedProduct)
                        <div class="group relative">
                            <div class="aspect-h-1 aspect-w-1 w-full overflow-hidden rounded-lg bg-gray-200 lg:aspect-none group-hover:opacity-75 lg:h-80">
                                @if($relatedProduct->getImageUrl('preview'))
                                    <img 
                                        src="{{ $relatedProduct->getImageUrl('preview') }}" 
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
                                        <a href="{{ route('localized.products.show', ['locale' => app()->getLocale(), 'product' => $relatedProduct->trans('slug') ?? $relatedProduct->slug ?? $relatedProduct->getKey()]) }}" 
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
