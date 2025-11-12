@section('meta')
    <x-meta
        :title="__('frontend/brands.meta.title') . ' - ' . config('app.name')"
        :description="__('frontend/brands.meta.description')"
        canonical="{{ url()->current() }}" />
@endsection

@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */
    $paginator = $this->brands;
    $totalBrands = $paginator->total();
    $activeFilterCount = collect([
        filled($this->search ?? ''),
        ($this->sortBy ?? 'name') !== 'name',
    ])->filter()->count();
@endphp

<div class="bg-sage">
    <div class="bg-dark text-sage">
        <x-container class="px-4 py-12 sm:py-16">
            <nav class="text-xs font-medium uppercase tracking-[0.3em] text-sage/80" aria-label="{{ __('Breadcrumb') }}">
                <ol class="flex items-center gap-3">
                    <li>
                        <a href="{{ route('localized.home', ['locale' => app()->getLocale()]) }}"
                           class="inline-flex items-center gap-2 text-sage transition hover:text-white">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h12a1 1 0 001-1V10" />
                            </svg>
                            {{ __('frontend.navigation.home') }}
                        </a>
                    </li>
                    <li class="text-sage/60">/</li>
                    <li class="text-white">{{ __('shared.brands') }}</li>
                </ol>
            </nav>

            <div class="mt-8 flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl space-y-5">
                    <span class="inline-flex items-center gap-2 rounded-full border border-sage bg-sage px-4 py-1 text-[11px] font-semibold uppercase tracking-[0.35em] text-dark">
                        {{ __('frontend/brands.hero.badge') ?: __('Catalogue brands') }}
                    </span>
                    <h1 class="text-3xl font-bold leading-tight text-white sm:text-4xl md:text-5xl">
                        {{ __('frontend/brands.hero.title') }}
                    </h1>
                    <p class="text-base text-sage sm:text-lg">
                        {{ __('frontend/brands.hero.description') }}
                    </p>
                </div>

                <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-end sm:gap-6">
                    <div class="rounded-2xl border border-sage/30 bg-sage/10 px-4 py-3 text-sm font-semibold text-sage shadow-sm">
                        {{ __(':count brands in catalogue', ['count' => number_format($totalBrands)]) }}
                    </div>
                    <div class="rounded-2xl border border-sage/30 bg-sage/10 px-4 py-3 text-sm text-sage/80 shadow-sm">
                        @if ($activeFilterCount > 0)
                            {{ __(':count filters active', ['count' => $activeFilterCount]) }}
                        @else
                            {{ __('No filters applied') }}
                        @endif
            </div>
                    <button type="button"
                            wire:click="$toggle('sidebarOpen')"
                            wire:confirm="{{ __('translations.confirm_toggle_sidebar') }}"
                            class="inline-flex items-center gap-2 rounded-full border border-sage/30 bg-sage/10 px-4 py-2 text-sm font-semibold text-sage shadow-sm transition hover:border-sage hover:bg-sage/20 lg:hidden">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h6M3 12h6m-6 7h6M13 5h8M13 12h8m-8 7h8" />
                        </svg>
                        {{ __('Filters') }}
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
                            {{ __('Filters') }}
                            </span>
                        <h2 class="text-xl font-semibold text-white">{{ __('frontend/brands.filters.title') }}</h2>
                        @if(__('frontend/brands.filters.description'))
                            <p class="text-sm leading-relaxed text-sage/80">
                                {{ __('frontend/brands.filters.description') }}
                            </p>
                        @endif
                    </div>
                    <div class="space-y-6">
                        @include('livewire.pages.brand.partials.filters', ['variant' => 'desktop'])
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
                                {{ __('Real-time results') }}
                            </span>
                            @if ($paginator->count() > 0)
                                <span class="text-sage/80">{{ __('Showing :from–:to of :total results', ['from' => $paginator->firstItem() ?? 0, 'to' => $paginator->lastItem() ?? 0, 'total' => $totalBrands]) }}</span>
                            @else
                                <span class="text-sage/80">{{ __('No results to display') }}</span>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <div class="flex items-center gap-2 rounded-xl border border-sage/30 bg-dark/30 px-3 py-2 text-sm text-sage">
                                <label for="sort" class="text-xs font-semibold uppercase tracking-wide text-sage/60">
                                    {{ __('Sort') }}
                                </label>
                                <select id="sort" wire:model.live="sortBy" class="border-0 bg-transparent text-sm font-medium text-sage focus:outline-none focus:ring-0">
                                    <option value="name" class="bg-dark text-sage">{{ __('frontend/brands.filters.options.name') }}</option>
                                    <option value="name_desc" class="bg-dark text-sage">{{ __('frontend/brands.filters.options.name_desc') }}</option>
                                    <option value="products_count" class="bg-dark text-sage">{{ __('frontend/brands.filters.options.products_count') }}</option>
                                    <option value="created_at" class="bg-dark text-sage">{{ __('frontend/brands.filters.options.created_at') }}</option>
                                    <option value="featured" class="bg-dark text-sage">{{ __('frontend/brands.filters.options.featured') }}</option>
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
                                    {{ __('Grid') }}
                                </button>
                                <button type="button"
                                        @click="view = 'list'"
                                        :class="view === 'list' ? 'bg-sage text-dark shadow-sm' : 'hover:text-white hover:bg-sage/10'"
                                        class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold transition text-sage">
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
                        <div class="h-10 w-10 animate-spin rounded-full border-2 border-brand-primary border-t-transparent"></div>
                            </div>

                    @if ($paginator->count() > 0)
                        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3" :class="view === 'list' ? 'sm:grid-cols-1 xl:grid-cols-1' : 'sm:grid-cols-2 xl:grid-cols-3'">
                            @foreach ($paginator as $brand)
                                @php
                                    $slug = $brand->slug ?? '';
                                    $name = $brand->name ?? '';
                                    $description = $brand->description ?? '';
                                    $logo = $brand->getFirstMediaUrl('logo');
                                    $productCount = $brand->products_count ?? 0;
                                    $isFeatured = $brand->is_featured ?? false;
                                @endphp

                                <article class="group flex flex-col overflow-hidden rounded-3xl border border-ash/30 bg-white shadow-sm transition hover:-translate-y-1 hover:border-ash/60 hover:shadow-xl"
                                         :class="view === 'list' ? 'sm:flex-row' : ''">
                                    <div class="relative h-48 overflow-hidden sm:h-52" :class="view === 'list' ? 'sm:h-auto sm:w-64' : ''">
                                        @if ($logo)
                                            <div class="flex h-full w-full items-center justify-center bg-ash/10 p-8">
                                                <img src="{{ $logo }}"
                                                     alt="{{ $name }}"
                                                    loading="lazy"
                                                     class="max-h-24 object-contain transition duration-500 group-hover:scale-105" />
                                            </div>
                                            @else
                                            <div class="flex h-full w-full items-center justify-center bg-ash/10 text-4xl font-semibold text-dark">
                                                {{ mb_strtoupper(mb_substr($name, 0, 2)) }}
                                            </div>
                                            @endif
                                        @if ($isFeatured)
                                            <div class="absolute left-4 top-4">
                                                <span class="inline-flex items-center gap-1 rounded-full bg-brand-primary px-3 py-1 text-xs font-semibold text-white shadow-sm">
                                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                        <path d="M12 17.3 6.18 20l1.11-6.45L2 8.9l6.5-.94L12 2l2.5 5.96 6.5.94-4.7 4.65L17.82 20 12 17.3z" />
                                                    </svg>
                                                    {{ __('frontend/brands.list.badges.featured') }}
                                                </span>
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
                                                {{ __('Detailed description coming soon.') }}
                                                    </p>
                                        @endif

                                        <div class="flex items-center justify-center">
                                            <a href="{{ route('localized.brands.show', ['locale' => app()->getLocale(), 'slug' => $slug]) }}"
                                               class="inline-flex items-center gap-2 rounded-full bg-sage px-4 py-2 text-sm font-semibold text-dark transition hover:bg-sage/90">
                                                {{ __('frontend/brands.list.visit') }}
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        @if ($paginator->hasPages())
                            <div class="mt-12 rounded-3xl border border-sage/30 bg-dark p-6 shadow-lg">
                                @php
                                    $queryParameters = request()->query();
                                    unset($queryParameters['page']);
                                    $appendQueryString = static function (?string $url) use ($queryParameters): ?string {
                                        if ($url === null) {
                                            return null;
                                        }
                                        if ($queryParameters === []) {
                                            return $url;
                                        }
                                        $queryString = http_build_query($queryParameters);
                                        return $url . (str_contains($url, '?') ? '&' : '?') . $queryString;
                                    };
                                @endphp
                                <nav class="flex items-center justify-center" aria-label="{{ __('Pagination Navigation') }}">
                                    <div class="flex items-center justify-center">
                                        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                            @if ($paginator->onFirstPage())
                                                <span class="relative inline-flex items-center rounded-l-md px-2 py-2 text-sage/60 ring-1 ring-inset ring-sage/30">
                                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                                                    </svg>
                                                </span>
                                            @else
                                                <a href="{{ $appendQueryString($paginator->previousPageUrl()) }}" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-sage ring-1 ring-inset ring-sage/30 hover:bg-sage/10 hover:text-white focus:z-20 focus:outline-offset-0">
                                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                                                    </svg>
                                                </a>
                                            @endif

                                            @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                                                @if ($page == $paginator->currentPage())
                                                    <span class="relative z-10 inline-flex items-center bg-sage px-4 py-2 text-sm font-semibold text-dark focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sage">
                                                        {{ $page }}
                                                    </span>
                                                @else
                                                    <a href="{{ $appendQueryString($url) }}" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-sage ring-1 ring-inset ring-sage/30 hover:bg-sage/10 hover:text-white focus:z-20 focus:outline-offset-0">
                                                        {{ $page }}
                                                    </a>
                                                @endif
                                            @endforeach

                                            @if ($paginator->hasMorePages())
                                                <a href="{{ $appendQueryString($paginator->nextPageUrl()) }}" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-sage ring-1 ring-inset ring-sage/30 hover:bg-sage/10 hover:text-white focus:z-20 focus:outline-offset-0">
                                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                                    </svg>
                                                </a>
                                            @else
                                                <span class="relative inline-flex items-center rounded-r-md px-2 py-2 text-sage/60 ring-1 ring-inset ring-sage/30">
                                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                                    </svg>
                                                </span>
                                            @endif
                                        </nav>
                                    </div>
                                </nav>
                            </div>
                        @endif
                @else
                    <x-shared.empty-state
                            title="{{ __('frontend/brands.list.empty.title') }}"
                            description="{{ __('frontend/brands.list.empty.description') }}"
                            icon="heroicon-o-archive-box"
                            :action-text="__('Reset filters')"
                            :action-url="route('localized.brands.index', ['locale' => app()->getLocale()])"
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
                                {{ __('Filters') }}
                            </span>
                            <h2 class="text-xl font-semibold text-white">{{ __('frontend/brands.filters.title') }}</h2>
                        </div>
                        <button type="button"
                                class="rounded-full border border-sage/30 p-2 text-sage transition hover:border-sage hover:bg-sage/10"
                                wire:click="$toggle('sidebarOpen')"
                                wire:confirm="{{ __('translations.confirm_toggle_sidebar') }}"
                                aria-label="{{ __('Close') }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="flex-1 space-y-6 overflow-y-auto p-6">
                        @include('livewire.pages.brand.partials.filters', ['variant' => 'mobile'])
                    </div>
                    <div class="border-t border-sage/30 p-6">
                        <x-shared.button
                            type="button"
                            variant="primary"
                            size="sm"
                            class="w-full"
                            wire:click="$toggle('sidebarOpen')"
                        >
                            {{ __('Apply filters') }}
                        </x-shared.button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <x-filament-actions::modals />
</div>
