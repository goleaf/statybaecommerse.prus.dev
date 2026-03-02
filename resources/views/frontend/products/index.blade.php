@extends('frontend.layouts.app')

@section('title', __('messages.products'))
@section('description', __('ui.browse_the_latest_additions_featured_picks_and_trusted_tools_across_every_category'))

@section('content')
    <div class="bg-gray-50 py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            {{-- Mobile filter toggle --}}
            <div class="mb-4 lg:hidden" x-data="{ filtersOpen: false }">
                <button
                    type="button"
                    @click="filtersOpen = !filtersOpen"
                    class="inline-flex items-center gap-2 rounded-full border border-indigo-600 bg-white px-4 py-2 text-sm font-semibold text-indigo-600 shadow-sm transition hover:bg-indigo-50"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                    </svg>
                    <span x-text="filtersOpen ? '{{ __('frontend.search.hide_filters') }}' : '{{ __('frontend.search.show_filters') }}'">{{ __('frontend.search.show_filters') }}</span>
                </button>

                <div x-show="filtersOpen" x-transition x-cloak class="mt-4 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('ui.search_catalogue') }}</h2>
                    <form method="get" class="mt-4 space-y-4">
                        <div>
                            <label for="search-mobile" class="block text-sm font-medium text-gray-700">{{ __('messages.search') }}</label>
                            <div class="mt-1 flex rounded-full border border-gray-200 bg-gray-50 px-4 py-2 focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500">
                                <input id="search-mobile" name="q" type="search" value="{{ $searchTerm }}" placeholder="{{ __('ui.search_products') }}" class="w-full border-none bg-transparent text-sm focus:outline-none" />
                                <x-untitledui-search-sm class="h-5 w-5 text-gray-400" />
                            </div>
                        </div>

                        <div>
                            <label for="filter-mobile" class="block text-sm font-medium text-gray-700">{{ __('ui.quick_filter') }}</label>
                            <div class="mt-2 space-y-2">
                                @foreach ($availableFilters as $key => $label)
                                    <label class="flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700">
                                        <span>{{ $label }}</span>
                                        <input id="filter-mobile" type="radio" name="filter" value="{{ $key }}" @checked($activeFilter === $key) class="h-4 w-4 text-indigo-600" />
                                    </label>
                                @endforeach
                                <label class="flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700">
                                    <span>{{ __('ui.clear_filters') }}</span>
                                    <input type="radio" name="filter" value="" @checked(! $activeFilter) class="h-4 w-4 text-indigo-600" />
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="w-full rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                            {{ __('ui.apply_filters') }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="grid gap-10 lg:grid-cols-[280px_1fr]">
                <aside class="hidden space-y-8 lg:block">
                    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('ui.search_catalogue') }}</h2>
                        <form method="get" class="mt-4 space-y-4">
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700">{{ __('messages.search') }}</label>
                                <div class="mt-1 flex rounded-full border border-gray-200 bg-gray-50 px-4 py-2 focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500">
                                    <input id="search" name="q" type="search" value="{{ $searchTerm }}" placeholder="{{ __('ui.search_products') }}" class="w-full border-none bg-transparent text-sm focus:outline-none" />
                                    <x-untitledui-search-sm class="h-5 w-5 text-gray-400" />
                                </div>
                            </div>

                            <div>
                                <label for="filter" class="block text-sm font-medium text-gray-700">{{ __('ui.quick_filter') }}</label>
                                <div class="mt-2 space-y-2">
                                    @foreach ($availableFilters as $key => $label)
                                        <label class="flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700">
                                            <span>{{ $label }}</span>
                                            <input type="radio" name="filter" value="{{ $key }}" @checked($activeFilter === $key) class="h-4 w-4 text-indigo-600" />
                                        </label>
                                    @endforeach
                                    <label class="flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700">
                                        <span>{{ __('ui.clear_filters') }}</span>
                                        <input type="radio" name="filter" value="" @checked(! $activeFilter) class="h-4 w-4 text-indigo-600" />
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="w-full rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                                {{ __('ui.apply_filters') }}
                            </button>
                        </form>
                    </div>

                    <div class="space-y-6">
                        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                            <h2 class="text-lg font-semibold text-gray-900">{{ __('ui.leading_categories') }}</h2>
                            <ul class="mt-4 space-y-3 text-sm text-gray-700">
                                @foreach ($categories as $category)
                                    <li class="flex items-center justify-between">
                                        <a href="{{ route('frontend.categories.show', $category) }}" class="hover:text-indigo-600">{{ $category->name }}</a>
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600">{{ number_format($category->published_products_count ?? $category->products_count ?? 0) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                            <h2 class="text-lg font-semibold text-gray-900">{{ __('ui.trusted_brands') }}</h2>
                            <ul class="mt-4 space-y-3 text-sm text-gray-700">
                                @foreach ($brands as $brand)
                                    <li class="flex items-center justify-between">
                                        <a href="{{ route('frontend.brands.show', $brand) }}" class="hover:text-indigo-600">{{ $brand->name }}</a>
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600">{{ number_format($brand->published_products_count ?? $brand->products_count ?? 0) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </aside>

                <div class="space-y-8">
                    @include('frontend.products.partials.product-grid', ['products' => $products])
                </div>
            </div>
        </div>
    </div>
@endsection
