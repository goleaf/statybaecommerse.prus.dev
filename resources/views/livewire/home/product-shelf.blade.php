<section class="relative bg-slate-950 py-20 text-slate-50">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(79,70,229,0.18),transparent_60%)] pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="space-y-4 max-w-3xl">
            <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-white/70">
                {{ $preset === 'sale' ? __('Акции') : ($preset === 'latest' ? __('Новинки') : ($preset === 'trending' ? __('Популярное') : __('Избранное'))) }}
            </span>
            <h2 class="text-3xl sm:text-4xl font-heading font-semibold leading-tight">
                {{ $title }}
            </h2>
            @if ($subtitle)
                <p class="text-sm sm:text-base text-white/70 leading-relaxed">
                    {{ $subtitle }}
                </p>
            @endif
        </div>

        {{ $this->getSchema('products')?->toEmbeddedHtml() }}
    </div>
</section>
