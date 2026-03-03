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

    $imageUrl = $productData['image_url']
        ?? ($productData['main_image'] ?? null)
        ?? ($productData['thumbnail'] ?? null);
    $fallbackImageUrl = product_placeholder_url('large');
    $name = $productData['name'] ?? '';
    $detailUrl = $productData['detail_url'] ?? null;
    $productRouteKey = null;

    foreach ([
        is_object($product) && method_exists($product, 'trans') ? $product->trans('slug') : null,
        is_object($product) && method_exists($product, 'getRouteKey') ? $product->getRouteKey() : null,
        $productData['slug'] ?? null,
        isset($productData['id']) ? (string) $productData['id'] : null,
    ] as $routeCandidate) {
        if (is_string($routeCandidate) && $routeCandidate !== '') {
            $productRouteKey = $routeCandidate;
            break;
        }
    }

    if (! is_string($detailUrl) || $detailUrl === '' || $detailUrl === '#') {
        $detailUrl = null;

        if ($productRouteKey !== null && \Illuminate\Support\Facades\Route::has('localized.products.show')) {
            try {
                $detailUrl = route('localized.products.show', [
                    'locale'  => app()->getLocale(),
                    'product' => $productRouteKey,
                ]);
            } catch (\Throwable) {
                $detailUrl = null;
            }
        }

        if ($detailUrl === null && $productRouteKey !== null && \Illuminate\Support\Facades\Route::has('frontend.products.show')) {
            try {
                $detailUrl = route('frontend.products.show', ['product' => $productRouteKey]);
            } catch (\Throwable) {
                $detailUrl = null;
            }
        }

        if ($detailUrl === null && $productRouteKey !== null && \Illuminate\Support\Facades\Route::has('products.show')) {
            try {
                $detailUrl = route('products.show', ['product' => $productRouteKey]);
            } catch (\Throwable) {
                $detailUrl = null;
            }
        }
    }

    $detailUrl = $detailUrl ?: '#';
    $brandName = $productData['brand_name'] ?? null;
    $categoryLabels = is_array($productData['category_labels'] ?? null)
        ? $productData['category_labels']
        : [];
    $id = (int) ($productData['id'] ?? 0);

    $price = (float) ($productData['price'] ?? 0.0);

    $stockQuantity = (int) ($productData['stock_quantity'] ?? 0);
    $manageStock = isset($productData['manage_stock']) ? (bool) $productData['manage_stock'] : true;
    $inStock = ! $manageStock || $stockQuantity > 0;
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
            <img src="{{ $fallbackImageUrl }}" alt="{{ $name }}"
                 class="w-full h-full object-cover" loading="lazy">
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
                    class="flex items-center cursor-pointer text-white px-4 py-2 rounded-md text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed transition-colors hover:opacity-90"
                    style="background-color: {{ $brandPrimary }} !important;">
                <span>{{ __('frontend.home.products.actions.add_to_cart') }}</span>
            </button>
        </div>

        {{-- Bottom section: Stock status --}}
        <div class="mt-auto flex items-center justify-between pt-2" style="border-top: 1px solid {{ $borderColor }};">
            {{-- Stock Status --}}
            <span class="text-xs font-medium" style="color: {{ $brandPrimaryLight }} !important; display: inline-block;">
                {{ $inStock ? __('frontend.home.products.stock.in') : __('frontend.home.products.stock.out') }}
            </span>
        </div>
    </div>
</article>
