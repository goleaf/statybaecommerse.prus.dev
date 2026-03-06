@props([
    'products' => collect(),
    'title' => null,
    'limit' => 4,
    'showTitle' => true,
    'class' => '',
])

@if($products->isNotEmpty())
    <section class="bg-white py-16 {{ $class }}" aria-labelledby="related-products-heading">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if($showTitle)
                <div class="text-center">
                    <h2 id="related-products-heading" class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                        {{ $title ?? __('messages.ecommerce') }}
                    </h2>
                    <p class="mt-4 text-lg text-gray-600">
                        {{ __('messages.ecommerce') }}
                    </p>
                </div>
            @endif

            <div class="mt-12">
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($products->take($limit) as $product)
                        @php($productUrl = route('frontend.products.show', ['product' => $product->trans('slug') ?? $product->slug ?? $product->getKey()]))
                        <div class="group relative">
                            <div class="aspect-h-1 aspect-w-1 w-full overflow-hidden rounded-lg bg-gray-200 lg:aspect-none group-hover:opacity-75 lg:h-80">
                                <a href="{{ $productUrl }}" class="block h-full w-full">
                                    <img 
                                        src="{{ $product->getImageUrl('preview') ?: asset('images/placeholder-product.jpg') }}" 
                                        alt="{{ $product->trans('name') ?? $product->name }}"
                                        class="h-full w-full object-cover object-center lg:h-full lg:w-full"
                                        loading="lazy"
                                    />
                                </a>
                            </div>
                            
                            <div class="mt-4 flex justify-between">
                                <div class="flex-1">
                                    <h3 class="text-sm text-gray-900">
                                        <a href="{{ $productUrl }}" 
                                           class="group-hover:text-indigo-600 transition-colors duration-200">
                                            {{ $product->trans('name') ?? $product->name }}
                                        </a>
                                    </h3>
                                    
                                    @if($product->brand)
                                        <p class="mt-1 text-sm text-gray-500">
                                            {{ $product->brand->trans('name') ?? $product->brand->name }}
                                        </p>
                                    @endif
                                    
                                    @if($product->trans('short_description') ?? $product->short_description)
                                        <p class="mt-1 text-sm text-gray-600 line-clamp-2">
                                            {{ Str::limit($product->trans('short_description') ?? $product->short_description, 80) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="mt-2 flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    @if($product->getPrice())
                                        <span class="text-lg font-semibold text-gray-900">
                                            {{ format_price($product->getPrice()) }}
                                        </span>
                                    @else
                                        <span class="text-lg font-semibold text-gray-900">
                                            {{ format_price($product->price) }}
                                        </span>
                                    @endif
                                </div>
                                
                                @if($product->isInStock())
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                        {{ __('messages.ecommerce') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                        {{ __('messages.ecommerce') }}
                                    </span>
                                @endif
                            </div>
                            
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif
