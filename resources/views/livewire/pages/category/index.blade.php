@php
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

    $supportedLocales = array_values(array_filter($supportedLocales, static function ($locale): bool {
        return is_string($locale) && $locale !== '';
    }));

    $routeLocale = $request->route('locale');
    $locale = $routeLocale;

    if (! $locale || ! in_array($locale, $supportedLocales, true)) {
        $candidateLocales = array_filter([
            session('locale'),
            session('app.locale'),
            $request->cookie('app_locale'),
            config('app.locale', 'lt'),
        ], static function ($candidate): bool {
            return is_string($candidate) && $candidate !== '';
        });

        foreach ($candidateLocales as $candidate) {
            if (in_array($candidate, $supportedLocales, true)) {
                $locale = $candidate;
                break;
            }
        }
    }

    if (! $locale || ! in_array($locale, $supportedLocales, true)) {
        $locale = config('app.locale', 'lt');
    }

    app()->setLocale($locale);
    app()->instance('request_locale', $locale);

    $categoryRows = $this->categories;
    $totalCategories = $categoryRows->count();
    $from = $totalCategories > 0 ? 1 : 0;
    $to = $totalCategories > 0 ? $totalCategories : 0;
    $activeFilterCount = ($search !== '' ? 1 : 0)
        + (! empty($selectedBrandIds) ? 1 : 0)
        + (! empty($selectedCollectionIds) ? 1 : 0)
        + (! empty($selectedCategoryIds) ? 1 : 0)
        + ($inStock ? 1 : 0)
        + ($onSale ? 1 : 0)
        + ($hasProducts ? 1 : 0);
    $activeSortLabel = match ($sort) {
        'name_desc' => __('categories.index.sort_name_desc'),
        'products_desc' => __('categories.index.sort_products_desc'),
        'products_asc' => __('categories.index.sort_products_asc'),
        default => __('categories.index.sort_name_asc'),
    };
    $clearFiltersUrl = \Illuminate\Support\Facades\Route::has('localized.categories.index')
        ? route('localized.categories.index')
        : (\Illuminate\Support\Facades\Route::has('frontend.categories.index')
            ? route('frontend.categories.index')
            : url('/categories'));
@endphp

@section('meta')
    <x-meta
        :title="__('categories.index.meta_title') . ' - ' . config('app.name')"
        :description="__('categories.index.meta_description')"
        canonical="{{ url()->current() }}" />
@endsection

