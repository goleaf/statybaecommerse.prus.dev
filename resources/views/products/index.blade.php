@extends('frontend.layouts.app')

@section('title', $pageTitle ?? __('Products'))

@section('content')
    <div class="container mx-auto px-4">
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ $pageTitle ?? __('Products') }}
                </h1>
                @if (! empty($filters['search']))
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                        {{ __('Showing results for “:term”.', ['term' => $filters['search']]) }}
                    </p>
                @endif
            </div>
            <form method="GET" action="{{ url()->current() }}" class="grid w-full gap-4 sm:grid-cols-2 lg:flex lg:w-auto lg:items-end lg:gap-4">
                <div class="flex flex-col">
                    <label for="q" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Search') }}</label>
                    <input type="text" id="q" name="q" value="{{ $filters['search'] ?? '' }}"
                           class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                           placeholder="{{ __('Search products…') }}">
                </div>
                <div class="flex flex-col">
                    <label for="category" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Category') }}</label>
                    <select id="category" name="category" class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                        <option value="">{{ __('All categories') }}</option>
                        @foreach ($availableCategories as $category)
                            <option value="{{ $category->slug }}" @selected(($filters['categories'][0] ?? null) === $category->slug)>
                                {{ $category->trans('name') ?? $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col">
                    <label for="brand" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Brand') }}</label>
                    <select id="brand" name="brand" class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                        <option value="">{{ __('All brands') }}</option>
                        @foreach ($availableBrands as $brand)
                            <option value="{{ $brand->slug }}" @selected(($filters['brands'][0] ?? null) === $brand->slug)>
                                {{ $brand->trans('name') ?? $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col">
                    <label for="sort" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Sort by') }}</label>
                    <select id="sort" name="sort" class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                        <option value="latest" @selected(($filters['sort'] ?? 'latest') === 'latest')>{{ __('Newest first') }}</option>
                        <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>{{ __('Price: Low to High') }}</option>
                        <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>{{ __('Price: High to Low') }}</option>
                        <option value="name_asc" @selected(($filters['sort'] ?? '') === 'name_asc')>{{ __('Name: A to Z') }}</option>
                        <option value="name_desc" @selected(($filters['sort'] ?? '') === 'name_desc')>{{ __('Name: Z to A') }}</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="inline-flex items-center rounded-md bg-blue-600 px-5 py-2.5 font-semibold text-white shadow hover:bg-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500">
                        {{ __('Apply filters') }}
                    </button>
                </div>
            </form>
        </div>

        @include('products.partials.grid', ['products' => $products])
    </div>
@endsection
