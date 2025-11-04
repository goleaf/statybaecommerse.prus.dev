@php
    use Illuminate\View\ComponentAttributeBag;

    // Avoid repeated media queries: prefer preloaded relations or cached accessors
    $image = method_exists($product, 'getMainImage')
        ? $product->getMainImage('image-lg') ?? $product->getMainImage()
        : ($product->getFirstMediaUrl('images', 'image-lg') ?:
        $product->getFirstMediaUrl('images'));
    $currentPrice =
        $product->sale_price && $product->sale_price < $product->price ? $product->sale_price : $product->price;
    $hasDiscount =
        ($product->sale_price && $product->sale_price < $product->price) ||
        ($product->compare_price && $product->compare_price > $product->price);
    $comparePrice =
        $product->compare_price && $product->compare_price > $currentPrice
            ? $product->compare_price
            : ($product->sale_price && $product->sale_price < $product->price
                ? $product->price
                : null);
    $cardPreset = $preset ?? 'featured';
    $attributes = ($attributes ?? new ComponentAttributeBag())->merge([
        'class' => 'product-card group bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden',
    ]);
@endphp

<article {{ $attributes }} aria-labelledby="product-title-{{ $product->id }}">
    <!-- Product Image Section -->
    <div class="product-card__image relative bg-gray-50 aspect-[16/9] flex items-center justify-center p-4">


        @if ($image)
            <img src="{{ $image }}" alt="{{ $product->name }}"
                 class="product-card__image-main max-h-32 max-w-full object-contain transition-transform duration-500 group-hover:scale-105" 
                 loading="lazy">
        @else
            <div class="product-card__image-placeholder text-center flex flex-col items-center">
                <div class="w-16 h-16 bg-gray-200 flex items-center justify-center mb-3 rounded-lg">
                    <span class="text-2xl font-bold text-gray-600">{{ strtoupper(substr($product->name, 0, 2)) }}</span>
                </div>
                <div class="text-sm text-gray-500">{{ $product->name }}</div>
            </div>
        @endif
        
        <!-- Sale Badge -->
        @if ($hasDiscount)
            <div class="product-card__badge absolute top-3 right-3" style="background: transparent !important;">
                <span class="bg-red-500 !text-white px-3 py-1 rounded-full text-xs font-semibold uppercase shadow-none">
                    {{ __('frontend/home.products.badges.sale') }}
                    @if ($product->discount_percentage > 0)
                        <span class="!text-white">−{{ (int) round($product->discount_percentage) }}%</span>
                    @endif
                </span>
            </div>
        @elseif($cardPreset === 'latest')
            <div class="product-card__badge absolute top-3 right-3" style="background: transparent !important; border: none !important; box-shadow: none !important;">
                <span class="bg-blue-500 !text-white px-3 py-1 rounded-full text-xs font-semibold uppercase shadow-none hover:shadow-none border-0 ring-0 outline-none hover:border-0 hover:ring-0 focus:border-0 focus:ring-0 transition-transform duration-200 group-hover:scale-105" style="border: none !important; box-shadow: none !important;">
                    {{ __('frontend/home.products.badges.new') }}
                </span>
            </div>
        @elseif($cardPreset === 'trending')
            <div class="product-card__badge absolute top-3 right-3" style="background: transparent !important; border: none !important; box-shadow: none !important;">
                <span class="bg-amber-500 !text-white px-3 py-1 rounded-full text-xs font-semibold uppercase shadow-none hover:shadow-none border-0 ring-0 outline-none hover:border-0 hover:ring-0 focus:border-0 focus:ring-0 transition-transform duration-200 group-hover:scale-105" style="border: none !important; box-shadow: none !important;">
                    {{ __('frontend/home.products.badges.popular') }}
                </span>
            </div>
        @endif
    </div>

    <!-- Product Details Section -->
    <div class="product-card__content p-6 space-y-4">
        <!-- Category -->
        {{--
        @if ($product->categories && $product->categories->isNotEmpty())
            <div class="product-card__category text-gray-500 text-sm uppercase tracking-wide">
                {{ $product->categories->first()->name }}
            </div>
        @endif
        --}}

        <!-- Product Title -->
        <h3 id="product-title-{{ $product->id }}" class="product-card__title text-xl font-bold text-dark group-hover:text-dark transition-colors duration-300">
            <a href="{{ route('product.show', $product->slug ?? $product) }}" class="stretched-link">
                {{ $product->name }}
            </a>
        </h3>

        <!-- Product Description -->
        @if ($product->description)
            <p class="product-card__description text-gray-600 text-sm leading-relaxed">
                {{ Str::limit($product->description, 120) }}
            </p>
        @endif

        <!-- Feature Tags -->
        {{--
        @if ($product->categories && $product->categories->count() > 1)
            <div class="product-card__features flex flex-wrap gap-2">
                @foreach ($product->categories->skip(1)->take(3) as $category)
                    <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-medium">
                        {{ $category->name }}
                    </span>
                @endforeach
            </div>
        @endif
        --}}

        <!-- Pricing Section -->
        <div class="product-card__pricing flex items-center justify-between">
            <div class="flex flex-col">
                @if ($comparePrice)
                    <span class="text-gray-400 text-sm line-through">
                        {{ \Illuminate\Support\Number::currency($comparePrice, current_currency(), app()->getLocale()) }}
                    </span>
                @endif
                <span class="product-card__price text-2xl font-bold text-gray-900">
                    {{ \Illuminate\Support\Number::currency($currentPrice, current_currency(), app()->getLocale()) }}
                </span>
            </div>
            
            <!-- Add to Cart Button -->
            <button type="button"
                    wire:click="addToCart({{ $product->id }})"
                    wire:loading.attr="disabled"
                    class="product-card__action bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors duration-200 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                </svg>
                <span class="text-white">{{ __('frontend/home.products.actions.add_to_cart') }}</span>
            </button>
        </div>

        <!-- Reviews and Stock -->
        <div class="product-card__meta flex items-center justify-between text-sm">
            <!-- Reviews -->
            @if ($product->reviews_count > 0)
                <div class="product-card__rating flex items-center gap-1">
                    <div class="flex items-center">
                        <svg class="h-4 w-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <span class="text-gray-600">{{ number_format((float) $product->average_rating, 1) }} ({{ $product->reviews_count }})</span>
                    </div>
                </div>
            @endif

            <!-- Stock Status -->
            <div class="product-card__stock">
                @if ($product->stock_quantity > 0)
                    <span class="text-green-500 font-medium">
                        {{ __('frontend/home.products.stock.in') }}
                    </span>
                @else
                    <span class="text-red-600 font-medium">
                        {{ __('frontend/home.products.stock.out') }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</article>