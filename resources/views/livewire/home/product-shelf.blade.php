<section class="relative bg-white py-20 text-gray-900" aria-labelledby="home-products-heading-{{ $preset }}">
    <div
         class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(79,70,229,0.03),transparent_60%)] pointer-events-none">
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="space-y-4 max-w-3xl">
            <span
                  class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-gray-100 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-gray-600">
                {{ $preset === 'sale' ? __('home.sale') : ($preset === 'latest' ? __('home.new') : ($preset === 'trending' ? __('home.trending') : __('home.featured'))) }}
            </span>
            <h2 id="home-products-heading-{{ $preset }}"
                class="text-3xl sm:text-4xl font-heading font-semibold leading-tight text-gray-900">
                {{ $title ??
                    match ($preset) {
                        'sale' => __('home.sale_products'),
                        'latest' => __('home.latest_products'),
                        'trending' => __('home.trending_products'),
                        default => __('home.featured_products'),
                    } }}
            </h2>
            @if ($subtitle)
                <p class="text-sm sm:text-base text-gray-600 leading-relaxed">
                    {{ $subtitle }}
                </p>
            @endif
        </div>

        <div aria-label="{{ __('home.products_grid') }}">
            {!! $this->getSchema('productShelf')?->toEmbeddedHtml() !!}
        </div>
    </div>
</section>
