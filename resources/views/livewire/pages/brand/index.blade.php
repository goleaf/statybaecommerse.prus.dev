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

<div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-blue-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
    {{-- Hero Section --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-800 dark:from-gray-900 dark:via-blue-900 dark:to-indigo-900">
        <div class="absolute inset-0 bg-black/20"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600/90 to-transparent"></div>
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0 bg-pattern-dots-60"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="flex justify-center mb-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                </div>

                <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                    {{ __('shared.brands') }}
                </h1>

                <p class="mt-6 text-xl text-blue-100 max-w-3xl mx-auto">
                    {{ __('Browse all our trusted brand partners and discover quality products from industry leaders') }}
                </p>

                {{-- Stats --}}
                <div class="mt-12 grid grid-cols-1 gap-8 sm:grid-cols-3">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-white">{{ number_format($totalBrands) }}</div>
                        <div class="text-blue-100">{{ __('Total Brands') }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-white">{{ number_format($totalProducts) }}</div>
                        <div class="text-blue-100">{{ __('Products Available') }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-white">{{ __('Premium Quality') }}</div>
                        <div class="text-blue-100">{{ __('Guaranteed') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="relative">
        <x-container class="-mt-24 px-4 pb-16 sm:px-6 lg:px-8">
            <div class="grid gap-12 lg:grid-cols-[320px,minmax(0,1fr)]">
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

                        <div class="rounded-xl border border-dashed border-blue-200/70 bg-blue-50/60 px-4 py-3 text-sm text-blue-700 dark:border-blue-800/80 dark:bg-blue-900/30 dark:text-blue-100">
                            @if($activeFilterCount > 0)
                                <span class="font-semibold">{{ $activeFilterCount === 1 ? __('1 filter active') : __(':count filters active', ['count' => $activeFilterCount]) }}</span>
                                <span class="block text-xs mt-1 text-blue-600/80 dark:text-blue-200/80">{{ __('Filters update instantly for a smoother browsing experience.') }}</span>
                            @else
                                <span class="font-semibold">{{ __('No filters applied') }}</span>
                                <span class="block text-xs mt-1 text-blue-600/80 dark:text-blue-200/80">{{ __('Showing the full list of enabled brands.') }}</span>
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
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                        class="flex items-center justify-between rounded-lg border border-blue-200 bg-white px-4 py-2 text-sm font-medium text-blue-700 transition hover:border-blue-300 hover:bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-100">
                                    <span>{{ __('Featured first') }}</span>
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                </button>
                                <button type="button" wire:click="$set('sortBy', 'products_count')"
                                        class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-blue-300 hover:bg-blue-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
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
                <div class="space-y-16">
                    {{-- Overview Card --}}
                    <div class="rounded-3xl border border-blue-100/80 bg-white/80 p-6 shadow-sm backdrop-blur-sm dark:border-gray-700 dark:bg-gray-800/60">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Brand directory') }}</h2>
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    {{ __('Explore industry-leading suppliers and partners curated by the StatyBae team.') }}
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="inline-flex items-center gap-2 rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-blue-700 dark:bg-blue-900/50 dark:text-blue-200">
                                    <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                    {{ number_format($totalBrands) }} {{ __('brands') }}
                                </span>
                                <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-gray-700 dark:bg-gray-700/60 dark:text-gray-200">
                                    <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                    {{ number_format($totalProducts) }} {{ __('products') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @if($paginator->count() > 0)
                        {{-- Featured Brands Section --}}
                        @if($featuredBrands->count() > 0)
                            <section>
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
                                    <div>
                                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Featured Brands') }}</h2>
                                        <p class="text-gray-600 dark:text-gray-300">{{ __('Our most popular and trusted brand partners') }}</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="h-2 w-2 rounded-full bg-amber-400"></div>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $featuredBrands->count() }} {{ __('brands') }}</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 xl:grid-cols-3">
                                    @foreach($featuredBrands->take(6) as $brand)
                                        <x-shared.card hover="true" class="group relative overflow-hidden">
                                            <div class="absolute top-4 right-4 z-10">
                                                <x-shared.badge variant="primary" size="sm" class="bg-gradient-to-r from-yellow-400 to-orange-500 text-white">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                    {{ __('Featured') }}
                                                </x-shared.badge>
                                            </div>

                                            <div class="aspect-w-16 aspect-h-9 overflow-hidden rounded-t-lg bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800">
                                                @if($brand->getFirstMediaUrl('logo'))
                                                    <img
                                                        src="{{ $brand->getFirstMediaUrl('logo') }}"
                                                        alt="{{ $brand->name }}"
                                                        loading="lazy"
                                                        class="h-48 w-full object-contain object-center p-8 transition-transform duration-500 group-hover:scale-110"
                                                    />
                                                @else
                                                    <div class="flex h-48 items-center justify-center" aria-hidden="true">
                                                        <div class="text-center">
                                                            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600">
                                                                <span class="text-2xl font-bold text-white">{{ strtoupper(substr($brand->name, 0, 2)) }}</span>
                                                            </div>
                                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $brand->name }}</div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="p-6">
                                                <h3 class="mb-2 text-xl font-bold text-gray-900 transition-colors duration-200 group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">
                                                    <a href="{{ route('localized.brands.show', ['locale' => app()->getLocale(), 'slug' => $brand->getTranslatedSlug()]) }}" class="stretched-link">
                                                        {{ $brand->getTranslatedName() }}
                                                    </a>
                                                </h3>

                                                @if($brand->getTranslatedDescription())
                                                    <p class="mb-4 line-clamp-2 text-sm text-gray-600 dark:text-gray-300">
                                                        {{ $brand->getTranslatedDescription() }}
                                                    </p>
                                                @endif

                                                <div class="flex items-center justify-between">
                                                    <x-shared.badge variant="primary" size="sm" class="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                        {{ $brand->products_count }} {{ trans_choice('products', $brand->products_count) }}
                                                    </x-shared.badge>
                                                    <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                                        <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                                        </svg>
                                                        {{ __('Premium Quality') }}
                                                    </div>
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
                                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('All Brands') }}</h2>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('Discover all our brand partners') }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ trans_choice('frontend/collections.stats.products', $totalBrands, ['count' => number_format($totalBrands)]) }}
                                    </span>
                                    <div class="hidden lg:flex items-center gap-1 rounded-lg border border-gray-200 bg-white p-1 text-sm font-medium text-gray-500 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                        <button type="button" class="rounded-md px-3 py-1.5 text-gray-600 transition hover:bg-blue-50 hover:text-blue-600 dark:text-gray-300 dark:hover:bg-gray-700">{{ __('Grid') }}</button>
                                        <button type="button" class="rounded-md px-3 py-1.5 text-gray-600 transition hover:bg-blue-50 hover:text-blue-600 dark:text-gray-300 dark:hover:bg-gray-700">{{ __('List') }}</button>
                                    </div>
                                </div>
                            </div>

                            <div wire:loading.flex class="min-h-[200px] items-center justify-center rounded-3xl border border-dashed border-blue-200 py-12 text-blue-600 dark:border-blue-800 dark:text-blue-200">
                                <div class="inline-flex items-center gap-3 text-sm font-medium">
                                    <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ __('Loading brands...') }}
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 xl:grid-cols-3" wire:loading.remove>
                                @foreach($paginator as $brand)
                                    <x-shared.card hover="true" class="group relative overflow-hidden">
                                        <div class="aspect-w-16 aspect-h-9 overflow-hidden rounded-t-lg bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800">
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
                                                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-200 text-lg font-bold text-gray-600 dark:bg-gray-600 dark:text-gray-200">
                                                            {{ strtoupper(substr($brand->name, 0, 2)) }}
                                                        </div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $brand->name }}</div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="p-6">
                                            <h3 class="mb-2 text-lg font-semibold text-gray-900 transition-colors duration-200 group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">
                                                <a href="{{ route('localized.brands.show', ['locale' => app()->getLocale(), 'slug' => $brand->getTranslatedSlug()]) }}" class="stretched-link">
                                                    {{ $brand->getTranslatedName() }}
                                                </a>
                                            </h3>

                                            @if($brand->getTranslatedDescription())
                                                <p class="mb-3 line-clamp-2 text-sm text-gray-600 dark:text-gray-300">
                                                    {{ $brand->getTranslatedDescription() }}
                                                </p>
                                            @endif

                                            <div class="flex items-center justify-between">
                                                <x-shared.badge variant="primary" size="sm">
                                                    {{ $brand->products_count }} {{ trans_choice('products', $brand->products_count) }}
                                                </x-shared.badge>
                                                <svg class="h-4 w-4 text-gray-400 transition-colors duration-200 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
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
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 dark:from-gray-800 dark:to-gray-900">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-white mb-4">{{ __('Can\'t find your brand?') }}</h2>
                <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
                    {{ __('We\'re always looking to partner with new brands. Contact us to discuss partnership opportunities.') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="mailto:support@statybae.com" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        {{ __('Contact Us') }}
                    </a>
                    <a href="{{ route('localized.products.index', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-6 py-3 border border-white text-base font-medium rounded-md text-white hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
