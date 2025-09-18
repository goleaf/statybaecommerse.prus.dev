<section class="relative py-20 bg-slate-950 text-slate-50">
    <div class="absolute inset-x-0 -top-32 -bottom-20 bg-gradient-to-b from-indigo-500/10 via-transparent to-transparent pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
            <div class="space-y-4 max-w-2xl">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-white/80">
                    {{ __('Категории каталога') }}
                </span>
                <h2 class="text-3xl sm:text-4xl font-heading font-semibold leading-tight">
                    {{ __('Исследуйте все разделы нашего магазина') }}
                </h2>
                <p class="text-sm sm:text-base text-white/70 leading-relaxed">
                    {{ __('Просмотрите полный список категорий с визуальными карточками, чтобы быстро найти нужные товары.') }}
                </p>
            </div>
            <a href="{{ route('localized.categories.index', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center gap-2 self-start rounded-full border border-white/20 bg-white/10 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20">
                {{ __('Все категории') }}
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>

        {{ $this->getSchema('categories')?->toEmbeddedHtml() }}
    </div>
</section>