<div class="min-h-screen bg-sage text-dark">
    <header class="bg-dark text-sage">
        <x-container class="px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
            <nav class="mb-8 text-sm text-sage/90" aria-label="{{ __('messages.categories') }}">
                <ol class="flex flex-wrap items-center gap-2">
                    <li>
                        <a href="{{ route('home', []) }}" class="font-medium text-sage underline decoration-sage/40 underline-offset-4 transition-colors hover:text-white hover:decoration-sage">
                            {{ __('nav.home') }}
                        </a>
                    </li>
                    <li class="text-sage/70">/</li>
                    <li class="text-white">{{ __('categories.index.meta_title') }}</li>
                </ol>
            </nav>

            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                <div class="max-w-3xl space-y-4">
                    <span class="inline-flex items-center rounded-full border border-sage/40 px-4 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-sage">
                        {{ __('categories.index.badge') }}
                    </span>
                    <h1 class="text-3xl font-bold leading-tight text-white sm:text-4xl md:text-5xl">
                        {{ __('categories.index.title') }}
                    </h1>
                    <p class="text-base text-sage/90 sm:text-lg">
                        {{ __('categories.index.description') }}
                    </p>
                </div>

                <dl class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-3 lg:grid-cols-1 lg:justify-items-end">
                    <div class="border-b border-sage/40 pb-2 lg:min-w-56">
                        <dt class="text-sage/70">{{ __('messages.categories') }}</dt>
                        <dd class="mt-1 text-lg font-semibold text-white">{{ number_format($totalCategories) }}</dd>
                    </div>
                    <div class="border-b border-sage/40 pb-2 lg:min-w-56">
                        <dt class="text-sage/70">{{ __('categories.index.filters_title') }}</dt>
                        <dd class="mt-1 text-lg font-semibold text-white">
                            @if ($activeFilterCount > 0)
                                {{ __('categories.index.filters_active', ['count' => $activeFilterCount]) }}
                            @else
                                {{ __('categories.index.filters_none') }}
                            @endif
                        </dd>
                    </div>
                    <div class="border-b border-sage/40 pb-2 lg:min-w-56">
                        <dt class="text-sage/70">{{ __('categories.index.sort') }}</dt>
                        <dd class="mt-1 text-lg font-semibold text-white">{{ $activeSortLabel }}</dd>
                    </div>
                </dl>
            </div>
        </x-container>
    </header>

    <x-container class="px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
        <section class="border border-dark/25 bg-sage/60">
            <div class="grid gap-4 p-4 sm:p-6 lg:grid-cols-[minmax(0,1fr)_240px_auto] lg:items-end">
                <div>
                    <label for="category-search" class="text-sm font-semibold text-dark">{{ __('messages.search_categories') }}</label>
                    <input
                        id="category-search"
                        type="search"
                        wire:model.live.debounce.400ms="search"
                        placeholder="{{ __('messages.type_to_filter_categories') }}"
                        class="mt-2 w-full border border-dark/30 bg-white/80 px-4 py-2.5 text-sm text-dark placeholder:text-dark/50 focus:border-dark focus:outline-none focus:ring-2 focus:ring-dark/20"
                    />
                </div>

                <div>
                    <label for="category-sort" class="text-sm font-semibold text-dark">{{ __('categories.index.sort') }}</label>
                    <select
                        id="category-sort"
                        wire:model.live="sort"
                        class="mt-2 w-full border border-dark/30 bg-white/80 px-4 py-2.5 text-sm font-medium text-dark focus:border-dark focus:outline-none focus:ring-2 focus:ring-dark/20"
                    >
                        <option value="name_asc">{{ __('categories.index.sort_name_asc') }}</option>
                        <option value="name_desc">{{ __('categories.index.sort_name_desc') }}</option>
                        <option value="products_desc">{{ __('categories.index.sort_products_desc') }}</option>
                        <option value="products_asc">{{ __('categories.index.sort_products_asc') }}</option>
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button
                        type="button"
                        wire:click="openSidebar"
                        class="inline-flex items-center border border-dark bg-dark px-4 py-2.5 text-sm font-semibold text-sage transition-colors hover:bg-dark/90 lg:hidden"
                    >
                        {{ __('categories.index.filters_button') }}
                    </button>
                    @if ($activeFilterCount > 0 || $sort !== 'name_asc')
                        <a href="{{ $clearFiltersUrl }}" class="inline-flex items-center border border-dark/30 px-4 py-2.5 text-sm font-semibold text-dark transition-colors hover:border-dark hover:bg-sage/50">
                            {{ __('categories.index.reset_filters') }}
                        </a>
                    @endif
                </div>
            </div>

            <p class="border-t border-dark/20 px-4 py-3 text-sm text-dark/75 sm:px-6">
                @if ($totalCategories > 0)
                    {{ __('categories.index.showing_results', ['from' => $from, 'to' => $to, 'total' => $totalCategories]) }}
                @else
                    {{ __('categories.index.no_results') }}
                @endif
            </p>
        </section>

        <div class="mt-8 grid gap-8 lg:grid-cols-12 lg:items-start xl:gap-10">
            <aside class="hidden lg:col-span-4 lg:block xl:col-span-3">
                <div class="sticky top-20 space-y-4">
                    <section class="border border-dark/25 bg-white/60 p-5">
                        <span class="inline-flex items-center border border-dark/20 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-dark/70">
                            {{ __('categories.index.filters_button') }}
                        </span>
                        <h2 class="mt-4 text-xl font-semibold text-dark">{{ __('categories.index.filters_title') }}</h2>
                        <p class="mt-2 text-sm leading-6 text-dark/70">{{ __('categories.index.filters_description') }}</p>
                    </section>

                    <div class="space-y-4">
                        @include('livewire.pages.category.partials.filters', ['variant' => 'desktop', 'showSearch' => false])
                    </div>
                </div>
            </aside>

            <div class="relative lg:col-span-8 xl:col-span-9">
                <div wire:loading.delay.longer class="absolute inset-0 z-10 bg-white/70"></div>

                @if ($totalCategories > 0)
                    <section class="border border-dark/25 bg-white/50">
                        <div class="hidden overflow-x-auto md:block">
                            <table class="min-w-full table-auto border-collapse">
                                <thead class="sticky top-0 z-10 bg-dark text-sage">
                                    <tr class="text-left text-xs uppercase tracking-wider">
                                        <th scope="col" class="w-12 px-4 py-3 text-right font-semibold sm:px-6">#</th>
                                        <th scope="col" class="px-4 py-3 font-semibold sm:px-6">{{ __('messages.category') }}</th>
                                        <th scope="col" class="px-4 py-3 font-semibold sm:px-6">{{ __('messages.description') }}</th>
                                        <th scope="col" class="px-4 py-3 text-right font-semibold sm:px-6">{{ __('messages.products') }}</th>
                                        <th scope="col" class="px-4 py-3 text-right font-semibold sm:px-6">{{ __('messages.view') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-dark/15">
                                    @foreach ($categoryRows as $row)
                                        @php
                                            /** @var \App\Models\Category $category */
                                            $category = $row['category'];
                                            $depth = min((int) ($row['depth'] ?? 0), 4);
                                            $name = method_exists($category, 'trans')
                                                ? ($category->trans('name') ?? $category->name)
                                                : $category->name;
                                            $description = method_exists($category, 'trans')
                                                ? ($category->trans('description') ?? $category->description)
                                                : $category->description;
                                            $productCount = (int) ($category->products_count
                                                ?? ($category->published_products_count ?? ($category->products?->count() ?? 0)));
                                            $rowNumber = $loop->iteration;
                                            $categoryUrl = \Illuminate\Support\Facades\Route::has('localized.categories.show')
                                                ? route('localized.categories.show', ['category' => $category])
                                                : route('frontend.categories.show', ['category' => $category]);
                                        @endphp
                                        <tr class="align-top odd:bg-white/40 even:bg-sage/20 hover:bg-sage/50">
                                            <td class="px-4 py-4 text-right text-sm font-semibold text-dark/55 sm:px-6">
                                                {{ $rowNumber }}
                                            </td>
                                            <td class="px-4 py-4 sm:px-6">
                                                <div class="flex items-center" style="padding-left: {{ $depth * 1 }}rem;">
                                                    @if ($depth > 0)
                                                        <span class="mr-3 h-px w-4 bg-dark/25"></span>
                                                    @endif
                                                    <a href="{{ $categoryUrl }}" class="text-base font-semibold text-dark underline decoration-dark/40 underline-offset-4 transition-colors hover:decoration-dark">
                                                        {{ $name }}
                                                    </a>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 text-sm leading-6 text-dark/75 sm:px-6">
                                                @if ($description)
                                                    {{ \Illuminate\Support\Str::limit(strip_tags((string) $description), 180) }}
                                                @else
                                                    {{ __('categories.index.description_placeholder') }}
                                                @endif
                                            </td>
                                            <td class="px-4 py-4 text-right text-sm font-semibold text-dark sm:px-6">
                                                {{ number_format($productCount) }}
                                            </td>
                                            <td class="px-4 py-4 text-right sm:px-6">
                                                <a href="{{ $categoryUrl }}" class="text-sm font-semibold text-dark underline decoration-dark/40 underline-offset-4 transition-colors hover:decoration-dark">
                                                    {{ __('categories.index.view_category') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <ul class="divide-y divide-dark/15 md:hidden">
                            @foreach ($categoryRows as $row)
                                @php
                                    /** @var \App\Models\Category $category */
                                    $category = $row['category'];
                                    $depth = min((int) ($row['depth'] ?? 0), 4);
                                    $name = method_exists($category, 'trans')
                                        ? ($category->trans('name') ?? $category->name)
                                        : $category->name;
                                    $description = method_exists($category, 'trans')
                                        ? ($category->trans('description') ?? $category->description)
                                        : $category->description;
                                    $productCount = (int) ($category->products_count
                                        ?? ($category->published_products_count ?? ($category->products?->count() ?? 0)));
                                    $rowNumber = $loop->iteration;
                                    $categoryUrl = \Illuminate\Support\Facades\Route::has('localized.categories.show')
                                        ? route('localized.categories.show', ['category' => $category])
                                        : route('frontend.categories.show', ['category' => $category]);
                                @endphp
                                <li class="space-y-3 px-4 py-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0 space-y-1" style="padding-left: {{ $depth * 0.75 }}rem;">
                                            <span class="inline-flex border border-dark/25 px-2 py-0.5 text-[10px] font-semibold text-dark/70">
                                                {{ str_pad((string) $rowNumber, 2, '0', STR_PAD_LEFT) }}
                                            </span>
                                            <a href="{{ $categoryUrl }}" class="block text-base font-semibold text-dark underline decoration-dark/40 underline-offset-4">
                                                {{ $name }}
                                            </a>
                                        </div>
                                        <span class="shrink-0 border border-dark/25 px-2.5 py-1 text-xs font-semibold text-dark">
                                            {{ number_format($productCount) }} {{ __('messages.products') }}
                                        </span>
                                    </div>
                                    <p class="text-sm leading-6 text-dark/75">
                                        @if ($description)
                                            {{ \Illuminate\Support\Str::limit(strip_tags((string) $description), 120) }}
                                        @else
                                            {{ __('categories.index.description_placeholder') }}
                                        @endif
                                    </p>
                                    <a href="{{ $categoryUrl }}" class="inline-flex text-sm font-semibold text-dark underline decoration-dark/40 underline-offset-4">
                                        {{ __('categories.index.view_category') }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @else
                    <section class="border border-dashed border-dark/30 bg-sage/40 p-10 text-center">
                        <h2 class="text-xl font-semibold text-dark">{{ __('categories.index.empty_title') }}</h2>
                        <p class="mt-2 text-sm text-dark/70">{{ __('categories.index.empty_description') }}</p>
                        <a href="{{ $clearFiltersUrl }}" class="mt-6 inline-flex items-center border border-dark bg-dark px-5 py-2.5 text-sm font-semibold text-sage transition-colors hover:bg-dark/90">
                            {{ __('categories.index.reset_filters') }}
                        </a>
                    </section>
                @endif
            </div>
        </div>
    </x-container>

    @if ($sidebarOpen)
        <div class="fixed inset-0 z-40 lg:hidden">
            <div class="absolute inset-0 bg-dark/60 backdrop-blur-sm" wire:click="closeSidebar"></div>
            <div class="absolute inset-y-0 right-0 w-full max-w-lg bg-sage shadow-2xl sm:w-11/12">
                <div class="flex h-full flex-col overflow-y-auto">
                    <div class="flex items-center justify-between border-b border-dark/15 p-6">
                        <div class="space-y-2">
                            <span class="inline-flex items-center border border-dark/20 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-dark/70">
                                {{ __('categories.index.filters_button') }}
                            </span>
                            <h2 class="text-xl font-semibold text-dark">{{ __('categories.index.filters_title') }}</h2>
                            <p class="text-sm leading-relaxed text-dark/70">{{ __('categories.index.filters_description') }}</p>
                        </div>
                        <button
                            type="button"
                            class="rounded-full border border-dark/20 p-2 text-dark/60 transition hover:border-dark hover:bg-white/60 hover:text-dark"
                            wire:click="closeSidebar"
                            aria-label="{{ __('categories.index.close') }}"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="flex-1 space-y-4 overflow-y-auto p-6">
                        @include('livewire.pages.category.partials.filters', ['variant' => 'mobile', 'showSearch' => false])
                    </div>

                    <div class="border-t border-dark/15 p-6">
                        <button
                            type="button"
                            class="inline-flex w-full items-center justify-center border border-dark bg-dark px-5 py-3 text-sm font-semibold text-sage transition-colors hover:bg-dark/90"
                            wire:click="closeSidebar"
                        >
                            {{ __('categories.index.apply_filters') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
