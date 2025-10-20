@extends('frontend.layouts.app')

@section('title', $category->name)

@section('content')
    <div class="bg-gray-50 py-12">
        <div class="mx-auto max-w-6xl space-y-10 px-6">
            <nav class="text-sm text-gray-500">
                <ol class="flex flex-wrap items-center gap-2">
                    @foreach($breadcrumbs as $crumb)
                        <li class="flex items-center gap-2">
                            @if(! $loop->last)
                                <a href="{{ $crumb['url'] }}" class="hover:text-indigo-600">{{ $crumb['label'] }}</a>
                                <span aria-hidden="true">/</span>
                            @else
                                <span class="text-gray-700">{{ $crumb['label'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>

            @php
                $totalProducts = method_exists($products, 'total') ? $products->total() : $products->count();
            @endphp
            <header class="space-y-4 rounded-2xl bg-white p-8 shadow">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-600">{{ __('Category') }}</span>
                        <h1 class="mt-3 text-3xl font-bold text-gray-900">{{ $category->name }}</h1>
                        @if($category->description)
                            <p class="mt-3 text-sm text-gray-600">{{ \Illuminate\Support\Str::limit(strip_tags($category->description), 220) }}</p>
                        @endif
                    </div>
                    <div class="rounded-xl bg-gray-50 p-4 text-sm text-gray-600">
                        <p>{{ __('Products available:') }} <span class="font-semibold text-gray-900">{{ $totalProducts }}</span></p>
                        @if($childCategories->isNotEmpty())
                            <p class="mt-2 text-xs uppercase tracking-wide text-gray-500">{{ __('Subcategories') }}</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach($childCategories as $child)
                                    <a href="{{ route('frontend.categories.show', $child) }}" class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold text-indigo-600 shadow-sm hover:bg-indigo-50">{{ $child->name }}</a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </header>

            <form method="get" action="{{ route('frontend.categories.show', $category) }}" class="grid gap-4 rounded-xl bg-white p-6 shadow-sm lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)] lg:items-end">
                <div>
                    <label for="q" class="text-sm font-medium text-gray-700">{{ __('Search within category') }}</label>
                    <input
                        id="q"
                        type="search"
                        name="q"
                        value="{{ $activeFilters['search'] ?? request('q') }}"
                        placeholder="{{ __('Search products...') }}"
                        class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    >
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
                <div class="flex flex-wrap justify-end gap-3 lg:col-span-2">
                    <button type="submit" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
                        {{ __('Apply filters') }}
                    </button>
                    <a href="{{ route('frontend.categories.show', $category) }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">
                        {{ __('Reset') }}
                    </a>
                </div>
            </form>

            <section>
                @include('frontend.products.partials.grid', ['products' => $products, 'emptyMessage' => __('No products match the selected filters yet.')])
            </section>
        </div>
    </div>
@endsection
