<section class="relative bg-slate-900 py-20 text-slate-50">
    <div class="absolute inset-x-0 top-0 h-32 bg-gradient-to-b from-indigo-500/20 to-transparent pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
            <div class="space-y-4 max-w-2xl">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-white/70">
                    {{ __('Коллекции и подборки') }}
                </span>
                <h2 class="text-3xl sm:text-4xl font-heading font-semibold leading-tight">
                    {{ __('Актуальные коллекции с вдохновением и готовыми подборками') }}
                </h2>
                <p class="text-sm sm:text-base text-white/70 leading-relaxed">
                    {{ __('Каждая коллекция оформлена визуально: выбирайте настроение, сезон или стиль и мгновенно переходите к товарам.') }}
                </p>
            </div>
            <a href="{{ route('collections.index') }}" class="inline-flex items-center gap-2 self-start rounded-full border border-white/20 bg-white/5 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-white/10">
                {{ __('Все коллекции') }}
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>

        {{ $this->getSchema('collections')?->toEmbeddedHtml() }}
    </div>
</section>
