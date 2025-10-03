@section('meta')
    <x-meta
        :title="__('translations.brands') . ' - ' . config('app.name')"
        :description="__('Browse all our trusted brand partners and discover quality products')"
        canonical="{{ url()->current() }}" />
@endsection

@php
    $paginator = $this->brands;
    $totalBrands = $paginator->total();
    $totalProducts = $paginator->sum('products_count');
    $activeFilterCount = collect([
        filled($search ?? ''),
        ($sortBy ?? 'name') !== 'name',
    ])->filter()->count();
    $featuredBrands = $paginator->where('is_featured', true);
@endphp

<div class="min-h-screen bg-white text-gray-900 dark:bg-gray-950 dark:text-gray-100">
    {{-- Hero Section --}}
    <div class="border-b border-gray-200 bg-white/95 backdrop-blur-sm dark:border-gray-800 dark:bg-gray-950/80">
        <div class="mx-auto flex max-w-7xl flex-col gap-10 px-4 py-20 sm:px-6 lg:flex-row lg:items-center lg:gap-16 lg:px-8">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-3 rounded-full bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 shadow-sm dark:bg-gray-800/80 dark:text-gray-200">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white text-gray-900 shadow-sm dark:bg-gray-900 dark:text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </span>
                    {{ __('Curated partner network') }}
                </div>

                <h1 class="mt-6 text-4xl font-semibold tracking-tight sm:text-5xl">
                    {{ __('shared.brands') }}
                </h1>

                <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">
                    {{ __('Browse all our trusted brand partners and discover quality products from industry leaders') }}
                </p>
            </div>

            {{-- Stats --}}
            <div class="grid w-full max-w-2xl grid-cols-1 gap-4 sm:grid-cols-2 lg:max-w-none">
                <div class="rounded-2xl border border-gray-200 bg-white/80 p-6 shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900/60">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Total Brands') }}</p>
                    <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ number_format($totalBrands) }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white/80 p-6 shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900/60">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Products Available') }}</p>
                    <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ number_format($totalProducts) }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white/80 p-6 shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900/60 sm:col-span-2 lg:col-span-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Our promise') }}</p>
                    <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ __('Premium quality') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="relative">
        <x-container class="-mt-12 px-4 pb-20 sm:px-6 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-[320px,minmax(0,1fr)]">
                {{-- Sidebar Filters --}}
                <aside>
                    <x-shared.filter-sidebar
                        title="{{ __('Refine brand catalogue') }}"
                        description="{{ __('Use quick search and smart sorting to find the partners that match your needs.') }}"
                    >
                        <form wire:submit.prevent class="space-y-6">
                            <div class="space-y-5">
                                {{ $this->form }}
                            </div>
                        </form>

                        <div class="rounded-xl border border-dashed border-gray-200 bg-white px-4 py-3 text-sm text-gray-600 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            @if($activeFilterCount > 0)
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $activeFilterCount === 1 ? __('1 filter active') : __(':count filters active', ['count' => $activeFilterCount]) }}</span>
                                <span class="mt-1 block text-xs text-gray-500 dark:text-gray-400">{{ __('Filters update instantly for a smoother browsing experience.') }}</span>
                            @else
                                <span class="font-semibold text-gray-900 dark:text-white">{{ __('No filters applied') }}</span>
                                <span class="mt-1 block text-xs text-gray-500 dark:text-gray-400">{{ __('Showing the full list of enabled brands.') }}</span>
                            @endif
                        </div>

                        <x-slot name="footer">
                            <div class="flex items-center justify-between gap-3">
                                <x-shared.button
                                    type="button"
                                    variant="secondary"
                                    size="sm"
                                    wire:click="clearFilters"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 4.5l15 15m0-15l-15 15" />
                                    </svg>
                                    {{ __('shared.clear_filters') }}
                                </x-shared.button>

                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ __('Filters sync automatically') }}
                                </span>
                            </div>
                        </x-slot>

                        <x-slot name="actions">
                            <div class="grid gap-2">
                                <button type="button" wire:click="$set('sortBy', 'featured')"
                                        class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-200">
                                    <span>{{ __('Featured first') }}</span>
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                </button>
                                <button type="button" wire:click="$set('sortBy', 'products_count')"
                                        class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-200">
                                    <span>{{ __('Most products') }}</span>
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18M9 7h6m-3 0v14" />
                                    </svg>
                                </button>
                            </div>
                        </x-slot>
                    </x-shared.filter-sidebar>
                </aside>

                {{-- Main Brand Content --}}
                <div class="space-y-14">
                    {{-- Overview Card --}}
                    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900/60">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('Brand directory') }}</h2>
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    {{ __('Explore industry-leading suppliers and partners curated by the StatyBae team.') }}
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                    <span class="h-2 w-2 rounded-full bg-gray-500"></span>
                                    {{ number_format($totalBrands) }} {{ __('brands') }}
                                </span>
                                <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                    {{ number_format($totalProducts) }} {{ __('products') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @if($paginator->count() > 0)
                        {{-- Featured Brands Section --}}
                        @if($featuredBrands->count() > 0)
                            <section>
                                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('Featured Brands') }}</h2>
                                        <p class="text-gray-600 dark:text-gray-300">{{ __('Our most popular and trusted brand partners') }}</p>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                        <div class="h-2 w-2 rounded-full bg-amber-400"></div>
                                        <span>{{ $featuredBrands->count() }} {{ __('brands') }}</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
                                    @foreach($featuredBrands->take(6) as $brand)
                                        <x-shared.card hover="true" class="group relative overflow-hidden border border-gray-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg dark:border-gray-800 dark:bg-gray-900/70">
                                            <div class="absolute right-4 top-4 z-10">
                                                <x-shared.badge variant="primary" size="sm" class="bg-amber-500 text-white shadow-sm">
                                                    <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                    {{ __('Featured') }}
                                                </x-shared.badge>
                                            </div>

                                            <div class="aspect-w-16 aspect-h-9 overflow-hidden rounded-t-lg bg-white">
                                                @if($brand->getFirstMediaUrl('logo'))
                                                    <img
                                                        src="{{ $brand->getFirstMediaUrl('logo') }}"
                                                        alt="{{ $brand->name }}"
                                                        loading="lazy"
                                                        class="h-48 w-full object-contain object-center p-8 transition-transform duration-500 group-hover:scale-105"
                                                    />
                                                @else
                                                    <div class="flex h-48 items-center justify-center" aria-hidden="true">
                                                        <div class="text-center">
                                                            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 text-gray-700">
                                                                <span class="text-2xl font-bold">{{ strtoupper(substr($brand->name, 0, 2)) }}</span>
                                                            </div>
                                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $brand->name }}</div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="space-y-4 p-6">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div>
                                                        <h3 class="text-xl font-semibold text-gray-900 transition-colors group-hover:text-gray-700 dark:text-white dark:group-hover:text-gray-200">
                                                            {{ $brand->name }}
                                                        </h3>
                                                        <p class="mt-2 line-clamp-2 text-sm text-gray-600 dark:text-gray-300">
                                                            {{ $brand->description }}
                                                        </p>
                                                    </div>
                                                    @if($brand->website)
                                                        <a href="{{ $brand->website }}" target="_blank" rel="noopener"
                                                           class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 bg-gray-50 text-gray-600 transition hover:border-gray-300 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 010 5.656l-3 3a4 4 0 11-5.656-5.656l1.415-1.414" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.172 13.828a4 4 0 010-5.656l3-3a4 4 0 115.656 5.656l-1.415 1.414" />
                                                            </svg>
                                                        </a>
                                                    @endif
                                                </div>

                                                <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                                                    <span class="inline-flex items-center gap-2">
                                                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                                        {{ $brand->products_count }} {{ __('products') }}
                                                    </span>
                                                    <a
                                                        href="{{ route('localized.brands.show', ['locale' => app()->getLocale(), 'slug' => $brand->slug]) }}"
                                                        class="inline-flex items-center gap-1 font-semibold text-gray-900 transition hover:text-gray-600 dark:text-gray-100 dark:hover:text-gray-300"
                                                    >
                                                        {{ __('View brand') }}
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                                        </svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </x-shared.card>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        {{-- All Brands Grid --}}
                        <section>
                            <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('All Brands') }}</h2>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('Discover the complete list of our brand partners across all categories.') }}</p>
                                </div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ trans_choice('frontend/collections.stats.products', $totalBrands, ['count' => number_format($totalBrands)]) }}
                                </span>
                            </div>

                            <div wire:loading.flex class="min-h-[200px] items-center justify-center rounded-3xl border border-dashed border-gray-200 bg-white py-12 text-gray-500 shadow-sm dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-300">
                                <div class="inline-flex items-center gap-3 text-sm font-medium">
                                    <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ __('Loading brands...') }}
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3" wire:loading.remove>
                                @foreach($paginator as $brand)
                                    <x-shared.card hover="true" class="group relative overflow-hidden border border-gray-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg dark:border-gray-800 dark:bg-gray-900/70">
                                        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-gray-900 via-gray-700 to-gray-500 opacity-0 transition-opacity duration-500 group-hover:opacity-100 dark:from-gray-100 dark:via-gray-300 dark:to-gray-500"></div>

                                        <div class="aspect-w-16 aspect-h-9 overflow-hidden rounded-t-lg bg-white">
                                            @if($brand->getFirstMediaUrl('logo'))
                                                <img
                                                    src="{{ $brand->getFirstMediaUrl('logo') }}"
                                                    alt="{{ $brand->name }}"
                                                    loading="lazy"
                                                    class="h-48 w-full object-contain object-center p-8 transition-transform duration-500 group-hover:scale-105"
                                                />
                                            @else
                                                <div class="flex h-48 items-center justify-center" aria-hidden="true">
                                                    <div class="text-center">
                                                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 text-gray-700">
                                                            <span class="text-2xl font-bold">{{ strtoupper(substr($brand->name, 0, 2)) }}</span>
                                                        </div>
                                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $brand->name }}</div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="space-y-4 p-6">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="space-y-2">
                                                    <h3 class="text-lg font-semibold text-gray-900 transition-colors group-hover:text-gray-700 dark:text-white dark:group-hover:text-gray-200">
                                                        <a href="{{ route('localized.brands.show', ['locale' => app()->getLocale(), 'slug' => $brand->slug]) }}" class="transition-colors">
                                                            {{ $brand->name }}
                                                        </a>
                                                    </h3>
                                                    <p class="line-clamp-2 text-sm text-gray-600 dark:text-gray-300">
                                                        {{ $brand->description }}
                                                    </p>
                                                </div>

                                                @if($brand->is_featured)
                                                    <x-shared.badge variant="primary" size="sm" class="bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200">
                                                        {{ __('Featured') }}
                                                    </x-shared.badge>
                                                @endif
                                            </div>

                                            <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                                                <span class="inline-flex items-center gap-2">
                                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                                    {{ $brand->products_count }} {{ __('products') }}
                                                </span>

                                                <a
                                                    href="{{ route('localized.brands.show', ['locale' => app()->getLocale(), 'slug' => $brand->slug]) }}"
                                                    class="inline-flex items-center gap-1 font-semibold text-gray-900 transition hover:text-gray-600 dark:text-gray-100 dark:hover:text-gray-300"
                                                >
                                                    {{ __('View brand') }}
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </x-shared.card>
                                @endforeach
                            </div>

                            @if($paginator->hasPages())
                                <div class="mt-12 border-t border-gray-200 pt-6 dark:border-gray-700">
                                    <x-shared.pagination :paginator="$paginator" />
                                </div>
                            @endif
                        </section>
                    @else
                        <x-shared.empty-state
                            title="{{ __('shared.no_results_found') }}"
                            :description="!empty($this->search) ? __('Try adjusting your search terms') : __('No brands are available at the moment')"
                            icon="heroicon-o-cube"
                            :action-text="!empty($this->search) ? __('shared.clear_filters') : null"
                            :action-wire="!empty($this->search) ? '$set(\'search\', \'\')' : null"
                        />
                    @endif
                </div>
            </div>
        </x-container>
    </div>

    {{-- CTA Section --}}
    <div class="bg-gray-100 dark:bg-gray-900/70">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="mb-4 text-3xl font-semibold text-gray-900 dark:text-white">{{ __('Can\'t find your brand?') }}</h2>
                <p class="mx-auto mb-8 max-w-2xl text-lg text-gray-600 dark:text-gray-300">
                    {{ __('We\'re always looking to partner with new brands. Contact us to discuss partnership opportunities.') }}
                </p>
                <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <a href="mailto:support@statybae.com" class="inline-flex items-center rounded-full border border-gray-300 bg-white px-6 py-3 text-base font-medium text-gray-700 shadow-sm transition hover:border-gray-400 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:hover:border-gray-600 dark:hover:bg-gray-800">
                        <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        {{ __('Contact Us') }}
                    </a>
                    <a href="{{ route('localized.products.index', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center rounded-full border border-gray-300 bg-white px-6 py-3 text-base font-medium text-gray-700 shadow-sm transition hover:border-gray-400 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:hover:border-gray-600 dark:hover:bg-gray-800">
                        <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        {{ __('Browse Products') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <x-filament-actions::modals />
</div>
