@extends('frontend.layouts.app')

@section('title', __('Product catalogue'))

@section('content')
    <div class="bg-gray-50 py-12">
        <div class="mx-auto max-w-7xl space-y-10 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-2">
                    <p class="inline-flex items-center gap-2 rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-700">
                        <x-untitledui-grid class="h-4 w-4" />
                        {{ __('Browse the catalogue') }}
                    </p>
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                        {{ __('Discover construction essentials') }}
                    </h1>
                    <p class="max-w-2xl text-gray-600">
                        {{ __('Explore the latest tools, materials, and protective equipment sourced from our trusted Lithuanian and European suppliers.') }}
                    </p>
                </div>
                <form method="GET" action="{{ route('frontend.products.index') }}" class="w-full max-w-xl">
                    <label for="catalogue-search" class="sr-only">{{ __('Search catalogue') }}</label>
                    <div class="relative rounded-2xl border border-gray-200 bg-white shadow-sm">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                            <x-untitledui-search-md class="h-5 w-5" />
                        </div>
                        <input
                            id="catalogue-search"
                            name="q"
                            type="search"
                            value="{{ $searchTerm }}"
                            placeholder="{{ __('Search for drills, insulation, safety gear...') }}"
                            class="w-full rounded-2xl border-0 py-4 pl-12 pr-4 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                        <button type="submit" class="absolute inset-y-0 right-0 m-2 inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700">
                            {{ __('Search') }}
                        </button>
                    </div>
                </form>
            </div>

            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                <form method="GET" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <input type="hidden" name="q" value="{{ $searchTerm }}">
                    <div class="space-y-2">
                        <label for="filter" class="text-sm font-semibold text-gray-700">{{ __('Filter') }}</label>
                        <select
                            id="filter"
                            name="filter"
                            class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            @foreach($availableFilters as $value => $label)
                                <option value="{{ $value }}" @selected($appliedFilter === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label for="sort" class="text-sm font-semibold text-gray-700">{{ __('Sort by') }}</label>
                        <select
                            id="sort"
                            name="sort"
                            class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            @foreach($availableSorts as $value => $label)
                                <option value="{{ $value }}" @selected($appliedSort === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label for="per_page" class="text-sm font-semibold text-gray-700">{{ __('Results per page') }}</label>
                        <input
                            id="per_page"
                            name="per_page"
                            type="number"
                            min="6"
                            max="60"
                            value="{{ $perPage }}"
                            class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">
                            <x-untitledui-adjustments-vertical class="mr-2 h-4 w-4" />
                            {{ __('Update view') }}
                        </button>
                    </div>
                </form>
            </div>

            <div>
                <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">
                            {{ __('Showing :count products', ['count' => method_exists($products, 'total') ? $products->total() : $products->count()]) }}
                        </h2>
                        @if($appliedFilter)
                            <p class="text-sm text-gray-500">
                                {{ __('Filter applied: :filter', ['filter' => $availableFilters[$appliedFilter] ?? $appliedFilter]) }}
                            </p>
                        @endif
                        @if($searchTerm)
                            <p class="text-sm text-gray-500">
                                {{ __('Search term: ":term"', ['term' => $searchTerm]) }}
                            </p>
                        @endif
                    </div>
                </div>

                @include('frontend.catalogue.product-grid', ['products' => $products])
            </div>
        </div>
    </div>
@endsection

