@extends('frontend.layouts.app')

@section('title', __('messages.brands'))
@section('description', __('ui.meet_the_manufacturers_and_labels_powering_the_catalogue_with_professional_grade_inventory'))

@section('content')
    @php
        $activeFilterCount = $search !== '' ? 1 : 0;
    @endphp

    <div class="min-h-screen bg-sage brands-page">
        <div class="bg-dark text-sage">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
                <nav class="mb-8 text-sm text-white" aria-label="{{ __('frontend.navigation.breadcrumbs') }}">
                    <ol class="flex flex-wrap items-center gap-2">
                        <li>
                            <a href="{{ route('home') }}" class="text-white transition-colors hover:text-white">
                                {{ __('nav.home') }}
                            </a>
                        </li>
                        <li class="text-white">/</li>
                        <li class="text-white">{{ __('messages.brands') }}</li>
                    </ol>
                </nav>

                <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-2xl space-y-4">
                        <span class="inline-flex items-center gap-2 rounded-full border border-sage bg-sage px-4 py-1 text-[11px] font-semibold uppercase tracking-[0.35em] text-dark">
                            {{ __('messages.brands_index_badge') }}
                        </span>
                        <h1 class="text-3xl font-bold leading-tight text-white sm:text-4xl md:text-5xl">
                            {{ __('messages.brands_index_title') }}
                        </h1>
                        <p class="text-base text-white/90 sm:text-lg">
                            {{ __('messages.brands_index_description') }}
                        </p>
                    </div>

                    <div class="flex w-full flex-col items-start gap-3 sm:flex-row sm:flex-wrap sm:items-end sm:gap-4 lg:w-auto">
                        <div class="w-full rounded-2xl border border-sage/30 bg-sage/10 px-4 py-3 text-sm font-semibold text-white shadow-sm sm:w-auto">
                            {{ __('messages.brands_index_catalogue_count', ['count' => number_format($brands->total())]) }}
                        </div>
                        <div class="w-full rounded-2xl border border-sage/30 bg-sage/10 px-4 py-3 text-sm text-white/80 shadow-sm sm:w-auto">
                            @if ($activeFilterCount > 0)
                                {{ trans_choice('messages.brands_index_status', $activeFilterCount, ['count' => $activeFilterCount]) }}
                            @else
                                {{ __('messages.brands_index_status_none') }}
                            @endif
                        </div>
                        <div class="w-full rounded-2xl border border-sage/30 bg-sage/10 px-4 py-3 text-sm text-white/80 shadow-sm sm:w-auto">
                            {{ number_format($featuredProducts->count()) }} {{ __('ui.featured_picks') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <section class="grid gap-8 lg:grid-cols-[1fr_320px]">
                <div class="space-y-6">
                    <div class="rounded-3xl border border-sage/30 bg-dark p-6 shadow-lg">
                        <form method="get" action="{{ route('frontend.brands.index') }}" class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                            <div class="w-full lg:max-w-xl">
                                <label for="q" class="text-sm font-semibold text-white">{{ __('messages.brands_index_search_label') }}</label>
                                <div class="mt-2 flex items-center gap-2 rounded-2xl border border-sage/30 bg-sage/10 px-4 py-2">
                                    <svg class="h-4 w-4 text-white/70" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                                    </svg>
                                    <input
                                        type="search"
                                        id="q"
                                        name="q"
                                        value="{{ $search }}"
                                        class="w-full border-0 bg-transparent text-sm text-white placeholder:text-white/60 focus:outline-none focus:ring-0"
                                        placeholder="{{ __('messages.brands_index_search_placeholder') }}"
                                    />
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <button type="submit" class="rounded-full border border-sage bg-sage px-4 py-2 text-sm font-semibold text-dark shadow-sm transition-colors hover:bg-sage/90">
                                    {{ __('frontend.brands.index.search_action') }}
                                </button>
                                @if ($search !== '')
                                    <a href="{{ route('frontend.brands.index') }}" class="rounded-full border border-sage/30 px-4 py-2 text-sm font-semibold text-white transition-colors hover:border-sage hover:bg-sage/10">
                                        {{ __('messages.clear_all') }}
                                    </a>
                                @endif
                            </div>
                        </form>

                        <div class="mt-4 text-sm text-white/80">
                            @if ($brands->count() > 0)
                                {{ __('messages.brands_index_showing_results', ['from' => $brands->firstItem() ?? 0, 'to' => $brands->lastItem() ?? 0, 'total' => $brands->total()]) }}
                            @else
                                {{ __('messages.brands_index_no_results') }}
                            @endif
                        </div>
                    </div>

                    @if ($brands->isEmpty())
                        <div class="rounded-3xl border border-dashed border-sage/30 bg-dark/50 p-12 text-center">
                            <h2 class="text-xl font-semibold text-white">{{ __('messages.brands_index_empty_title') }}</h2>
                            <p class="mt-2 text-sm text-white/70">{{ __('messages.brands_index_empty_description') }}</p>
                            <a href="{{ route('frontend.brands.index') }}" class="mt-6 inline-flex items-center rounded-full border border-sage bg-sage px-5 py-2.5 text-sm font-semibold text-dark transition-colors hover:bg-sage/90">
                                {{ __('messages.brands_index_reset_filters') }}
                            </a>
                        </div>
                    @else
                        <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($brands as $brand)
                                <li class="group overflow-hidden rounded-3xl border border-sage/30 bg-dark shadow-sm transition hover:-translate-y-1 hover:border-sage hover:shadow-xl">
                                    <a href="{{ route('frontend.brands.show', $brand) }}" class="block h-full">
                                        <div class="relative flex min-h-36 items-center justify-center bg-sage/10 p-6">
                                            @if ($brand->getFirstMediaUrl('logo'))
                                                <img
                                                    src="{{ $brand->getFirstMediaUrl('logo') }}"
                                                    alt="{{ $brand->name }}"
                                                    loading="lazy"
                                                    class="max-h-20 w-auto object-contain transition duration-300 group-hover:scale-105"
                                                />
                                            @else
                                                <svg class="h-10 w-10 text-white/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M15 9h.01M9 13h.01M15 13h.01" />
                                                </svg>
                                            @endif

                                            <span class="absolute right-4 top-4 rounded-full border border-sage/30 bg-dark/70 px-2.5 py-1 text-xs font-semibold text-white">
                                                {{ number_format($brand->published_products_count ?? 0) }} {{ __('messages.products') }}
                                            </span>
                                        </div>

                                        <div class="flex flex-col justify-between gap-4 p-5">
                                            <div class="space-y-2">
                                                <h2 class="text-lg font-semibold text-white transition-colors group-hover:text-sage">
                                                    {{ $brand->name }}
                                                </h2>
                                                <p class="text-sm leading-relaxed text-white/70">
                                                    @if ($brand->description)
                                                        {{ \Illuminate\Support\Str::limit(strip_tags((string) $brand->description), 120) }}
                                                    @else
                                                        {{ __('messages.brands_index_description_placeholder') }}
                                                    @endif
                                                </p>
                                            </div>

                                            <span class="inline-flex w-fit items-center gap-2 rounded-full border border-sage/30 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white/90 transition-colors group-hover:border-sage group-hover:text-sage">
                                                {{ __('messages.brands_index_visit_brand') }}
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7" />
                                                </svg>
                                            </span>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        @if ($brands->hasPages())
                            <div class="brands-pagination rounded-3xl border border-sage/30 bg-dark p-6 shadow-lg">
                                {{ $brands->onEachSide(1)->links('pagination::tailwind') }}
                            </div>
                        @endif
                    @endif
                </div>

                <aside class="space-y-6">
                    <div class="rounded-3xl border border-sage/30 bg-dark p-6 shadow-lg">
                        <h2 class="text-lg font-semibold text-white">{{ __('ui.trending_brands') }}</h2>
                        <ul class="mt-4 space-y-3 text-sm text-white/80">
                            @forelse ($highlightedBrands as $brand)
                                <li class="flex items-center justify-between gap-3">
                                    <a href="{{ route('frontend.brands.show', $brand) }}" class="truncate transition-colors hover:text-sage">{{ $brand->name }}</a>
                                    <span class="shrink-0 rounded-full border border-sage/30 bg-sage/10 px-2 py-0.5 text-xs font-semibold text-white">
                                        {{ number_format($brand->published_products_count ?? 0) }}
                                    </span>
                                </li>
                            @empty
                                <li class="rounded-2xl border border-dashed border-sage/30 bg-dark/40 p-4 text-center text-xs text-white/70">
                                    {{ __('messages.brands_index_no_results') }}
                                </li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="rounded-3xl border border-sage/30 bg-dark p-6 shadow-lg">
                        <h2 class="text-lg font-semibold text-white">{{ __('ui.featured_picks') }}</h2>
                        <ul class="mt-4 space-y-3 text-sm text-white/80">
                            @forelse ($featuredProducts as $product)
                                <li class="flex items-center justify-between gap-3">
                                    <a href="{{ route('frontend.products.show', $product) }}" class="truncate transition-colors hover:text-sage">{{ $product->name }}</a>
                                    <span class="shrink-0 rounded-full border border-sage/30 bg-sage/10 px-2 py-0.5 text-xs font-semibold text-white">{{ $product->formatted_price }}</span>
                                </li>
                            @empty
                                <li class="rounded-2xl border border-dashed border-sage/30 bg-dark/40 p-4 text-center text-xs text-white/70">
                                    {{ __('ui.featured_products_will_appear_soon') }}
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </aside>
            </section>
        </div>
    </div>
@endsection
