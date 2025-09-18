@section('meta')
    <x-meta
        :title="__('Categories') . ' - ' . config('app.name')"
        :description="__('Explore our comprehensive range of categories')"
        canonical="{{ url()->current() }}" />
@endsection

@php
    $locale = app()->getLocale();
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

<div class="bg-slate-50 dark:bg-gray-900">
    <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-100/40 via-white to-slate-100/60 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900"></div>
        <div class="absolute inset-y-10 -left-24 hidden h-64 w-64 rounded-full bg-blue-500/10 blur-3xl lg:block"></div>
        <x-container class="relative px-4 py-12 sm:py-16">
            <nav class="text-xs font-medium uppercase tracking-[0.3em] text-slate-500" aria-label="{{ __('Breadcrumb') }}">
                <ol class="flex items-center gap-3">
                    <li>
                        <a href="{{ route('localized.home', ['locale' => $locale]) }}"
                           class="inline-flex items-center gap-2 text-slate-600 transition hover:text-blue-600">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h12a1 1 0 001-1V10" />
                            </svg>
                            {{ __('Home') }}
                        </a>
                    </li>
                    <li class="text-slate-400">/</li>
                    <li class="text-slate-700">{{ __('Categories') }}</li>
                </ol>
            </nav>

            <div class="mt-8 flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl space-y-5">
                    <span class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-4 py-1 text-[11px] font-semibold uppercase tracking-[0.35em] text-blue-600">
                        {{ __('Catalogue overview') }}
                    </span>
                    <h1 class="text-3xl font-bold leading-tight text-slate-900 sm:text-4xl md:text-5xl">
                        {{ __('Discover every department in StatyBae Commerce') }}
                    </h1>
                    <p class="text-base text-slate-600 sm:text-lg">
                        {{ __('Browse structured categories curated by our merchandisers to help professionals and DIY enthusiasts find the right materials faster.') }}
                    </p>
                </div>

                <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-end sm:gap-6">
                    <div class="rounded-2xl border border-blue-200 bg-white px-4 py-3 text-sm font-semibold text-blue-600 shadow-sm">
                        {{ __(':count categories in catalogue', ['count' => number_format($totalCategories)]) }}
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-500 shadow-sm">
                        @if ($activeFilterCount > 0)
                            {{ __(':count filters active', ['count' => $activeFilterCount]) }}
                        @else
                            {{ __('No filters applied') }}
                        @endif
                    </div>
                    <button type="button"
                            wire:click="$toggle('sidebarOpen')"
                            wire:confirm="{{ __('translations.confirm_toggle_sidebar') }}"
                            class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm transition hover:border-blue-300 hover:text-blue-600 lg:hidden">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h6M3 12h6m-6 7h6M13 5h8M13 12h8m-8 7h8" />
                        </svg>
                        {{ __('Filters') }}
                    </button>
                </div>
            </div>
        </x-container>
    </div>

    <x-container class="relative -mt-12 px-4 pb-16">
        <div class="grid gap-8 lg:grid-cols-12">
            <aside class="hidden lg:col-span-3 lg:block">
                <x-shared.filter-sidebar
                    title="{{ __('Refine catalogue') }}"
                    description="{{ __('Combine availability, price, brands and collections to focus your search.') }}"
                >
                    @include('livewire.pages.category.partials.filters', ['variant' => 'desktop'])
                </x-shared.filter-sidebar>
            </aside>

            <section class="lg:col-span-9 space-y-6" x-data="{ view: 'grid' }">
                <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex flex-wrap items-center gap-3 text-sm text-slate-500">
                            <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ __('Real-time results') }}
                            </span>
                            @if ($from && $to)
                                <span>{{ __('Showing :from–:to of :total results', ['from' => $from, 'to' => $to, 'total' => $totalCategories]) }}</span>
                            @else
                                <span>{{ __('No results to display') }}</span>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600">
                                <label for="sort" class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    {{ __('Sort') }}
                                </label>
                                <select id="sort" wire:model.live="sort" class="border-0 bg-transparent text-sm font-medium focus:outline-none focus:ring-0">
                                    <option value="name_asc">{{ __('Name (A–Z)') }}</option>
                                    <option value="name_desc">{{ __('Name (Z–A)') }}</option>
                                    <option value="products_desc">{{ __('Most products') }}</option>
                                    <option value="products_asc">{{ __('Fewest products') }}</option>
                                </select>
                            </div>

                            <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600">
                                <label for="per-page" class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    {{ __('Per page') }}
                                </label>
                                <select id="per-page" wire:model.live="perPage" class="border-0 bg-transparent text-sm font-medium focus:outline-none focus:ring-0">
                                    <option value="12">12</option>
                                    <option value="24">24</option>
                                    <option value="36">36</option>
                                    <option value="48">48</option>
                                </select>
                            </div>

                            <div class="hidden items-center gap-1 rounded-xl border border-slate-200 bg-white p-1 text-slate-500 shadow-sm md:flex">
                                <button type="button"
                                        @click="view = 'grid'"
                                        :class="view === 'grid' ? 'bg-blue-600 text-white shadow-sm' : 'hover:text-blue-600'"
                                        class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h7v7H4V6zm9 0h7v7h-7V6zM4 13h7v7H4v-7zm9 0h7v7h-7v-7z" />
                                    </svg>
                                    {{ __('Grid') }}
                                </button>
                                <button type="button"
                                        @click="view = 'list'"
                                        :class="view === 'list' ? 'bg-blue-600 text-white shadow-sm' : 'hover:text-blue-600'"
                                        class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                    {{ __('List') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div wire:loading.delay.longer class="absolute inset-0 z-10 flex items-center justify-center rounded-3xl bg-white/80 backdrop-blur-sm">
                        <div class="h-10 w-10 animate-spin rounded-full border-2 border-blue-500 border-t-transparent"></div>
                    </div>

                    @if ($categories->count() > 0)
                        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3" :class="view === 'list' ? 'sm:grid-cols-1 xl:grid-cols-1' : 'sm:grid-cols-2 xl:grid-cols-3'">
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

                                <article class="group flex flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:border-blue-300 hover:shadow-xl"
                                         :class="view === 'list' ? 'sm:flex-row' : ''">
                                    <div class="relative h-48 overflow-hidden sm:h-52" :class="view === 'list' ? 'sm:h-auto sm:w-64' : ''">
                                        @if ($image)
                                            <img src="{{ $image }}"
                                                 alt="{{ $name }}"
                                                 loading="lazy"
                                                 class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                                        @else
                                            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-blue-100 to-indigo-100 text-4xl font-semibold text-blue-600">
                                                {{ mb_strtoupper(mb_substr($name, 0, 2)) }}
                                            </div>
                                        @endif
                                        <div class="absolute inset-x-0 bottom-0 flex items-center justify-between bg-gradient-to-t from-black/70 to-transparent px-5 pb-4 pt-12">
                                            <h3 class="text-lg font-semibold text-white drop-shadow-lg">
                                                {{ $name }}
                                            </h3>
                                            <span class="inline-flex items-center gap-1 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-slate-700 shadow-sm">
                                                {{ $productCount }}
                                                <span class="text-slate-400">{{ trans_choice('products', $productCount) }}</span>
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
                                                {{ __('Detailed description coming soon.') }}
                                            </p>
                                        @endif

                                        <div class="flex items-center justify-center">
                                            <a href="{{ route('localized.categories.show', ['locale' => $locale, 'category' => $slug]) }}"
                                               class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-600 transition hover:bg-blue-100">
                                                {{ __('View category') }}
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
                            title="{{ __('No categories found') }}"
                            description="{{ __('Try adjusting your filters or search terms to discover available categories.') }}"
                            icon="heroicon-o-archive-box"
                            :action-text="__('Reset filters')"
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

            <div class="absolute inset-y-0 right-0 w-11/12 max-w-md rounded-l-3xl bg-white shadow-2xl">
                <x-shared.filter-sidebar
                    :sticky="false"
                    class="h-full overflow-y-auto p-6"
                    title="{{ __('Filters') }}"
                    description="{{ __('Adjust filters to personalise the catalogue view.') }}"
                >
                    <x-slot name="headerActions">
                        <button type="button"
                                class="rounded-full border border-slate-200 p-2 text-slate-500 transition hover:border-blue-300 hover:text-blue-600"
                                wire:click="$toggle('sidebarOpen')"
                                wire:confirm="{{ __('translations.confirm_toggle_sidebar') }}"
                                aria-label="{{ __('Close') }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </x-slot>

                    @include('livewire.pages.category.partials.filters', ['variant' => 'mobile'])

                    <x-slot name="footer">
                        <x-shared.button
                            type="button"
                            variant="primary"
                            size="sm"
                            class="w-full"
                            wire:click="$toggle('sidebarOpen')"
                        >
                            {{ __('Apply filters') }}
                        </x-shared.button>
                    </x-slot>
                </x-shared.filter-sidebar>
            </div>
        </div>
    @endif

    <x-filament-actions::modals />
</div>
