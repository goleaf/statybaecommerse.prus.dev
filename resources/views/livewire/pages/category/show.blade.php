@php
    // Ensure locale is set from route before rendering (mirror SetLocale middleware logic)
    $request = request();
    $supportedConfig = config('app.supported_locales', 'lt,en');
    if (is_array($supportedConfig)) {
        $supportedLocales = $supportedConfig;
    } elseif ($supportedConfig instanceof \Traversable) {
        $supportedLocales = iterator_to_array($supportedConfig);
    } elseif (is_string($supportedConfig)) {
        $supportedLocales = array_map('trim', explode(',', $supportedConfig));
    } else {
        $fallback = is_array($supportedConfig)
            ? implode(',', array_filter($supportedConfig, static fn ($value) => is_string($value)))
            : (string) $supportedConfig;
        $supportedLocales = $fallback !== ''
            ? array_map('trim', explode(',', $fallback))
            : [];
    }
    $supportedLocales = array_values(array_filter($supportedLocales, function ($locale) {
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
    
    // Determine if this is category index or category show
    $isIndex = !isset($category);
    $pageTitle = $isIndex ? __('categories.index.meta_title') : $category->name;
    $pageDescription = $isIndex ? __('categories.index.meta_description') : ($category->description ?? '');
@endphp

@section('meta')
    <x-meta
        :title="$pageTitle . ' - ' . config('app.name')"
        :description="$pageDescription"
        canonical="{{ url()->current() }}" />
@endsection

<div x-data="{ showFilters: false, viewMode: 'grid' }" class="min-h-screen bg-slate-50">
    <!-- Dark Banner Section -->
    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-cyan-900 text-slate-100">
        <x-container class="px-4 py-12 sm:py-16">
            <nav class="text-xs font-medium uppercase tracking-[0.3em] text-slate-300" aria-label="{{ __('ui.breadcrumb') }}">
                <ol class="flex items-center gap-3">
                    <li>
                        <a href="{{ route('localized.home', ['locale' => app()->getLocale()]) }}"
                           class="inline-flex items-center gap-2 text-slate-200 transition hover:text-white">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h12a1 1 0 001-1V10" />
                            </svg>
                            {{ __('frontend.navigation.home') }}
                        </a>
                    </li>
                    <li class="text-slate-400">/</li>
                    @if (!$isIndex)
                        <li>
                            <a href="{{ route('localized.categories.index', ['locale' => app()->getLocale()]) }}"
                               class="text-slate-200 transition hover:text-white">
                                {{ __('categories.index.meta_title') }}
                            </a>
                        </li>
                        <li class="text-slate-400">/</li>
                        <li class="text-white">{{ $category->name }}</li>
                    @else
                        <li class="text-white">{{ __('categories.index.meta_title') }}</li>
                    @endif
                </ol>
            </nav>

            <div class="mt-8 flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl space-y-5">
                    <span class="inline-flex items-center gap-2 rounded-full border border-cyan-200/40 bg-white/10 px-4 py-1 text-[11px] font-semibold uppercase tracking-[0.35em] text-cyan-100 backdrop-blur-sm">
                        @if ($isIndex)
                            {{ __('categories.index.badge') }}
                        @else
                            {{ __('categories.show.badge') }}
                        @endif
                    </span>
                    <h1 class="text-3xl font-bold leading-tight text-white sm:text-4xl md:text-5xl">
                        @if ($isIndex)
                            {{ __('categories.index.title') }}
                        @else
                            {{ $category->name }}
                        @endif
                    </h1>
                    @if ($isIndex)
                        <p class="text-base text-slate-200 sm:text-lg">
                            {{ __('categories.index.description') }}
                        </p>
                    @elseif (!empty($category->description))
                        <p class="text-base text-white sm:text-lg">
                            {{ $category->description }}
                        </p>
                    @endif
                </div>

                <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-end sm:gap-6">
                    @if ($isIndex)
                        @php
                            $categories = $this->categories;
                            $totalCategories = $categories->total();
                            $from = $categories->firstItem();
                            $to = $categories->lastItem();
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
                        <div class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3 text-sm font-semibold text-white shadow-sm backdrop-blur-sm">
                            {{ __('categories.index.catalogue_count', ['count' => number_format($totalCategories)]) }}
                        </div>
                        <div class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3 text-sm text-slate-100 shadow-sm backdrop-blur-sm">
                            @if ($activeFilterCount > 0)
                                {{ __('categories.index.filters_active', ['count' => $activeFilterCount]) }}
                            @else
                                {{ __('categories.index.filters_none') }}
                            @endif
                        </div>
                        <button type="button"
                                wire:click="$toggle('sidebarOpen')"
                                wire:confirm="{{ __('translations.confirm_toggle_sidebar') }}"
                                class="inline-flex items-center gap-2 rounded-full border border-cyan-200/40 bg-white/10 px-4 py-2 text-sm font-semibold text-cyan-100 shadow-sm transition hover:border-cyan-200 hover:bg-white/15 lg:hidden">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h6M3 12h6m-6 7h6M13 5h8M13 12h8m-8 7h8" />
                            </svg>
                            {{ __('categories.index.filters_button') }}
                        </button>
                    @else
                        <div class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3 text-sm font-semibold text-white shadow-sm backdrop-blur-sm">
                            {{ __('categories.show.products_count', ['count' => number_format($products->total())]) }}
                        </div>
                        <div class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3 text-sm text-slate-100 shadow-sm backdrop-blur-sm">
                            @if ($products->firstItem() && $products->lastItem())
                                {{ __('categories.show.showing', ['from' => $products->firstItem(), 'to' => $products->lastItem()]) }}
                            @else
                                {{ __('categories.show.no_products') }}
                            @endif
                        </div>
                        <button type="button"
                                @click="showFilters = true"
                                class="inline-flex items-center gap-2 rounded-full border border-cyan-200/40 bg-white/10 px-4 py-2 text-sm font-semibold text-cyan-100 shadow-sm transition hover:border-cyan-200 hover:bg-white/15 lg:hidden">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h6M3 12h6m-6 7h6M13 5h8M13 12h8m-8 7h8" />
                            </svg>
                            {{ __('categories.show.filter') }}
                        </button>
                    @endif
                </div>
            </div>
        </x-container>
    </div>

    <x-container class="px-4 pb-16 pt-12">
        @php
            $roots = \App\Models\Category::query()
                ->where('is_visible', true)
                ->whereNull('parent_id')
                ->with([
                    'children' => function ($q) {
                        $q->where('is_visible', true)
                            ->orderBy('sort_order')
                            ->orderBy('name')
                            ->with([
                                'children' => function ($qq) {
                                    $qq->where('is_visible', true)->orderBy('sort_order')->orderBy('name');
                                },
                            ]);
                    },
                ])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            $categoryTree = $roots
                ->map(function ($cat) {
                    return [
                        'id' => $cat->id,
                        'name' => $cat->name,
                        'slug' => $cat->slug,
                        'children' => $cat->children
                            ->map(function ($child) {
                                return [
                                    'id' => $child->id,
                                    'name' => $child->name,
                                    'slug' => $child->slug,
                                    'children' => $child->children
                                        ->map(function ($gc) {
                                            return [
                                                'id' => $gc->id,
                                                'name' => $gc->name,
                                                'slug' => $gc->slug,
                                            ];
                                        })
                                        ->values(),
                                ];
                            })
                            ->values(),
                    ];
                })
                ->values();
        @endphp

        <div class="grid gap-8 lg:grid-cols-12">
            <aside class="hidden lg:col-span-3 lg:block lg:sticky lg:top-24 self-start">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-lg">
                    <div class="mb-6 space-y-2">
                        <span class="inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.35em] text-cyan-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            @if ($isIndex)
                                {{ __('categories.index.filters_button') }}
                            @else
                                {{ __('categories.show.filter') }}
                            @endif
                        </span>
                        <h2 class="text-xl font-semibold text-slate-900">
                            @if ($isIndex)
                                {{ __('categories.index.filters_title') }}
                            @else
                                {{ __('categories.show.filters_title') }}
                            @endif
                        </h2>
                        <p class="text-sm leading-relaxed text-slate-600">
                            @if ($isIndex)
                                {{ __('categories.index.filters_description') }}
                            @else
                                {{ __('categories.show.filters_description') }}
                            @endif
                        </p>
                    </div>
                    <div class="space-y-6">
                        @if ($isIndex)
                            @include('livewire.pages.category.partials.filters', ['variant' => 'desktop'])
                        @else
                            <!-- Categories Filter -->
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    {{ __('categories.index.meta_title') }}
                                </h3>
                                <div class="space-y-1">
                                    <x-category.tree :nodes="$categoryTree" />
                                </div>
                            </div>

                            <!-- Advanced Filters -->
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707l-6.414 6.414A1 1 0 0014 13v6l-4-2v-4a1 1 0 00-.293-.707L3.293 6.707A1 1 0 013 6V4z"></path>
                                    </svg>
                                    {{ __('categories.show.advanced_filters') }}
                                </h3>
                                <div>
                                    @livewire('components.product-filter-widget')
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </aside>

            <section class="lg:col-span-9 space-y-6" x-data="{ view: 'grid' }">
                <!-- Sort and Filter Controls -->
                <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex flex-wrap items-center gap-3 text-sm text-slate-700">
                            <span class="inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                @if ($isIndex)
                                    {{ __('categories.index.real_time_results') }}
                                @else
                                    {{ __('categories.show.real_time_results') }}
                                @endif
                            </span>
                            @if ($isIndex)
                                @if ($from && $to)
                                    <span class="text-slate-500">{{ __('categories.index.showing_results', ['from' => $from, 'to' => $to, 'total' => $totalCategories]) }}</span>
                                @else
                                    <span class="text-slate-500">{{ __('categories.index.no_results') }}</span>
                                @endif
                            @else
                                @if ($products->firstItem() && $products->lastItem())
                                    <span class="text-slate-500">{{ __('categories.show.showing_results', ['from' => $products->firstItem(), 'to' => $products->lastItem(), 'total' => $products->total()]) }}</span>
                                @else
                                    <span class="text-slate-500">{{ __('categories.show.no_results') }}</span>
                                @endif
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            @if ($isIndex)
                                <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                                    <label for="sort" class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        {{ __('categories.index.sort') }}
                                    </label>
                                    <select id="sort" wire:model.live="sort" class="border-0 bg-transparent text-sm font-medium text-slate-700 focus:outline-none focus:ring-0">
                                        <option value="name_asc" class="bg-white text-slate-700">{{ __('categories.index.sort_name_asc') }}</option>
                                        <option value="name_desc" class="bg-white text-slate-700">{{ __('categories.index.sort_name_desc') }}</option>
                                        <option value="products_desc" class="bg-white text-slate-700">{{ __('categories.index.sort_products_desc') }}</option>
                                        <option value="products_asc" class="bg-white text-slate-700">{{ __('categories.index.sort_products_asc') }}</option>
                                    </select>
                                </div>
                            @else
                                <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                                    <label for="sort-by" class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        {{ __('categories.show.sort') }}
                                    </label>
                                    <select id="sort-by" wire:model.live="sortBy" class="border-0 bg-transparent text-sm font-medium text-slate-700 focus:outline-none focus:ring-0">
                                        <option value="created_at" class="bg-white text-slate-700">{{ __('categories.show.sort_newest') }}</option>
                                        <option value="name" class="bg-white text-slate-700">{{ __('categories.show.sort_name') }}</option>
                                        <option value="price" class="bg-white text-slate-700">{{ __('categories.show.sort_price') }}</option>
                                        <option value="rating" class="bg-white text-slate-700">{{ __('categories.show.sort_rating') }}</option>
                                    </select>
                                </div>

                                <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                                    <label for="sort-direction" class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        {{ __('categories.show.order') }}
                                    </label>
                                    <select id="sort-direction" wire:model.live="sortDirection" class="border-0 bg-transparent text-sm font-medium text-slate-700 focus:outline-none focus:ring-0">
                                        <option value="asc" class="bg-white text-slate-700">{{ __('categories.show.order_ascending') }}</option>
                                        <option value="desc" class="bg-white text-slate-700">{{ __('categories.show.order_descending') }}</option>
                                    </select>
                                </div>
                            @endif

                            <div class="hidden items-center gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1 text-slate-600 shadow-sm md:flex">
                                <button type="button"
                                        @click="view = 'grid'; viewMode = 'grid'"
                                        :class="view === 'grid' ? 'bg-cyan-100 text-cyan-800 shadow-sm' : 'hover:text-cyan-700 hover:bg-cyan-50'"
                                        class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h7v7H4V6zm9 0h7v7h-7V6zM4 13h7v7H4v-7zm9 0h7v7h-7v-7z" />
                                    </svg>
                                    @if ($isIndex)
                                        {{ __('categories.index.view_grid') }}
                                    @else
                                        {{ __('categories.show.view_grid') }}
                                    @endif
                                </button>
                                <button type="button"
                                        @click="view = 'list'; viewMode = 'list'"
                                        :class="view === 'list' ? 'bg-cyan-100 text-cyan-800 shadow-sm' : 'hover:text-cyan-700 hover:bg-cyan-50'"
                                        class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                    @if ($isIndex)
                                        {{ __('categories.index.view_list') }}
                                    @else
                                        {{ __('categories.show.view_list') }}
                                    @endif
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div wire:loading.delay.longer class="absolute inset-0 z-10 flex items-center justify-center rounded-3xl bg-slate-900/30 backdrop-blur-sm">
                        <div class="h-10 w-10 animate-spin rounded-full border-2 border-cyan-500 border-t-transparent"></div>
                    </div>

                    @if ($isIndex)
                        {{-- Categories Display --}}
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

                                    <article class="group flex flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-cyan-300 hover:shadow-xl"
                                             :class="view === 'list' ? 'sm:flex-row' : ''">
                                        <div class="relative h-48 overflow-hidden sm:h-52" :class="view === 'list' ? 'sm:h-auto sm:w-64' : ''">
                                            @if ($image)
                                                <img src="{{ $image }}"
                                                     alt="{{ $name }}"
                                                     loading="lazy"
                                                     class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                                            @else
                                                <div class="flex h-full w-full items-center justify-center bg-slate-100 text-4xl font-semibold text-slate-700">
                                                    {{ mb_strtoupper(mb_substr($name, 0, 2)) }}
                                                </div>
                                            @endif
                                            <div class="absolute inset-x-0 bottom-0 flex items-center justify-between bg-gradient-to-t from-black/70 to-transparent px-5 pb-4 pt-12">
                                                <h3 class="text-lg font-semibold text-white drop-shadow-lg">
                                                    {{ $name }}
                                                </h3>
                                                <span class="inline-flex items-center gap-1 rounded-full bg-white/95 px-3 py-1 text-xs font-semibold text-slate-800 shadow-sm">
                                                    {{ $productCount }}
                                                    <span class="text-slate-400">{{ __('messages.products') }}</span>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="flex flex-1 flex-col justify-between gap-4 px-5 py-6">
                                            @if ($description)
                                                <p class="text-sm leading-relaxed text-slate-600 line-clamp-3">
                                                    {{ \Illuminate\Support\Str::limit(strip_tags($description), 180) }}
                                                </p>
                                            @else
                                                <p class="text-sm text-slate-400">
                                                    {{ __('categories.index.description_placeholder') }}
                                                </p>
                                            @endif

                                            <div class="flex items-center justify-center">
                                                <a href="{{ route('localized.categories.show', ['locale' => $locale, 'category' => $slug]) }}"
                                                   class="inline-flex items-center gap-2 rounded-full bg-cyan-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700">
                                                    {{ __('categories.index.view_category') }}
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>

                            <div class="mt-10">
                                {{ $categories->links() }}
                            </div>
                        @else
                            <x-shared.empty-state
                                title="{{ __('categories.index.empty_title') }}"
                                description="{{ __('categories.index.empty_description') }}"
                                icon="heroicon-o-archive-box"
                                :action-text="__('categories.index.reset_filters')"
                                :action-url="route('localized.categories.index', ['locale' => $locale])"
                            />
                        @endif
                    @else
                        {{-- Products Display --}}
                        @if ($products->count() > 0)
                            <!-- Products Grid -->
                            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                                 :class="view === 'list' ? 'sm:grid-cols-1 xl:grid-cols-1' : 'sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4'">
                                @foreach ($products as $product)
                                    <div class="animate-fade-in-up animate-delay" data-delay="{{ $loop->index * 0.1 }}">
                                        @include('livewire.home.partials.product-card', [
                                            'product' => $product,
                                            'preset' => 'featured',
                                            'attributes' => new \Illuminate\View\ComponentAttributeBag(),
                                        ])
                                    </div>
                                @endforeach
                            </div>

                            <!-- Pagination -->
                            <div class="mt-12 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                                <nav class="flex items-center justify-center" aria-label="{{ __('categories.show.pagination_navigation') }}">
                                    <div class="flex items-center justify-center">
                                        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                            @if ($products->onFirstPage())
                                                <span class="relative inline-flex items-center rounded-l-md px-2 py-2 text-slate-400 ring-1 ring-inset ring-slate-200">
                                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                                                    </svg>
                                                </span>
                                            @else
                                                <button wire:click="previousPage('page')" wire:loading.attr="disabled" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-slate-600 ring-1 ring-inset ring-slate-200 hover:bg-cyan-50 hover:text-cyan-700 focus:z-20 focus:outline-offset-0 disabled:cursor-not-allowed disabled:opacity-50">
                                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            @endif

                                            @php
                                                $currentPage = $products->currentPage();
                                                $lastPage = $products->lastPage();
                                                $startPage = max(1, $currentPage - 2);
                                                $endPage = min($lastPage, $currentPage + 2);
                                            @endphp

                                            @if ($startPage > 1)
                                                <button wire:click="gotoPage(1, 'page')" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-cyan-50 hover:text-cyan-700 focus:z-20 focus:outline-offset-0">
                                                    1
                                                </button>
                                                @if ($startPage > 2)
                                                    <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-slate-400">...</span>
                                                @endif
                                            @endif

                                            @for ($page = $startPage; $page <= $endPage; $page++)
                                                @if ($page == $currentPage)
                                                    <span class="relative z-10 inline-flex items-center bg-cyan-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-600">
                                                        {{ $page }}
                                                    </span>
                                                @else
                                                    <button wire:click="gotoPage({{ $page }}, 'page')" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-cyan-50 hover:text-cyan-700 focus:z-20 focus:outline-offset-0">
                                                        {{ $page }}
                                                    </button>
                                                @endif
                                            @endfor

                                            @if ($endPage < $lastPage)
                                                @if ($endPage < $lastPage - 1)
                                                    <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-slate-400">...</span>
                                                @endif
                                                <button wire:click="gotoPage({{ $lastPage }}, 'page')" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-cyan-50 hover:text-cyan-700 focus:z-20 focus:outline-offset-0">
                                                    {{ $lastPage }}
                                                </button>
                                            @endif

                                            @if ($products->hasMorePages())
                                                <button wire:click="nextPage('page')" wire:loading.attr="disabled" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-slate-600 ring-1 ring-inset ring-slate-200 hover:bg-cyan-50 hover:text-cyan-700 focus:z-20 focus:outline-offset-0 disabled:cursor-not-allowed disabled:opacity-50">
                                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            @else
                                                <span class="relative inline-flex items-center rounded-r-md px-2 py-2 text-slate-400 ring-1 ring-inset ring-slate-200">
                                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                                    </svg>
                                                </span>
                                            @endif
                                        </nav>
                                    </div>
                                </nav>
                            </div>
                        @else
                            <!-- Empty State -->
                            <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center shadow-sm">
                                <div class="mx-auto mb-6 h-16 w-16 text-slate-400">
                                    <svg class="h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M21 21l-4.35-4.35m1.1-4.4a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z" />
                                    </svg>
                                                                </div>
                                                                <h3 class="mb-2 text-xl font-bold text-slate-900">
                                                                    {{ __('categories.show.no_products_found') }}
                                                                </h3>
                                                                <p class="mx-auto mb-8 max-w-md text-slate-600">
                                                                    {{ __('categories.show.try_different_search') }}
                                                                </p>
                                                                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                                                                    <a href="{{ route('localized.categories.index', ['locale' => app()->getLocale()]) }}"
                                                                       class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-slate-50 px-6 py-3 text-sm font-semibold text-slate-700 transition hover:border-cyan-300 hover:bg-cyan-50 hover:text-cyan-700 shadow-sm">
                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                                        </svg>
                                                                        {{ __('categories.show.browse_categories') }}
                                                                    </a>
                                                                    <a href="{{ route('localized.products.index', ['locale' => app()->getLocale()]) }}"
                                                                       class="inline-flex items-center gap-2 rounded-full bg-cyan-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-cyan-700 shadow-sm">
                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                                        </svg>
                                                                        {{ __('categories.show.view_all_products') }}
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </section>
                                        </div>
                                    </x-container>
                                
                                    <!-- Mobile Filter Sidebar -->
                                    @if ($isIndex)
                                        @if ($sidebarOpen)
                                            <div class="fixed inset-0 z-40 lg:hidden">
                                                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
                                                     wire:click="$toggle('sidebarOpen')"></div>
                                
                                                <div class="absolute inset-y-0 right-0 w-11/12 max-w-md rounded-l-3xl bg-white shadow-2xl">
                                                    <div class="flex h-full flex-col overflow-y-auto">
                                                        <div class="flex items-center justify-between border-b border-slate-200 p-6">
                                                            <div class="space-y-2">
                                                                <span class="inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">
                                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                                                    </svg>
                                                                    {{ __('categories.index.filters_button') }}
                                                                </span>
                                                                <h2 class="text-xl font-semibold text-slate-900">{{ __('categories.index.filters_title') }}</h2>
                                                                <p class="text-sm leading-relaxed text-slate-600">
                                                                    {{ __('categories.index.filters_description') }}
                                                                </p>
                                                            </div>
                                                            <button type="button"
                                                                    class="rounded-full border border-slate-200 p-2 text-slate-500 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700"
                                                                    wire:click="$toggle('sidebarOpen')"
                                                                    aria-label="{{ __('categories.index.close') }}">
                                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                                        <div class="flex-1 space-y-6 overflow-y-auto p-6">
                                                            @livewire('category.filters', [
                                                                'isIndex' => $isIndex,
                                                                'category' => $category ?? null,
                                                            ])
                                                        </div>
                                                        <div class="border-t border-slate-200 p-6">
                                                            <x-shared.button
                                                                type="button"
                                                                variant="primary"
                                                                size="sm"
                                                                class="w-full"
                                                                wire:click="$toggle('sidebarOpen')">
                                                                {{ __('categories.index.apply_filters') }}
                                                            </x-shared.button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @else
                                        <div x-cloak x-show="showFilters" class="fixed inset-0 z-40 lg:hidden">
                                            <div @click="showFilters = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
                                            <div class="absolute inset-y-0 right-0 w-11/12 max-w-md rounded-l-3xl bg-white shadow-2xl">
                                                <div class="flex h-full flex-col overflow-y-auto">
                                                    <div class="flex items-center justify-between border-b border-slate-200 p-6">
                                                        <div class="space-y-2">
                                                            <span class="inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">
                                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                                                </svg>
                                                                {{ __('categories.show.filter') }}
                                                            </span>
                                                            <h2 class="text-xl font-semibold text-slate-900">{{ __('categories.show.filter') }}</h2>
                                                            <p class="text-sm leading-relaxed text-slate-600">
                                                                {{ __('categories.show.adjust_filters') }}
                                                            </p>
                                                        </div>
                                                        <button type="button"
                                                                class="rounded-full border border-slate-200 p-2 text-slate-500 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700"
                                                                @click="showFilters = false"
                                                                aria-label="{{ __('categories.index.close') }}">
                                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <div class="flex-1 space-y-6 overflow-y-auto p-6">
                                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                            <h4 class="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-700">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                                                </svg>
                                                                {{ __('categories.show.filters_title') }}
                                                            </h4>
                                                            <div class="space-y-1">
                                                                @livewire('components.product-filter-widget')
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="border-t border-slate-200 p-6">
                                                        <button type="button"
                                                                @click="showFilters = false"
                                                                class="w-full rounded-2xl bg-cyan-600 px-6 py-4 text-center text-sm font-bold text-white transition hover:bg-cyan-700">
                                                            {{ __('categories.show.apply_filters') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                
</div>
