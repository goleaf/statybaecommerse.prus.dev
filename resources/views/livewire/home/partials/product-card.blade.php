@php
    use Closure;
    use Illuminate\Contracts\Support\Arrayable;
    use Illuminate\Support\Number;
    use Illuminate\View\ComponentAttributeBag;

    /** @var array|Arrayable $product */
    $productData = $product instanceof Arrayable ? $product->toArray() : (array) $product;

    $cardPreset = $preset ?? 'featured';
    $attributes = ($attributes ?? new ComponentAttributeBag())->merge([
        'class' => 'group relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white shadow',
    ]);

    foreach ($attributes->getAttributes() as $attributeKey => $attributeValue) {
        if ($attributeValue instanceof Closure) {
            \Log::error('Closure attribute detected on product card.', [
                'attribute'  => $attributeKey,
                'product_id' => $productData['id'] ?? null,
            ]);
        }
    }

    $imageUrl = $productData['image_url'] ?? null;
    $initials = $productData['initials'] ?? '';
    $name = $productData['name'] ?? '';
    $detailUrl = $productData['detail_url'] ?? '#';
    $brandName = $productData['brand_name'] ?? null;
    $categoryLabels = is_array($productData['category_labels'] ?? null)
        ? $productData['category_labels']
        : [];
    $id = (int) ($productData['id'] ?? 0);

    $price = (float) ($productData['price'] ?? 0.0);
    $salePrice = isset($productData['sale_price']) ? (float) $productData['sale_price'] : null;
    $comparePrice = isset($productData['compare_price']) ? (float) $productData['compare_price'] : null;

    $currentPrice = $salePrice !== null && $salePrice > 0 && $salePrice < $price
        ? $salePrice
        : $price;

    $compareAtPrice = null;
    if ($comparePrice !== null && $comparePrice > $currentPrice) {
        $compareAtPrice = $comparePrice;
    } elseif ($salePrice !== null && $salePrice < $price) {
        $compareAtPrice = $price;
    }

    $hasDiscount = $compareAtPrice !== null;
    $discountBadge = null;
    if ($hasDiscount) {
        $discountValue = $productData['discount_percentage'] ?? null;
        if ($discountValue !== null) {
            $discountBadge = (int) round((float) $discountValue);
        } elseif ($compareAtPrice > 0) {
            $discountBadge = (int) round((($compareAtPrice - $currentPrice) / $compareAtPrice) * 100);
        }
    }

    $stockQuantity = (int) ($productData['stock_quantity'] ?? 0);
    $inStock = $stockQuantity > 0;
    $averageRating = $productData['average_rating'] ?? null;
    $reviewsCount = (int) ($productData['reviews_count'] ?? 0);
@endphp

<article {{ $attributes }} aria-labelledby="product-title-{{ $id }}">
    <div class="relative aspect-[4/3] overflow-hidden">
        @if (! empty($imageUrl))
            <img src="{{ $imageUrl }}" alt="{{ $name }}"
                 class="h-full w-full object-cover" loading="lazy">
        @else
            <div
                 class="flex h-full w-full items-center justify-center bg-gradient-to-br from-indigo-500/40 to-purple-500/40 text-3xl font-semibold text-white">
                {{ $initials }}
            </div>
        @endif

        <div class="absolute inset-0 bg-gradient-to-t from-gray-900/20 via-transparent to-transparent"></div>

        <div class="absolute top-4 left-4 flex flex-wrap gap-2">
            @if ($hasDiscount)
                <span
                      class="inline-flex items-center gap-1 rounded-full bg-rose-500 px-3 py-1 text-xs font-semibold text-white shadow-lg">
                    {{ __('frontend/home.products.badges.sale') }}
                    @if ($discountBadge)
                        <span>−{{ $discountBadge }}%</span>
                    @endif
                </span>
            @elseif($cardPreset === 'latest')
                <span
                      class="inline-flex items-center gap-1 rounded-full bg-indigo-500 px-3 py-1 text-xs font-semibold text-white shadow-lg">
                    {{ __('frontend/home.products.badges.new') }}
                </span>
            @elseif($cardPreset === 'trending')
                <span
                      class="inline-flex items-center gap-1 rounded-full bg-amber-500 px-3 py-1 text-xs font-semibold text-white shadow-lg">
                    {{ __('frontend/home.products.badges.popular') }}
                </span>
            @endif
        </div>

        @if ($brandName)
            <span
                  class="absolute top-4 right-4 rounded-full bg-white/90 px-3 py-1 text-xs font-medium text-gray-800 backdrop-blur">
                {{ $brandName }}
            </span>
        @endif
    </div>

    <div class="flex flex-1 flex-col gap-4 px-6 py-6">
        <div class="space-y-2">
            <h3 id="product-title-{{ $id }}"
                class="text-lg font-semibold leading-tight text-gray-900 line-clamp-2">
                <a href="{{ $detailUrl }}"
                   class="transition hover:text-indigo-600">
                    {{ $name }}
                </a>
            </h3>
            <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600">
                @foreach (array_slice($categoryLabels, 0, 2) as $categoryLabel)
                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-0.5 text-gray-700">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="1.5"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18" />
                        </svg>
                        {{ $categoryLabel }}
                    </span>
                @endforeach
            </div>
        </div>

        <div class="space-y-2">
            <div class="flex items-baseline gap-3">
                <span class="text-2xl font-bold text-gray-900">
                    {{ Number::currency($currentPrice, current_currency(), app()->getLocale()) }}
                </span>
                @if ($compareAtPrice)
                    <span class="text-sm text-gray-500 line-through">
                        {{ Number::currency($compareAtPrice, current_currency(), app()->getLocale()) }}
                    </span>
                @endif
            </div>
            <div class="flex items-center gap-2 text-xs text-gray-600">
                <span class="inline-flex items-center gap-1">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ $inStock ? __('frontend/home.products.stock.in') : __('frontend/home.products.stock.out') }}
                </span>
                @if ($averageRating !== null && $reviewsCount > 0)
                    <span class="inline-flex items-center gap-1"
                          aria-label="{{ number_format((float) $averageRating, 1) }} {{ __('frontend/home.products.rating_out_of_5') }}">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M11.48 3.499a.562.562 0 011.04 0l2.01 4.073 4.495.654a.563.563 0 01.311.96l-3.25 3.166.768 4.477a.563.563 0 01-.817.593L12 15.347l-4.037 2.125a.563.563 0 01-.817-.593l.768-4.477-3.25-3.165a.563.563 0 01.311-.96l4.495-.654 2.01-4.073z" />
                        </svg>
                        {{ number_format((float) $averageRating, 1) }}
                        <span class="text-gray-400">({{ $reviewsCount }})</span>
                    </span>
                @endif
            </div>
        </div>

        <div class="mt-auto flex items-center justify-between gap-3">
            <a href="{{ $detailUrl }}"
               class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-gray-50 px-4 py-2 text-xs font-semibold text-gray-700">
                {{ __('frontend/home.products.actions.details') }}
                <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" fill="none"
                     stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </a>
            <button type="button"
                    wire:click="addToCart({{ $id }})"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-lg transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-70">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01" />
                </svg>
                <span>{{ __('frontend/home.products.actions.add_to_cart') }}</span>
            </button>
        </div>
    </div>
</article>
