@section('meta')
    <x-meta
        :title="__('translations.brands') . ' - ' . config('app.name')"
        :description="__('Browse all our trusted brand partners and discover quality products')"
        canonical="{{ url()->current() }}" />
@endsection

@php
    $totalBrands = $this->brands->total();
    $visibleBrands = $this->brands->count();
    $totalProducts = $this->brands->getCollection()->sum('products_count');
@endphp

<div class="min-h-screen bg-slate-950">
    {{-- Hero --}}
    <div class="relative isolate overflow-hidden bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800">
        <div class="absolute inset-0 -z-10 opacity-60">
            <div class="absolute -top-24 right-1/3 h-64 w-64 rounded-full bg-blue-500/20 blur-3xl"></div>
            <div class="absolute bottom-0 left-1/3 h-72 w-72 rounded-full bg-indigo-500/30 blur-3xl"></div>
        </div>
        <div class="mx-auto flex max-w-7xl flex-col gap-10 px-4 pb-24 pt-20 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
            <div class="max-w-2xl text-white">
                <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1 text-sm font-medium tracking-wide text-blue-200 ring-1 ring-white/20">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                    {{ __('shared.brands') }}
                </div>
                <h1 class="mt-6 text-4xl font-semibold tracking-tight text-white sm:text-5xl lg:text-6xl">
                    {{ __('Browse visionary brands powering Statyba Ecommerce') }}
                </h1>
                <p class="mt-6 text-lg text-slate-200">
                    {{ __('Discover trusted partners, explore their stories, and shop curated collections crafted for a modern construction marketplace.') }}
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <a href="{{ localized_route('products.index') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-3 text-sm font-medium text-slate-900 transition hover:bg-slate-100">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        {{ __('Shop now') }}
                    </a>
                    <a href="#brand-catalog" class="inline-flex items-center gap-2 text-sm font-medium text-blue-200 transition hover:text-white">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('Explore the catalogue') }}
                    </a>
                </div>
            </div>

            <dl class="grid w-full max-w-xl grid-cols-2 gap-6 rounded-3xl border border-white/10 bg-white/5 p-8 text-white backdrop-blur lg:max-w-md">
                <div>
                    <dt class="text-sm font-medium text-blue-100">{{ __('Total brands') }}</dt>
                    <dd class="mt-1 text-3xl font-semibold">{{ number_format($totalBrands) }}</dd>
                    <dd class="mt-2 text-xs text-blue-200">{{ __('Total brands available across the platform') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-blue-100">{{ __('Now showing') }}</dt>
                    <dd class="mt-1 text-3xl font-semibold">{{ number_format($visibleBrands) }}</dd>
                    <dd class="mt-2 text-xs text-blue-200">{{ __('Brands visible with current filters') }}</dd>
                </div>
                <div class="col-span-2">
                    <dt class="text-sm font-medium text-blue-100">{{ __('Represented products') }}</dt>
                    <dd class="mt-1 text-3xl font-semibold">{{ number_format($totalProducts) }}</dd>
                    <dd class="mt-2 text-xs text-blue-200">{{ __('Products represented by brands on this page') }}</dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Filters --}}
    <section class="relative -mt-16 z-10" aria-label="{{ __('shared.filters') }}">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-3xl border border-white/10 bg-white/95 p-6 shadow-2xl backdrop-blur dark:bg-slate-900/80">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Refine brand discovery') }}</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">{{ __('Search by name, explore the most popular partners, or surface the freshest arrivals.') }}</p>
                    </div>
                    <form wire:submit.prevent class="w-full lg:w-1/2">
                        <div class="grid gap-4 sm:grid-cols-2">
                            {{ $this->form }}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    {{-- Brands Grid --}}
    <section id="brand-catalog" class="relative z-0 mt-20 pb-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if($this->brands->count() > 0)
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 xl:grid-cols-3" aria-live="polite">
                    @foreach($this->brands as $brand)
                        @php
                            $brandName = $brand->getTranslatedName() ?? $brand->name;
                            $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($brandName, 0, 1));
                            $hasLogo = (bool) $brand->getFirstMediaUrl('logo');
                            $description = $brand->getTranslatedDescription() ?? __('Crafting reliable solutions for modern building projects.');
                        @endphp
                        <article class="group relative overflow-hidden rounded-3xl border border-slate-200/60 bg-white shadow-xl transition duration-300 ease-out hover:-translate-y-1 hover:border-blue-200 hover:shadow-2xl dark:border-slate-800/60 dark:bg-slate-900">
                            <div class="relative bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 px-8 pb-8 pt-10 text-white">
                                <div class="absolute right-6 top-6 h-16 w-16 rounded-full bg-blue-500/30 blur-2xl transition-opacity duration-300 group-hover:opacity-80"></div>
                                <div class="relative flex items-center justify-between">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl border border-white/10 bg-white/10 backdrop-blur">
                                        @if($hasLogo)
                                            <img src="{{ $brand->getFirstMediaUrl('logo') }}" alt="{{ $brandName }}" loading="lazy" class="h-14 w-14 object-contain" />
                                        @else
                                            <span class="text-2xl font-semibold text-white">{{ $initial }}</span>
                                        @endif
                                    </div>
                                    <span class="rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-blue-100">
                                        {{ __('Partner brand') }}
                                    </span>
                                </div>
                                <h3 class="mt-6 text-2xl font-semibold tracking-tight text-white">
                                    <a href="{{ localized_route('brands.show', ['slug' => $brand->getTranslatedSlug()]) }}" class="inline-flex items-center gap-2">
                                        {{ $brandName }}
                                        <svg class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </h3>
                            </div>
                            <div class="flex h-full flex-col gap-6 px-8 py-8 text-slate-600 dark:text-slate-300">
                                <p class="line-clamp-3 text-base leading-relaxed">
                                    {{ $description }}
                                </p>
                                <div class="mt-auto flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-300">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18M3 9h18M3 15h18M3 21h18" />
                                            </svg>
                                        </span>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ number_format($brand->products_count) }}</p>
                                            <p class="text-xs uppercase tracking-wide text-slate-400">{{ trans_choice('products', $brand->products_count) }}</p>
                                        </div>
                                    </div>
                                    <a href="{{ localized_route('brands.show', ['slug' => $brand->getTranslatedSlug()]) }}" class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-900 transition hover:border-blue-400 hover:text-blue-500 dark:border-slate-700 dark:text-white dark:hover:border-blue-400">
                                        {{ __('View details') }}
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if($this->brands->hasPages())
                    <div class="mt-16">
                        <x-shared.pagination :paginator="$this->brands" />
                    </div>
                @endif
            @else
                <div class="rounded-3xl border border-dashed border-slate-700/60 bg-slate-900/60 p-12 text-center text-slate-300">
                    <svg class="mx-auto h-12 w-12 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L12 19.25 14.25 17m0-10L12 4.75 9.75 7m-2.5 4.5h9.5" />
                    </svg>
                    <h3 class="mt-6 text-2xl font-semibold text-white">{{ __('shared.no_results_found') }}</h3>
                    <p class="mt-3 text-sm">{{ !empty($this->search) ? __('Try adjusting your search terms or sorting options to surface more brands.') : __('No brands are available at the moment. Check back soon for new partnerships!') }}</p>
                    @if(!empty($this->search))
                        <button type="button" wire:click="$set('search', '')" class="mt-8 inline-flex items-center gap-2 rounded-full bg-white/10 px-5 py-2 text-sm font-medium text-white transition hover:bg-white/20">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            {{ __('shared.clear_filters') }}
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </section>

    <x-filament-actions::modals />
</div>
