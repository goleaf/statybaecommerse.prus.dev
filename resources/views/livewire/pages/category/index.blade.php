@php
    // Ensure locale is set from route before rendering (mirror SetLocale middleware logic)
    $request = request();
    $supportedConfig = config('app.supported_locales', 'lt,en');
    $supportedLocales = is_array($supportedConfig) 
        ? $supportedConfig 
        : array_map('trim', explode(',', (string) $supportedConfig));
    $supportedLocales = array_values(array_filter($supportedLocales, function($locale) {
        return is_string($locale) && $locale !== '';
    }));
    
    $routeLocale = $request->route('locale');
    $locale = $routeLocale;
    
    // If no route parameter or invalid, try session, cookie, or default
    if (!$locale || !in_array($locale, $supportedLocales, true)) {
        $candidateLocales = array_filter([
            session('locale'),
            session('app.locale'),
            $request->cookie('app_locale'),
            config('app.locale', 'lt'),
        ], function($candidate) {
            return is_string($candidate) && $candidate !== '';
        });
        
        foreach ($candidateLocales as $candidate) {
            if (in_array($candidate, $supportedLocales, true)) {
                $locale = $candidate;
                break;
            }
        }
    }
    
    // Ensure we have a valid locale
    if (!$locale || !in_array($locale, $supportedLocales, true)) {
        $locale = config('app.locale', 'lt');
    }
    
    // Set the locale explicitly
    app()->setLocale($locale);
    app()->instance('request_locale', $locale);
    
    $categories = $this->categories;
    $totalCategories = $categories->count();
    $from = $categories->count() ? 1 : 0;
    $to = $categories->count();
    $activeFilterCount = collect([
        !empty($search ?? ''),
        $inStock ?? false,
        $onSale ?? false,
        $hasProducts ?? false,
        filled($priceMin ?? null),
        filled($priceMax ?? null),
        !empty($selectedBrandIds ?? []),
        !empty($selectedCollectionIds ?? []),
        !empty($selectedCategoryIds ?? []),
    ])->filter()->count();
@endphp

@section('meta')
    <x-meta
        :title="__('categories_index_meta_title') . ' - ' . config('app.name')"
        :description="__('categories_index_meta_description')"
        canonical="{{ url()->current() }}" />
@endsection

