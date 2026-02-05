@php
    use Illuminate\Contracts\Support\Arrayable;
    use Illuminate\Support\Number;
    use Illuminate\Support\Str;
    use Illuminate\View\ComponentAttributeBag;

    /** @var array|Arrayable $product */
    $productData = $product instanceof Arrayable ? $product->toArray() : (array) $product;

    $cardPreset = $preset ?? 'featured';
    
    // Brand colors from app.css
    $brandPrimary = '#262523';
    $brandPrimaryLight = '#81817d';
    $brandPrimaryLighter = '#b3b2ae';
    $brandLight = '#e3ebd2';
    $borderColor = '#e2e8f0';
    
    $attributes = ($attributes ?? new ComponentAttributeBag())->merge([
        'class' => 'bg-white rounded-lg shadow-sm overflow-hidden flex flex-col',
        'style' => 'border: 1px solid ' . $borderColor . ';',
    ]);

    foreach ($attributes->getAttributes() as $attributeKey => $attributeValue) {
        if ($attributeValue instanceof \Closure) {
            \Log::error('Closure attribute detected on product card.', [
                'attribute'  => $attributeKey,
                'product_id' => $productData['id'] ?? null,
            ]);
        }
    }

    $imageUrl = $productData['image_url'] ?? null;
    $initials = $productData['initials'] ?? Str::upper(Str::substr($productData['name'] ?? '', 0, 2));
    $name = $productData['name'] ?? '';
    $detailUrl = $productData['detail_url'] ?? '#';
    $brandName = $productData['brand_name'] ?? null;
    $categoryLabels = is_array($productData['category_labels'] ?? null)
        ? $productData['category_labels']
        : [];
    $id = (int) ($productData['id'] ?? 0);

    $price = (float) ($productData['price'] ?? 0.0);

    $stockQuantity = (int) ($productData['stock_quantity'] ?? 0);
    $inStock = $stockQuantity > 0;
    $averageRating = $productData['average_rating'] ?? null;
    $reviewsCount = (int) ($productData['reviews_count'] ?? 0);
    
    // Get first category label for secondary name display
    $secondaryName = !empty($categoryLabels) ? $categoryLabels[0] : null;
    
    // Get short description and strip HTML tags, limit to 80 characters
    $shortDescription = isset($productData['short_description']) && !empty($productData['short_description'])
        ? strip_tags($productData['short_description'])
        : null;
    if ($shortDescription && strlen($shortDescription) > 80) {
        $shortDescription = Str::limit($shortDescription, 80);
    }
@endphp

<article {{ $attributes }} aria-labelledby="product-title-{{ $id }}" class="h-full">
    {{-- Product Image Section --}}
    <div class="relative w-full aspect-[3/2] bg-gray-100 rounded-t-lg overflow-hidden">
        @if (! empty($imageUrl))
            <img src="{{ $imageUrl }}" alt="{{ $name }}"
                 class="w-full h-full object-cover" loading="lazy">
        @else
            <div class="w-full h-full flex items-center justify-center" style="background-color: #f1f5f9;">
                <span class="text-4xl font-semibold" style="color: {{ $brandPrimaryLight }};">{{ $initials }}</span>
            </div>
        @endif

        {{-- Badge in top-right corner --}}
        <div class="absolute top-3 right-3">
            @if($cardPreset === 'latest')
                <span class="inline-block text-white px-3 py-1 rounded-full text-xs font-semibold uppercase" style="background-color: #0ea5e9;">
                    {{ __('frontend.home.products.badges.new') }}
                </span>
            @elseif($cardPreset === 'trending')
                <span class="inline-block bg-amber-500 text-white px-3 py-1 rounded-full text-xs font-semibold uppercase">
                    {{ __('frontend.home.products.badges.popular') }}
                </span>
            @endif
        </div>
    </div>

    {{-- Product Info Section --}}
    <div class="flex flex-col flex-1 p-4">
        {{-- Secondary name/category (centered below image) --}}
        @if ($secondaryName)
            <div class="text-center mb-2">
                <span class="text-sm" style="color: #64748b !important; display: inline-block !important;">{{ $secondaryName }}</span>
            </div>
        @endif

        {{-- Main product name with separator --}}
        <h3 id="product-title-{{ $id }}" class="text-base font-bold mb-2" style="color: {{ $brandPrimary }};">
            <a href="{{ $detailUrl }}" style="color: {{ $brandPrimary }};" class="hover:opacity-80 transition-opacity">
                    {{ $name }}
                </a>
            </h3>
        <div class="w-12 h-0.5 mb-3" style="background-color: {{ $brandPrimaryLighter }};"></div>

        {{-- Short Description --}}
        @if ($shortDescription)
            <p class="text-sm mb-4 line-clamp-2 leading-relaxed" style="color: #64748b !important; display: block !important;">
                {{ $shortDescription }}
            </p>
        @endif

        {{-- Price and Add to Cart Button (horizontal layout) --}}
        <div class="flex items-center justify-between mb-4">
            {{-- Price --}}
            <div class="flex items-baseline gap-2">
                <span class="text-xl font-bold" style="color: {{ $brandPrimary }} !important; display: inline-block;">
                    {{ Number::currency($price, current_currency(), app()->getLocale()) }}
                </span>
        </div>

            {{-- Dark Add to Cart Button with brand primary color --}}
            <button type="button"
                    wire:click="addToCart({{ $id }})"
                    wire:loading.attr="disabled"
                    @if (!$inStock) disabled @endif
                    class="flex items-center gap-2 text-white px-4 py-2 rounded-md text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed transition-colors hover:opacity-90"
                    style="background-color: {{ $brandPrimary }} !important;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01" />
                </svg>
                <span>|</span>
                <span>{{ __('frontend.home.products.actions.add_to_cart') }}</span>
            </button>
        </div>

        {{-- Bottom section: Stock status and Rating --}}
        <div class="mt-auto flex items-center justify-between pt-2" style="border-top: 1px solid {{ $borderColor }};">
            {{-- Stock Status (left) --}}
            <span class="text-xs font-medium" style="color: {{ $brandPrimaryLight }} !important; display: inline-block;">
                {{ $inStock ? __('frontend.home.products.stock.in') : __('frontend.home.products.stock.out') }}
            </span>

            {{-- Rating Stars (right) - Always show, even if no reviews --}}
            <div class="flex items-center gap-1">
                @if ($averageRating !== null && $reviewsCount > 0)
                    @for ($i = 1; $i <= 5; $i++)
                        <svg class="w-3.5 h-3.5" 
                             style="color: {{ $i <= round($averageRating) ? '#fbbf24' : '#d1d5db' }};"
                             fill="currentColor" 
                             viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    @endfor
                    <span class="ml-1 text-xs" style="color: {{ $brandPrimaryLight }} !important;">({{ $reviewsCount }})</span>
        @else
                    {{-- Show empty stars when no reviews --}}
                    @for ($i = 1; $i <= 5; $i++)
                        <svg class="w-3.5 h-3.5" 
                             style="color: #d1d5db;"
                             fill="currentColor" 
                             viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    @endfor
                @endif
            </div>
        </div>
    </div>
</article>
