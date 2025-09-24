<section class="relative bg-gradient-to-b from-indigo-50 to-white py-20 text-gray-900"
         aria-labelledby="home-collections-heading">
    <div class="absolute inset-x-0 top-0 h-32 bg-gradient-to-b from-indigo-500/5 to-transparent pointer-events-none">
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
            <div class="space-y-4 max-w-2xl">
                <span
                      class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-gray-100 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-gray-600">
                    {{ __('home.collections_selections') }}
                </span>
                <h2 id="home-collections-heading"
                    class="text-3xl sm:text-4xl font-heading font-semibold leading-tight text-gray-900">
                    {{ __('home.current_collections') }}
                </h2>
                <p class="text-sm sm:text-base text-gray-600 leading-relaxed">
                    {{ __('home.collections_description') }}
                </p>
            </div>
            <a href="{{ route('collections.index') }}"
               class="inline-flex items-center gap-2 self-start rounded-full border border-gray-300 bg-gray-50 px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">
                {{ __('home.all_collections') }}
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>

        <div aria-label="{{ __('home.collections_grid') }}">
            {!! $this->getSchema('collections')?->toEmbeddedHtml() !!}
        </div>
    </div>
</section>
