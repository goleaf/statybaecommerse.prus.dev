@extends('frontend.layouts.app')

@section('title', __('Product catalogue'))

@section('content')
    <div class="bg-gray-50 py-12">
        <div class="mx-auto max-w-6xl space-y-10 px-6">
            <header class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-2">
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('Product catalogue') }}</h1>
                    <p class="text-sm text-gray-600">{{ __('Filter, sort, and explore the live storefront inventory.') }}</p>
                </div>
                <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-500">
                    {{ __('Back to home') }}
                </a>
            </header>

            <form method="get" action="{{ route('frontend.products.index') }}" class="grid gap-4 rounded-xl bg-white p-6 shadow-sm lg:grid-cols-4">
                <div class="lg:col-span-2">
                    <label for="q" class="text-sm font-medium text-gray-700">{{ __('Search products') }}</label>
                    <input
                        id="q"
                        type="search"
                        name="q"
                        value="{{ $activeFilters['search'] ?? request('q') }}"
                        placeholder="{{ __('Search for tools, materials, or brands') }}"
                        class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    >
                </div>

                <div>
                    <label for="category" class="text-sm font-medium text-gray-700">{{ __('Category') }}</label>
                    <select
                        id="category"
                        name="category"
                        class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    >
                        <option value="">{{ __('All categories') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="brand" class="text-sm font-medium text-gray-700">{{ __('Brand') }}</label>
                    <select
                        id="brand"
                        name="brand"
                        class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    >
                        <option value="">{{ __('All brands') }}</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->slug }}" @selected(request('brand') === $brand->slug)>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="sort" class="text-sm font-medium text-gray-700">{{ __('Sort by') }}</label>
                    <select
                        id="sort"
                        name="sort"
                        class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    >
                        @foreach($availableSorts as $key => $label)
                            <option value="{{ $key }}" @selected(($activeFilters['sort'] ?? request('sort', 'latest')) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                @php
                    $totalProducts = method_exists($products, 'total') ? $products->total() : $products->count();
                @endphp
                <div class="lg:col-span-4 flex flex-wrap items-center justify-between gap-3">
                    <div class="text-xs text-gray-500">
                        @if($totalProducts > 0)
                            {{ trans_choice('{0}No products found|{1}Showing 1 product|[2,*] Showing :count products', $totalProducts, ['count' => $totalProducts]) }}
                        @else
                            {{ __('No products found with the current filters.') }}
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
                            {{ __('Apply filters') }}
                        </button>
                        <a href="{{ route('frontend.products.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">
                            {{ __('Reset') }}
                        </a>
                    </div>
                </div>
            </form>

            <section>
                @include('frontend.products.partials.grid', ['products' => $products, 'emptyMessage' => __('Try broadening your filters to see available products.')])
            </section>
        </div>
    </div>
@endsection
