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
        'class' =>
            'group relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white shadow',
    ]);
@endphp

<article {{ $attributes }} aria-labelledby="product-title-{{ $product->id }}">
    <div class="relative aspect-[4/3] overflow-hidden">
        @if ($image)
            <img src="{{ $image }}" alt="{{ $product->name }}"
                 class="h-full w-full object-cover" loading="lazy">
        @else
            <div
                 class="flex h-full w-full items-center justify-center bg-gradient-to-br from-indigo-500/40 to-purple-500/40 text-3xl font-semibold text-white">
                {{ mb_substr($product->name, 0, 2) }}
            </div>
        @endif

        <div class="absolute inset-0 bg-gradient-to-t from-gray-900/20 via-transparent to-transparent"></div>

        <div class="absolute top-4 left-4 flex flex-wrap gap-2">
            @if ($hasDiscount)
                <span
                      class="inline-flex items-center gap-1 rounded-full bg-rose-500 px-3 py-1 text-xs font-semibold text-white shadow-lg">
                    {{ __('frontend/home.products.badges.sale') }}
                    @if ($product->discount_percentage > 0)
                        <span>−{{ (int) round($product->discount_percentage) }}%</span>
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

        @if ($product->brand)
            <span
                  class="absolute top-4 right-4 rounded-full bg-white/90 px-3 py-1 text-xs font-medium text-gray-800 backdrop-blur">
                {{ $product->brand->name }}
            </span>
        @endif
    </div>

    <div class="flex flex-1 flex-col gap-4 px-6 py-6">
        <div class="space-y-2">
            <h3 id="product-title-{{ $product->id }}"
                class="text-lg font-semibold leading-tight text-gray-900 line-clamp-2">
                <a href="{{ route('product.show', $product->slug ?? $product) }}"
                   class="transition hover:text-indigo-600">
                    {{ $product->name }}
                </a>
            </h3>
            <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600">
                @foreach ($product->categories->take(2) as $category)
                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-0.5 text-gray-700">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="1.5"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18" />
                        </svg>
                        {{ $category->name }}
                    </span>
                @endforeach
            </div>
        </div>

        <div class="space-y-2">
            <div class="flex items-baseline gap-3">
                <span class="text-2xl font-bold text-gray-900">
                    {{ \Illuminate\Support\Number::currency($currentPrice, current_currency(), app()->getLocale()) }}
                </span>
                @if ($comparePrice)
                    <span class="text-sm text-gray-500 line-through">
                        {{ \Illuminate\Support\Number::currency($comparePrice, current_currency(), app()->getLocale()) }}
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
                    {{ $product->stock_quantity > 0 ? __('frontend/home.products.stock.in') : __('frontend/home.products.stock.out') }}
                </span>
                @if ($product->reviews_count > 0)
                    <span class="inline-flex items-center gap-1"
                          aria-label="{{ number_format((float) $product->average_rating, 1) }} {{ __('frontend/home.products.rating_out_of_5') }}">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M11.48 3.499a.562.562 0 011.04 0l2.01 4.073 4.495.654a.563.563 0 01.311.96l-3.25 3.166.768 4.477a.563.563 0 01-.817.593L12 15.347l-4.037 2.125a.563.563 0 01-.817-.593l.768-4.477-3.25-3.165a.563.563 0 01.311-.96l4.495-.654 2.01-4.073z" />
                        </svg>
                        {{ number_format((float) $product->average_rating, 1) }}
                        <span class="text-gray-400">({{ $product->reviews_count }})</span>
                    </span>
                @endif
            </div>
        </div>

        <div class="mt-auto flex items-center justify-between gap-3">
            <a href="{{ route('product.show', $product->slug ?? $product) }}"
               class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-gray-50 px-4 py-2 text-xs font-semibold text-gray-700">
                {{ __('frontend/home.products.actions.details') }}
                <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" fill="none"
                     stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </a>
            <button type="button"
                    wire:click="addToCart({{ $product->id }})"
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
