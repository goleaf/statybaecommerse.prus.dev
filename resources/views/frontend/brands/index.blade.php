@extends('frontend.layouts.app')

@section('title', __('messages.brands'))
@section('description', __('ui.meet_the_manufacturers_and_labels_powering_the_catalogue_with_professional_grade_inventory'))

@section('content')
    @php
        $activeFilterCount = $search !== '' ? 1 : 0;
        $spotlightBrand = $brands->first();
    @endphp

    <div id="brands-page-top" class="brands-page min-h-screen bg-slate-50 text-slate-900">
        <section class="relative overflow-hidden border-b border-slate-200 bg-white">
            <div class="absolute inset-0 bg-gradient-to-br from-white via-slate-50 to-blue-50"></div>
            <div class="absolute inset-0 bg-pattern-grid-80 opacity-60"></div>

            <div class="relative mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
                <nav class="mb-8 text-sm text-slate-500" aria-label="{{ __('frontend.navigation.breadcrumbs') }}">
                    <ol class="flex flex-wrap items-center gap-2">
                        <li>
                            <a href="{{ route('home') }}" class="font-medium text-slate-600 transition-colors hover:text-slate-900">
                                {{ __('nav.home') }}
                            </a>
                        </li>
                        <li class="text-slate-300">/</li>
                        <li class="text-slate-900">{{ __('messages.brands') }}</li>
                    </ol>
                </nav>

                <div class="grid gap-10 lg:grid-cols-[minmax(0,_1fr)_320px] lg:items-end">
                    <div class="max-w-3xl space-y-4">
                        <span class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-4 py-1 text-[11px] font-semibold uppercase tracking-[0.32em] text-blue-700">
                            {{ __('messages.brands_index_badge') }}
                        </span>
                        <h1 class="text-4xl font-semibold leading-tight text-balance text-slate-900 sm:text-5xl lg:text-6xl">
                            {{ __('messages.brands_index_title') }}
                        </h1>
                        <p class="max-w-2xl text-base leading-relaxed text-slate-600 sm:text-lg">
                            {{ __('messages.brands_index_description') }}
                        </p>

                        <div class="flex flex-wrap items-center gap-3 text-sm">
                            <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 font-medium text-slate-600 shadow-sm">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                {{ __('messages.brands_index_catalogue_count', ['count' => number_format($brands->total())]) }}
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 font-medium text-slate-600 shadow-sm">
                                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                @if ($activeFilterCount > 0)
                                    {{ trans_choice('messages.brands_index_status', $activeFilterCount, ['count' => $activeFilterCount]) }}
                                @else
                                    {{ __('messages.brands_index_status_none') }}
                                @endif
                            </span>
                        </div>
                    </div>

                    <dl class="grid gap-4 sm:grid-cols-3 lg:grid-cols-1">
                        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                            <dt class="text-sm font-medium text-slate-500">{{ __('messages.brands') }}</dt>
                            <dd class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($brands->total()) }}</dd>
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                            <dt class="text-sm font-medium text-slate-500">{{ __('messages.brands_index_filters_title') }}</dt>
                            <dd class="mt-2 text-lg font-semibold text-slate-900">
                                @if ($activeFilterCount > 0)
                                    {{ trans_choice('messages.brands_index_status', $activeFilterCount, ['count' => $activeFilterCount]) }}
                                @else
                                    {{ __('messages.brands_index_status_none') }}
                                @endif
                            </dd>
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                            <dt class="text-sm font-medium text-slate-500">{{ __('ui.featured_picks') }}</dt>
                            <dd class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($featuredProducts->count()) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </section>

        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-[300px_minmax(0,_1fr)] xl:grid-cols-[320px_minmax(0,_1fr)]">
                <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
                    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="space-y-2">
                            <span class="inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.3em] text-cyan-700">
                                {{ __('messages.brands_index_filters_button') }}
                            </span>
                            <h2 class="text-xl font-semibold text-slate-900">{{ __('messages.brands_index_filters_title') }}</h2>
                            <p class="text-sm leading-relaxed text-slate-600">{{ __('messages.brands_index_filters_description') }}</p>
                        </div>

                        <form method="get" action="{{ route('frontend.brands.index') }}" class="mt-6 space-y-4">
                            <div>
                                <label for="q" class="text-sm font-semibold text-slate-700">{{ __('messages.brands_index_search_label') }}</label>
                                <div class="mt-2 flex items-center gap-2 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 focus-within:border-cyan-400 focus-within:ring-2 focus-within:ring-cyan-100">
                                    <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                                    </svg>
                                    <input
                                        type="search"
                                        id="q"
                                        name="q"
                                        value="{{ $search }}"
                                        class="w-full border-0 bg-transparent p-0 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-0"
                                        placeholder="{{ __('messages.brands_index_search_placeholder') }}"
                                    />
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-3">
                                <button type="submit" class="inline-flex items-center justify-center rounded-full bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-cyan-700">
                                    {{ __('frontend.brands.index.search_action') }}
                                </button>
                                @if ($search !== '')
                                    <a href="{{ route('frontend.brands.index') }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900">
                                        {{ __('messages.clear_all') }}
                                    </a>
                                @endif
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                                @if ($brands->count() > 0)
                                    {{ __('messages.brands_index_showing_results', ['from' => $brands->firstItem() ?? 0, 'to' => $brands->lastItem() ?? 0, 'total' => $brands->total()]) }}
                                @else
                                    {{ __('messages.brands_index_no_results') }}
                                @endif
                            </div>
                        </form>
                    </section>

                    @if ($spotlightBrand)
                        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                            <div class="border-b border-slate-200 bg-gradient-to-r from-slate-900 to-slate-800 px-6 py-5 text-white">
                                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-300">{{ __('ui.trending_brands') }}</p>
                                <h2 class="mt-2 text-xl font-semibold">{{ $spotlightBrand->name }}</h2>
                                <p class="mt-2 text-sm text-slate-300">
                                    {{ \Illuminate\Support\Str::limit(strip_tags((string) ($spotlightBrand->description ?? __('messages.brands_index_description_placeholder'))), 110) }}
                                </p>
                            </div>
                            <div class="space-y-4 p-6">
                                <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                    <span class="text-sm font-medium text-slate-600">{{ __('messages.products') }}</span>
                                    <span class="text-lg font-semibold text-slate-900">{{ number_format($spotlightBrand->published_products_count ?? 0) }}</span>
                                </div>
                                <a href="{{ route('frontend.brands.show', $spotlightBrand) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-cyan-700 transition hover:text-cyan-800">
                                    {{ __('messages.brands_index_visit_brand') }}
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </section>
                    @endif

                    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-900">{{ __('ui.trending_brands') }}</h2>
                        <ul class="mt-4 space-y-3 text-sm text-slate-600">
                            @forelse ($highlightedBrands as $brand)
                                <li class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                    <a href="{{ route('frontend.brands.show', $brand) }}" class="truncate font-medium transition hover:text-cyan-700">
                                        {{ $brand->name }}
                                    </a>
                                    <span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-slate-700">
                                        {{ number_format($brand->published_products_count ?? 0) }}
                                    </span>
                                </li>
                            @empty
                                <li class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-xs text-slate-500">
                                    {{ __('messages.brands_index_no_results') }}
                                </li>
                            @endforelse
                        </ul>
                    </section>

                    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-900">{{ __('ui.featured_picks') }}</h2>
                        <ul class="mt-4 space-y-3 text-sm text-slate-600">
                            @forelse ($featuredProducts as $product)
                                <li class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                    <a href="{{ route('frontend.products.show', $product) }}" class="truncate font-medium transition hover:text-cyan-700">
                                        {{ $product->name }}
                                    </a>
                                    <span class="shrink-0 text-xs font-semibold text-slate-700">{{ $product->formatted_price }}</span>
                                </li>
                            @empty
                                <li class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-xs text-slate-500">
                                    {{ __('ui.featured_products_will_appear_soon') }}
                                </li>
                            @endforelse
                        </ul>
                    </section>
                </aside>

                <main class="space-y-8">
                    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="space-y-2">
                                <h2 class="text-xl font-semibold text-slate-900">{{ __('messages.brands') }}</h2>
                                <p class="text-sm text-slate-600">
                                    @if ($brands->count() > 0)
                                        {{ __('messages.brands_index_showing_results', ['from' => $brands->firstItem() ?? 0, 'to' => $brands->lastItem() ?? 0, 'total' => $brands->total()]) }}
                                    @else
                                        {{ __('messages.brands_index_no_results') }}
                                    @endif
                                </p>
                            </div>

                            @if ($search !== '')
                                <div class="inline-flex items-center gap-2 self-start rounded-full border border-cyan-200 bg-cyan-50 px-4 py-2 text-sm font-medium text-cyan-800">
                                    <span class="text-cyan-600">{{ __('messages.search') }}:</span>
                                    <span>{{ $search }}</span>
                                </div>
                            @endif
                        </div>
                    </section>

                    @if ($brands->isEmpty())
                        <section class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center shadow-sm">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                                </svg>
                            </div>
                            <h2 class="mt-5 text-2xl font-semibold text-slate-900">{{ __('messages.brands_index_empty_title') }}</h2>
                            <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-slate-600">{{ __('messages.brands_index_empty_description') }}</p>
                            <a href="{{ route('frontend.brands.index') }}" class="mt-8 inline-flex items-center justify-center rounded-full bg-slate-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                                {{ __('messages.brands_index_reset_filters') }}
                            </a>
                        </section>
                    @else
                        <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                                @foreach ($brands as $brand)
                                @php
                                    $brandDescription = $brand->description
                                        ? \Illuminate\Support\Str::limit(strip_tags((string) $brand->description), 135)
                                        : null;
                                @endphp

                                <article class="group flex h-full flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:-translate-y-1 hover:border-cyan-200 hover:shadow-xl">
                                    <div class="flex h-full flex-col p-6">
                                        <div class="flex items-start justify-between gap-4">
                                            @if ($brand->logo)
                                                <div class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                                    <img
                                                        src="{{ $brand->logo }}"
                                                        alt="{{ $brand->name }}"
                                                        loading="lazy"
                                                        class="h-9 w-9 object-contain"
                                                    />
                                                </div>
                                            @endif

                                            <div class="inline-flex items-center rounded-full border border-cyan-200 bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-800">
                                                {{ number_format($brand->published_products_count ?? 0) }} {{ __('messages.products') }}
                                            </div>
                                        </div>

                                        <div class="mt-8 flex-1 space-y-4">
                                            <div class="space-y-3">
                                                <h3 class="text-2xl font-semibold tracking-tight text-slate-900 transition group-hover:text-cyan-700">
                                                    <a href="{{ route('frontend.brands.show', $brand) }}">{{ $brand->name }}</a>
                                                </h3>
                                                @if ($brandDescription)
                                                    <p class="text-sm leading-6 text-slate-600">{{ $brandDescription }}</p>
                                                @endif
                                            </div>

                                            <div class="flex min-h-8 flex-wrap gap-2">
                                                @if ($brand->is_featured)
                                                    <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                                                        {{ __('messages.featured') }}
                                                    </span>
                                                @endif
                                                @if ($brand->website)
                                                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">
                                                        {{ $brand->website_domain ?? parse_url((string) $brand->website, PHP_URL_HOST) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="mt-8 flex items-center gap-4 border-t border-slate-200 pt-4">
                                            <a href="{{ route('frontend.brands.show', $brand) }}" class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 group-hover:bg-cyan-700">
                                                {{ __('messages.brands_index_visit_brand') }}
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </section>

                        @if ($brands->hasPages())
                            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                                {{ $brands->onEachSide(1)->links('pagination::tailwind') }}
                            </div>
                        @endif
                    @endif
                </main>
            </div>
        </div>
    </div>
@endsection