<div class="bg-sage">
    <div class="bg-dark text-sage">
        <x-container class="px-4 py-12 sm:py-16">
            <nav class="text-xs font-medium uppercase tracking-[0.3em] text-sage/80" aria-label="{{ __('Breadcrumb') }}">
                <ol class="flex items-center gap-3">
                    <li>
                        <a href="{{ route('localized.home', ['locale' => $locale]) }}"
                           class="inline-flex items-center gap-2 text-sage transition hover:text-white">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h12a1 1 0 001-1V10" />
                            </svg>
                            {{ __('frontend.navigation.home') }}
                        </a>
                    </li>
                    <li class="text-sage/60">/</li>
                    <li class="text-white">{{ __('categories_index_meta_title') }}</li>
                </ol>
            </nav>

            <div class="mt-8 flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl space-y-5">
                    <span class="inline-flex items-center gap-2 rounded-full border border-sage bg-sage px-4 py-1 text-[11px] font-semibold uppercase tracking-[0.35em] text-dark">
                        {{ __('categories_index_badge') }}
                    </span>
                    <h1 class="text-3xl font-bold leading-tight text-white sm:text-4xl md:text-5xl">
                        {{ __('categories_index_title') }}
                    </h1>
                    <p class="text-base text-sage sm:text-lg">
                        {{ __('categories_index_description') }}
                    </p>
                </div>

                <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-end sm:gap-6">
                    <div class="rounded-2xl border border-sage/30 bg-sage/10 px-4 py-3 text-sm font-semibold text-sage shadow-sm">
                        {{ __('categories_index_catalogue_count', ['count' => number_format($totalCategories)]) }}
                    </div>
                    <div class="rounded-2xl border border-sage/30 bg-sage/10 px-4 py-3 text-sm text-sage/80 shadow-sm">
                        @if ($activeFilterCount > 0)
                            {{ __('categories_index_filters_active', ['count' => $activeFilterCount]) }}
                        @else
                            {{ __('categories_index_filters_none') }}
                        @endif
                    </div>
                    <button type="button"
                            wire:click="$toggle('sidebarOpen')"
                            wire:confirm="{{ __('translations.confirm_toggle_sidebar') }}"
                            class="inline-flex items-center gap-2 rounded-full border border-sage/30 bg-sage/10 px-4 py-2 text-sm font-semibold text-sage shadow-sm transition hover:border-sage hover:bg-sage/20 lg:hidden">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h6M3 12h6m-6 7h6M13 5h8M13 12h8m-8 7h8" />
                        </svg>
                        {{ __('categories_index_filters_button') }}
                    </button>
                </div>
            </div>
        </x-container>
    </div>

    <x-container class="px-4 pb-16 pt-12">
        <div class="grid gap-8 lg:grid-cols-12">
            <aside class="hidden lg:col-span-3 lg:block">
                <div class="rounded-3xl border border-sage/30 bg-dark p-6 shadow-lg">
                    <div class="mb-6 space-y-2">
                        <span class="inline-flex items-center gap-2 rounded-full border border-sage/30 bg-sage/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.35em] text-sage">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            {{ __('categories_index_filters_button') }}
                        </span>
                        <h2 class="text-xl font-semibold text-white">{{ __('categories_index_filters_title') }}</h2>
                        <p class="text-sm leading-relaxed text-sage/80">
                            {{ __('categories_index_filters_description') }}
                        </p>
                    </div>
                    <div class="space-y-6">
                    @include('livewire.pages.category.partials.filters', ['variant' => 'desktop'])
                    </div>
                </div>
            </aside>

            <section class="lg:col-span-9 space-y-6" x-data="{ view: 'grid' }">
                <div class="rounded-3xl border border-sage/30 bg-dark p-4 shadow-sm sm:p-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex flex-wrap items-center gap-3 text-sm text-sage">
                            <span class="inline-flex items-center gap-2 rounded-full border border-sage/30 bg-sage/10 px-3 py-1 text-xs font-semibold text-sage">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ __('categories_index_real_time_results') }}
                            </span>
                            @if ($from && $to)
                                <span class="text-sage/80">{{ __('categories_index_showing_results', ['from' => $from, 'to' => $to, 'total' => $totalCategories]) }}</span>
                            @else
                                <span class="text-sage/80">{{ __('categories_index_no_results') }}</span>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <div class="flex items-center gap-2 rounded-xl border border-sage/30 bg-dark/30 px-3 py-2 text-sm text-sage">
                                <label for="sort" class="text-xs font-semibold uppercase tracking-wide text-sage/60">
                                    {{ __('categories_index_sort') }}
                                </label>
                                <select id="sort" wire:model.live="sort" class="border-0 bg-transparent text-sm font-medium text-sage focus:outline-none focus:ring-0">
                                    <option value="name_asc" class="bg-dark text-sage">{{ __('categories_index_sort_name_asc') }}</option>
                                    <option value="name_desc" class="bg-dark text-sage">{{ __('categories_index_sort_name_desc') }}</option>
                                    <option value="products_desc" class="bg-dark text-sage">{{ __('categories_index_sort_products_desc') }}</option>
                                    <option value="products_asc" class="bg-dark text-sage">{{ __('categories_index_sort_products_asc') }}</option>
                                </select>
                            </div>

                            <div class="flex items-center gap-2 rounded-xl border border-sage/30 bg-dark/30 px-3 py-2 text-sm text-sage">
                                <label for="per-page" class="text-xs font-semibold uppercase tracking-wide text-sage/60">
                                    {{ __('categories_index_per_page') }}
                                </label>
                                <select id="per-page" wire:model.live="perPage" class="border-0 bg-transparent text-sm font-medium text-sage focus:outline-none focus:ring-0">
                                    <option value="12" class="bg-dark text-sage">12</option>
                                    <option value="24" class="bg-dark text-sage">24</option>
                                    <option value="36" class="bg-dark text-sage">36</option>
                                    <option value="48" class="bg-dark text-sage">48</option>
                                </select>
                            </div>

                            <div class="hidden items-center gap-1 rounded-xl border border-sage/30 bg-dark/30 p-1 text-sage shadow-sm md:flex">
                                <button type="button"
                                        @click="view = 'grid'"
                                        :class="view === 'grid' ? 'bg-sage text-dark shadow-sm' : 'hover:text-white hover:bg-sage/10'"
                                        class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold transition text-sage">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h7v7H4V6zm9 0h7v7h-7V6zM4 13h7v7H4v-7zm9 0h7v7h-7v-7z" />
                                    </svg>
                                    {{ __('categories_index_view_grid') }}
                                </button>
                                <button type="button"
                                        @click="view = 'list'"
                                        :class="view === 'list' ? 'bg-sage text-dark shadow-sm' : 'hover:text-white hover:bg-sage/10'"
                                        class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold transition text-sage">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                    {{ __('categories_index_view_list') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div wire:loading.delay.longer class="absolute inset-0 z-10 flex items-center justify-center rounded-3xl bg-dark/80 backdrop-blur-sm">
                        <div class="h-10 w-10 animate-spin rounded-full border-2 border-sage border-t-transparent"></div>
                    </div>

                    @if ($categories->count() > 0)
                        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" :class="view === 'list' ? 'sm:grid-cols-1 xl:grid-cols-1' : 'sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4'">
                            @foreach ($categories as $category)
                                @php
                                    $slug = method_exists($category, 'trans')
                                        ? ($category->trans('slug') ?? $category->slug)
                                        : ($category->slug ?? (is_string($category) ? $category : null));
                                    $name = method_exists($category, 'trans')
                                        ? ($category->trans('name') ?? $category->name)
                                        : $category->name;
                                    $description = method_exists($category, 'trans')
                                        ? ($category->trans('description') ?? $category->description)
                                        : $category->description;
                                    $banner = method_exists($category, 'getBannerUrl') ? $category->getBannerUrl('md') : null;
                                    $image = $category->hero_image_url
                                        ?? $banner
                                        ?? (method_exists($category, 'getImageUrl') ? $category->getImageUrl('md') : null)
                                        ?? (method_exists($category, 'getFirstMediaUrl') ? $category->getFirstMediaUrl('images', 'image-md') : null)
                                        ?? (method_exists($category, 'getFirstMediaUrl') ? $category->getFirstMediaUrl('images') : null);
                                    $productCount = $category->products_count
                                        ?? ($category->published_products_count ?? ($category->products?->count() ?? 0));
                                @endphp

                                <article class="group flex flex-col overflow-hidden rounded-3xl border border-ash/30 bg-white shadow-sm transition hover:-translate-y-1 hover:border-ash/60 hover:shadow-xl"
                                         :class="view === 'list' ? 'sm:flex-row' : ''">
                                    <div class="relative h-48 overflow-hidden sm:h-52" :class="view === 'list' ? 'sm:h-auto sm:w-64' : ''">
                                        @if ($image)
                                            <img src="{{ $image }}"
                                                 alt="{{ $name }}"
                                                 loading="lazy"
                                                 class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                                        @else
                                            <div class="flex h-full w-full items-center justify-center bg-ash/10 text-4xl font-semibold text-dark">
                                                {{ mb_strtoupper(mb_substr($name, 0, 2)) }}
                                            </div>
                                        @endif
                                        <div class="absolute inset-x-0 bottom-0 flex items-center justify-between bg-gradient-to-t from-black/70 to-transparent px-5 pb-4 pt-12">
                                            <h3 class="text-lg font-semibold text-white drop-shadow-lg">
                                                {{ $name }}
                                            </h3>
                                            <span class="inline-flex items-center gap-1 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-dark shadow-sm">
                                                {{ $productCount }}
                                                <span class="text-ash">{{ trans_choice('products', $productCount) }}</span>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex flex-1 flex-col justify-between gap-4 px-5 py-6">
                                        @if ($description)
                                            <p class="text-sm leading-relaxed text-stone line-clamp-3">
                                                {{ \Illuminate\Support\Str::limit(strip_tags($description), 180) }}
                                            </p>
                                        @else
                                            <p class="text-sm text-ash">
                                                {{ __('categories_index_description_placeholder') }}
                                            </p>
                                        @endif

                                        <div class="flex items-center justify-center">
                                            <a href="{{ route('localized.categories.show', ['locale' => $locale, 'category' => $slug]) }}"
                                               class="inline-flex items-center gap-2 rounded-full bg-sage px-4 py-2 text-sm font-semibold text-dark transition hover:bg-sage/90">
                                                {{ __('categories_index_view_category') }}
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                    @else
                        <x-shared.empty-state
                            title="{{ __('categories_index_empty_title') }}"
                            description="{{ __('categories_index_empty_description') }}"
                            icon="heroicon-o-archive-box"
                            :action-text="__('categories_index_reset_filters')"
                            :action-url="route('localized.categories.index', ['locale' => $locale])"
                        />
                    @endif
                </div>
            </section>
        </div>
    </x-container>

    @if ($sidebarOpen)
        <div class="fixed inset-0 z-40 lg:hidden">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
                 wire:click="$toggle('sidebarOpen')"
                 wire:confirm="{{ __('translations.confirm_toggle_sidebar') }}"></div>

            <div class="absolute inset-y-0 right-0 w-11/12 max-w-md rounded-l-3xl bg-dark shadow-2xl">
                <div class="flex h-full flex-col overflow-y-auto">
                    <div class="flex items-center justify-between border-b border-sage/30 p-6">
                        <div class="space-y-2">
                            <span class="inline-flex items-center gap-2 rounded-full border border-sage/30 bg-sage/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.35em] text-sage">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                                {{ __('categories_index_filters_button') }}
                            </span>
                            <h2 class="text-xl font-semibold text-white">{{ __('categories_index_filters_button') }}</h2>
                            <p class="text-sm leading-relaxed text-sage/80">
                                {{ __('categories_index_adjust_filters') }}
                            </p>
                        </div>
                        <button type="button"
                                class="rounded-full border border-sage/30 p-2 text-sage transition hover:border-sage hover:bg-sage/10"
                                wire:click="$toggle('sidebarOpen')"
                                wire:confirm="{{ __('translations.confirm_toggle_sidebar') }}"
                                aria-label="{{ __('categories_index_close') }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="flex-1 space-y-6 overflow-y-auto p-6">
                    @include('livewire.pages.category.partials.filters', ['variant' => 'mobile'])
                    </div>
                    <div class="border-t border-sage/30 p-6">
                        <x-shared.button
                            type="button"
                            variant="primary"
                            size="sm"
                            class="w-full"
                            wire:click="$toggle('sidebarOpen')"
                        >
                            {{ __('categories_index_apply_filters') }}
                        </x-shared.button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <x-filament-actions::modals />
</div>
