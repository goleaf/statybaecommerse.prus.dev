@extends('frontend.layouts.app')

@section('title', __('messages.categories'))
@section('description', __('ui.navigate_the_full_taxonomy_of_products_and_find_the_collections_built_for_your_next_project'))

@section('content')
    <div class="min-h-screen bg-sage">

        {{-- Hero Header --}}
        <div class="bg-gradient-to-r from-emerald-600 via-teal-500 to-cyan-500 px-4 py-12 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="space-y-3">
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white">
                            {{ __('ui.browse_by_category') }}
                        </span>
                        <h1 class="text-3xl font-bold text-white sm:text-4xl">{{ __('ui.all_catalogue_categories') }}</h1>
                        <p class="max-w-xl text-sm text-white/80 sm:text-base">
                            {{ __('ui.each_category_is_powered_by_live_product_counts_so_you_always_know_what_is_in_stock_and_trending') }}
                        </p>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="rounded-2xl bg-white/10 px-5 py-3 text-center text-sm text-white/80">
                            <div class="text-2xl font-bold text-white">{{ number_format($topCategories->count()) }}</div>
                            <div>{{ __('ui.top_categories_tracked') }}</div>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-5 py-3 text-center text-sm text-white/80">
                            <div class="text-2xl font-bold text-white">{{ number_format($featuredProducts->count()) }}</div>
                            <div>{{ __('ui.featured_products_surfaced') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">

            {{-- Mobile filter toggle --}}
            <div class="mb-6 lg:hidden" x-data="{ open: false }">
                <button
                    type="button"
                    @click="open = !open"
                    class="inline-flex items-center gap-2 rounded-full border border-emerald-600 bg-white px-4 py-2 text-sm font-semibold text-emerald-600 shadow-sm transition hover:bg-emerald-50"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                    </svg>
                    <span x-text="open ? '{{ __('frontend.search.hide_filters') }}' : '{{ __('frontend.search.show_filters') }}'">
                        {{ __('frontend.search.show_filters') }}
                    </span>
                    @if ($activeSearch || $activeBrand || $activeCollection)
                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-700">
                            {{ ($activeSearch ? 1 : 0) + ($activeBrand ? 1 : 0) + ($activeCollection ? 1 : 0) }}
                        </span>
                    @endif
                </button>

                <div x-show="open" x-transition x-cloak class="mt-4">
                    @include('frontend.categories.partials._filters', compact('brands', 'collections', 'activeSearch', 'activeBrand', 'activeCollection'))
                </div>
            </div>

            {{-- Active filters badges --}}
            @if ($activeSearch || $activeBrand || $activeCollection)
                <div class="mb-6 flex flex-wrap items-center gap-2">
                    <span class="text-sm font-medium text-gray-600">{{ __('frontend.filters.sort_by') }}:</span>

                    @if ($activeSearch)
                        <a href="{{ request()->fullUrlWithQuery(['q' => null]) }}"
                           class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800 hover:bg-emerald-200">
                            "{{ $activeSearch }}" &times;
                        </a>
                    @endif

                    @if ($activeBrand)
                        <a href="{{ request()->fullUrlWithQuery(['brand' => null]) }}"
                           class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800 hover:bg-blue-200">
                            {{ $brands->firstWhere('slug', $activeBrand)?->name ?? $activeBrand }} &times;
                        </a>
                    @endif

                    @if ($activeCollection)
                        <a href="{{ request()->fullUrlWithQuery(['collection' => null]) }}"
                           class="inline-flex items-center gap-1 rounded-full bg-purple-100 px-3 py-1 text-xs font-semibold text-purple-800 hover:bg-purple-200">
                            {{ $collections->firstWhere('slug', $activeCollection)?->name ?? $activeCollection }} &times;
                        </a>
                    @endif

                    <a href="{{ route('frontend.categories.index') }}"
                       class="ml-2 text-xs font-medium text-gray-500 hover:text-red-600 underline">
                        {{ __('frontend.search.clear_filters') }}
                    </a>
                </div>
            @endif

            <div class="grid gap-8 lg:grid-cols-[280px_1fr]">

                {{-- Sidebar (desktop only) --}}
                <aside class="hidden space-y-6 lg:block">
                    @include('frontend.categories.partials._filters', compact('brands', 'collections', 'activeSearch', 'activeBrand', 'activeCollection'))

                    {{-- Top performers --}}
                    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h2 class="text-base font-semibold text-gray-900">{{ __('ui.top_performers') }}</h2>
                        <ul class="mt-4 space-y-3 text-sm text-gray-700">
                            @foreach ($topCategories as $highlight)
                                <li class="flex items-center justify-between">
                                    <a href="{{ route('frontend.categories.show', $highlight) }}" class="hover:text-emerald-600">{{ $highlight->name }}</a>
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-600">{{ number_format($highlight->published_products_count ?? 0) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Featured products --}}
                    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h2 class="text-base font-semibold text-gray-900">{{ __('ui.featured_picks') }}</h2>
                        <ul class="mt-4 space-y-3 text-sm text-gray-700">
                            @forelse ($featuredProducts as $product)
                                <li class="flex items-center justify-between">
                                    <a href="{{ route('frontend.products.show', $product) }}" class="hover:text-emerald-600">{{ $product->name }}</a>
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600">{{ $product->formatted_price }}</span>
                                </li>
                            @empty
                                <li class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-4 text-center text-xs text-gray-500">
                                    {{ __('ui.featured_products_will_appear_soon') }}
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </aside>

                {{-- Main content --}}
                <main>
                    {{-- Results count --}}
                    <div class="mb-6 flex items-center justify-between">
                        <p class="text-sm text-gray-600">
                            {{ $categories->total() }} {{ __('messages.categories') }}
                            @if ($activeSearch)
                                &mdash; {{ __('frontend.search.results_for') }} <strong>"{{ $activeSearch }}"</strong>
                            @endif
                        </p>
                    </div>

                    @if ($categories->isEmpty())
                        <div class="rounded-3xl border border-dashed border-gray-300 bg-white p-16 text-center">
                            <svg class="mx-auto mb-4 h-12 w-12 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-700">{{ __('frontend.search.no_results_found') }}</h3>
                            <p class="mt-2 text-sm text-gray-500">{{ __('frontend.search.try_different_keywords') }}</p>
                            <a href="{{ route('frontend.categories.index') }}"
                               class="mt-6 inline-flex items-center rounded-full bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                                {{ __('frontend.search.clear_filters') }}
                            </a>
                        </div>
                    @else
                        <ul class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach ($categories as $category)
                                <li class="group flex flex-col justify-between rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-emerald-500 hover:shadow-md">
                                    <div class="space-y-2">
                                        <a href="{{ route('frontend.categories.show', $category) }}"
                                           class="text-lg font-semibold text-gray-900 group-hover:text-emerald-600">
                                            {{ $category->name }}
                                        </a>
                                        @if ($category->description)
                                            <p class="text-sm text-gray-500">{!! str($category->description)->stripTags()->limit(100) !!}</p>
                                        @endif
                                    </div>
                                    <div class="mt-4 flex items-center justify-between">
                                        <a href="{{ route('frontend.categories.show', $category) }}"
                                           class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">
                                            {{ __('messages.view') }} &rarr;
                                        </a>
                                        <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700">
                                            {{ number_format($category->published_products_count ?? 0) }} {{ __('messages.products') }}
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-8">
                            {{ $categories->links() }}
                        </div>
                    @endif
                </main>
            </div>
        </div>
    </div>
@endsection
