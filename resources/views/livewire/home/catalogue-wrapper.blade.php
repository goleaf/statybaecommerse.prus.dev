<section class="relative bg-slate-950 py-24 text-slate-50">
    <div class="absolute inset-x-0 top-0 h-32 bg-gradient-to-b from-indigo-500/15 to-transparent pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="space-y-4 max-w-3xl">
            <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-white/70">
                {{ __('frontend/home.catalogue.badge') }}
            </span>
            <h2 class="text-3xl sm:text-4xl font-heading font-semibold leading-tight">
                {{ __('frontend/home.catalogue.title') }}
            </h2>
            <p class="text-sm sm:text-base text-white/70 leading-relaxed">
                {{ __('frontend/home.catalogue.subtitle') }}
            </p>
        </div>

        {{ $this->getSchema('catalogue')?->toEmbeddedHtml() }}
    </div>
</section>
