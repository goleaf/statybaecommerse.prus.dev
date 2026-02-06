@extends('frontend.layouts.app')

@section('title', __('messages.products'))
@section('description', __('ui.browse_the_latest_additions_featured_picks_and_trusted_tools_across_every_category'))

@section('content')
    <div class="bg-gray-50 py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-[280px_1fr]">
                <aside class="space-y-8">
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
                    <header class="flex flex-col gap-6 rounded-3xl bg-gradient-to-r from-indigo-600 via-blue-500 to-sky-500 p-8 text-white shadow-lg">
                        <div class="space-y-2">
                            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide">{{ __('ui.live_catalogue') }}</span>
                            <h1 class="text-3xl font-semibold sm:text-4xl">{{ __('ui.discover_professional_tools_for_every_job') }}</h1>
                            <p class="text-sm text-white/80 sm:text-base">{{ __('ui.filter_sort_and_browse_the_entire_product_catalogue_sourced_directly_from_the_live_inventory_feed') }}</p>
                        </div>
                        <div class="flex flex-col gap-4 text-sm sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-2 text-white/70">
                                <x-untitledui-check-badge class="h-5 w-5" />
                                <span>{{ __('ui.updated_in_real_time_with_the_latest_product_data') }}</span>
                            </div>
                            <form method="get" class="flex items-center gap-3">
                                @foreach (request()->except('sort') as $key => $value)
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}" />
                                @endforeach
                                <label for="sort" class="text-sm font-medium text-white/80">{{ __('messages.sort_by') }}</label>
                                <select id="sort" name="sort" class="rounded-full border border-white/30 bg-white/10 px-4 py-2 text-sm font-semibold text-white shadow-inner focus:border-white focus:outline-none">
                                    @foreach ($availableSorts as $key => $label)
                                        <option value="{{ $key }}" @selected($activeSort === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-indigo-600 shadow-sm transition hover:bg-blue-50">{{ __('messages.update') }}</button>
                            </form>
                        </div>
                    </header>

                    @include('frontend.products.partials.product-grid', ['products' => $products])
                </div>
            </div>
        </div>
    </div>
@endsection
