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

<div x-data="{ showFilters: false }" class="min-h-screen bg-slate-50">
    <div class="bg-dark text-sage">
        <div class="mx-auto w-full max-w-8xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
            <nav class="mb-8 text-sm text-sage/80" aria-label="{{ __('ui.breadcrumb') }}">
                <ol class="flex flex-wrap items-center gap-2">
                    <li>
                        <a href="{{ route('home', []) }}"
                           class="text-sage/80 transition-colors hover:text-sage">
                            {{ __('frontend.navigation.home') }}
                        </a>
                    </li>
                    <li class="text-sage/80">/</li>
                    @if (!$isIndex)
                        <li>
                            <a href="{{ route('frontend.categories.index', []) }}"
                               class="text-sage/80 transition-colors hover:text-sage">
                                {{ __('categories.index.meta_title') }}
                            </a>
                        </li>
                        <li class="text-sage/80">/</li>
                        <li class="text-sage/80">{{ $category->name }}</li>
                    @else
                        <li class="text-sage/80">{{ __('categories.index.meta_title') }}</li>
                    @endif
                </ol>
            </nav>

            <div class="space-y-6">
                <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                    <div class="space-y-4 lg:max-w-none">
                        <span class="inline-flex items-center gap-2 rounded-full border border-sage bg-sage px-4 py-1 text-[11px] font-semibold uppercase tracking-[0.35em] text-dark">
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
                            <p class="max-w-4xl text-base text-white/90 sm:text-lg">
                                {{ __('categories.index.description') }}
                            </p>
                        @elseif (!empty($category->description))
                            <p class="max-w-4xl text-base text-white/90 sm:text-lg">
                                {{ $category->description }}
                            </p>
                        @endif
                    </div>

                    <div class="flex flex-col items-start gap-2 sm:flex-row sm:items-end sm:gap-4">
                        @if ($isIndex)
                            @php
                                $categoryRows = $this->categories;
                                $totalCategories = $categoryRows->count();
                                $from = $totalCategories > 0 ? 1 : null;
                                $to = $totalCategories > 0 ? $totalCategories : null;
                            @endphp
                            <button type="button"
                                    wire:click="$toggle('sidebarOpen')"
                                    wire:confirm="{{ __('translations.confirm_toggle_sidebar') }}"
                                    class="inline-flex items-center gap-2 rounded-full border border-sage bg-sage px-4 py-2 text-sm font-semibold text-dark shadow-sm transition-colors hover:bg-sage/90 lg:hidden">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h6M3 12h6m-6 7h6M13 5h8M13 12h8m-8 7h8" />
                                </svg>
                                {{ __('categories.index.filters_button') }}
                            </button>
                        @else
                            <div class="rounded-2xl border border-sage/30 bg-sage/10 px-3 py-2 text-sm font-semibold text-white shadow-sm">
                                {{ __('categories.show.products_count', ['count' => number_format($products->total())]) }}
                            </div>
                            <div class="rounded-2xl border border-sage/30 bg-sage/10 px-3 py-2 text-sm text-white/80 shadow-sm">
                                @if ($products->firstItem() && $products->lastItem())
                                    {{ __('categories.show.showing', ['from' => $products->firstItem(), 'to' => $products->lastItem()]) }}
                                @else
                                    {{ __('categories.show.no_products') }}
                                @endif
                            </div>
                            <button type="button"
                                    @click="showFilters = true"
                                    class="inline-flex items-center gap-2 rounded-full border border-sage bg-sage px-4 py-2 text-sm font-semibold text-dark shadow-sm transition-colors hover:bg-sage/90 lg:hidden">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h6M3 12h6m-6 7h6M13 5h8M13 12h8m-8 7h8" />
                                </svg>
                                {{ __('categories.show.filter') }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
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

        <div class="grid gap-8 lg:grid-cols-12 lg:items-start">
            <aside class="hidden lg:col-span-3 lg:block">
                <div class="lg:sticky lg:top-24 lg:self-start">
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
                                    @livewire('components.product-filter-widget', ['categoryId' => $category->id])
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            </aside>

            <section class="lg:col-span-9 space-y-6">
                <!-- Sort and Filter Controls -->
                @if ($isIndex)
                    <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex flex-wrap items-center gap-3 text-sm text-slate-700">
                                @if ($from && $to)
                                    <span class="whitespace-nowrap text-slate-500">{{ __('categories.index.showing_results', ['from' => $from, 'to' => $to, 'total' => $totalCategories]) }}</span>
                                @else
                                    <span class="whitespace-nowrap text-slate-500">{{ __('categories.index.no_results') }}</span>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
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
                            </div>
                        </div>
                    </div>
                @else
                    <div class="rounded-3xl border border-sage/30 bg-dark p-6 shadow-lg">
                        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex flex-wrap items-center gap-3 text-sm font-medium">
                                @if ($products->firstItem() && $products->lastItem())
                                    <span class="text-white/80">
                                        {{ __('categories.show.showing_results', ['from' => $products->firstItem(), 'to' => $products->lastItem(), 'total' => $products->total()]) }}
                                    </span>
                                @else
                                    <span class="text-white/80">{{ __('categories.show.no_results') }}</span>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center gap-3 text-sm">
                                <label for="sort-by" class="text-white/80 font-semibold">
                                    {{ __('categories.show.sort') }}
                                </label>
                                <select id="sort-by" wire:model.live="sortBy" class="rounded-full border border-sage/30 bg-sage/10 px-4 py-2 text-sm font-medium text-white focus:border-sage focus:outline-none focus:ring-2 focus:ring-sage">
                                    <option value="created_at" class="bg-dark text-white">{{ __('categories.show.sort_newest') }}</option>
                                    <option value="name" class="bg-dark text-white">{{ __('categories.show.sort_name') }}</option>
                                    <option value="price" class="bg-dark text-white">{{ __('categories.show.sort_price') }}</option>
                                </select>

                                <label for="sort-direction" class="text-white/80 font-semibold">
                                    {{ __('categories.show.order') }}
                                </label>
                                <select id="sort-direction" wire:model.live="sortDirection" class="rounded-full border border-sage/30 bg-sage/10 px-4 py-2 text-sm font-medium text-white focus:border-sage focus:outline-none focus:ring-2 focus:ring-sage">
                                    <option value="asc" class="bg-dark text-white">{{ __('categories.show.order_ascending') }}</option>
                                    <option value="desc" class="bg-dark text-white">{{ __('categories.show.order_descending') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="relative">
                    <div wire:loading.delay.longer class="absolute inset-0 z-10 flex items-center justify-center rounded-3xl bg-slate-900/30 backdrop-blur-sm">
                        <div class="h-10 w-10 animate-spin rounded-full border-2 border-cyan-500 border-t-transparent"></div>
                    </div>

                    @if ($isIndex)
                        {{-- Categories Display --}}
                        @if ($categoryRows->count() > 0)
                            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead class="bg-slate-50">
                                            <tr>
                                                <th scope="col" class="w-[28%] px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                    {{ __('messages.name') }}
                                                </th>
                                                <th scope="col" class="w-[46%] px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                    {{ __('messages.description') }}
                                                </th>
                                                <th scope="col" class="w-[12%] px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                    {{ __('messages.products') }}
                                                </th>
                                                <th scope="col" class="w-[14%] px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                    {{ __('messages.view') }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200 bg-white">
                                            @foreach ($categoryRows as $row)
                                                @php
                                                    $category = $row['category'];
                                                    $depth = (int) ($row['depth'] ?? 0);
                                                    $slug = method_exists($category, 'trans')
                                                        ? ($category->trans('slug') ?? $category->slug)
                                                        : ($category->slug ?? (is_string($category) ? $category : null));
                                                    $name = method_exists($category, 'trans')
                                                        ? ($category->trans('name') ?? $category->name)
                                                        : $category->name;
                                                    $description = method_exists($category, 'trans')
                                                        ? ($category->trans('description') ?? $category->description)
                                                        : $category->description;
                                                    $productCount = $category->products_count
                                                        ?? ($category->published_products_count ?? ($category->products?->count() ?? 0));
                                                @endphp

                                                <tr class="group transition-colors even:bg-slate-50/40 hover:bg-slate-50">
                                                    <td class="px-6 py-4 align-middle">
                                                        <div class="flex items-center" style="padding-left: {{ $depth * 1.25 }}rem;">
                                                            @if ($depth > 0)
                                                                <span class="mr-2 text-xs text-slate-400">&gt;</span>
                                                            @endif
                                                            <a href="{{ route('frontend.categories.show', ['category' => $slug]) }}"
                                                               class="font-semibold text-slate-900 transition group-hover:text-slate-700">
                                                                {{ $name }}
                                                            </a>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 align-middle">
                                                        @if ($description)
                                                            <p class="max-w-2xl leading-relaxed text-slate-600">
                                                                {{ \Illuminate\Support\Str::limit(strip_tags($description), 180) }}
                                                            </p>
                                                        @else
                                                            <p class="text-slate-400">
                                                                {{ __('categories.index.description_placeholder') }}
                                                            </p>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 align-middle">
                                                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                                            {{ $productCount }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 text-right align-middle">
                                                        <a href="{{ route('frontend.categories.show', ['category' => $slug]) }}"
                                                           class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">
                                                            {{ __('categories.index.view_category') }}
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <x-shared.empty-state
                                title="{{ __('categories.index.empty_title') }}"
                                description="{{ __('categories.index.empty_description') }}"
                                icon="heroicon-o-archive-box"
                                :action-text="__('categories.index.reset_filters')"
                                :action-url="route('frontend.categories.index', [])"
                            />
                        @endif
                    @else
                        {{-- Products Display --}}
                        @if ($products->count() > 0)
                            <!-- Products Grid -->
                            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
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
                                        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="{{ __('messages.pagination') }}">
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
                                                                    <a href="{{ route('frontend.categories.index', []) }}"
                                                                       class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-slate-50 px-6 py-3 text-sm font-semibold text-slate-700 transition hover:border-cyan-300 hover:bg-cyan-50 hover:text-cyan-700 shadow-sm">
                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                                        </svg>
                                                                        {{ __('categories.show.browse_categories') }}
                                                                    </a>
                                                                    <a href="{{ route('frontend.products.index', []) }}"
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
                                                                @livewire('components.product-filter-widget', ['categoryId' => $category->id])
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
