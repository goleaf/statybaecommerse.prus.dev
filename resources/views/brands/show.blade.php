@extends('frontend.layouts.app')

@section('title', $brand->name)

@section('content')
    <div class="container mx-auto px-4">
        <nav class="mb-6 text-sm text-gray-500 dark:text-gray-400" aria-label="Breadcrumb">
            <ol class="flex flex-wrap items-center gap-1">
                <li><a href="{{ route('home') }}" class="hover:text-blue-600 dark:hover:text-blue-300">{{ __('Home') }}</a></li>
                <li aria-hidden="true" class="px-1">/</li>
                <li><a href="{{ route('frontend.brands.index') }}" class="hover:text-blue-600 dark:hover:text-blue-300">{{ __('Brands') }}</a></li>
                <li aria-hidden="true" class="px-1">/</li>
                <li class="font-semibold text-gray-900 dark:text-white">{{ $brand->name }}</li>
            </ol>
        </nav>

        <div class="mb-8 rounded-2xl bg-white/80 p-6 shadow-sm ring-1 ring-gray-100 dark:bg-slate-900/70 dark:ring-slate-800">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-3">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $brand->name }}</h1>
                    @if ($brand->description)
                        <div class="prose max-w-none text-gray-600 dark:prose-invert dark:text-gray-300">
                            {!! $brand->description !!}
                        </div>
                    @else
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            {{ __('Learn more about this manufacturer and the equipment they provide.') }}
                        </p>
                    @endif
                    @if ($brand->website)
                        <a href="{{ $brand->website }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-300 dark:hover:text-blue-200">
                            <span>{{ __('Visit website') }}</span>
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M12.293 2.293a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L14 5.414V13a1 1 0 11-2 0V5.414L9.707 7.707A1 1 0 018.293 6.293l4-4z"/><path d="M5 9a1 1 0 011 1v5h8v-5a1 1 0 112 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5a1 1 0 011-1z"/></svg>
                        </a>
                    @endif
                </div>
                <div class="rounded-xl bg-blue-50 px-4 py-3 text-sm text-blue-700 dark:bg-blue-900/40 dark:text-blue-200">
                    {{ trans_choice('{0}No active products yet|{1}1 active product|[2,*]:count active products', $products->total(), ['count' => $products->total()]) }}
                </div>
            </div>
        </div>

        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('Products from this brand') }}</h2>
            </div>
            <form method="GET" action="{{ url()->current() }}" class="grid w-full gap-4 sm:grid-cols-2 lg:flex lg:w-auto lg:items-end lg:gap-4">
                <div class="flex flex-col">
                    <label for="q" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Search') }}</label>
                    <input type="text" id="q" name="q" value="{{ $filters['search'] ?? '' }}"
                           class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                           placeholder="{{ __('Search products from this brand…') }}">
                </div>
                <div class="flex flex-col">
                    <label for="category" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Category') }}</label>
                    <select id="category" name="category" class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                        <option value="">{{ __('All categories') }}</option>
                        @foreach ($availableCategories as $category)
                            <option value="{{ $category->slug }}" @selected(($filters['categories'][0] ?? null) === $category->slug)>
                                {{ $category->name }}
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
                        {{ __('Filter products') }}
                    </button>
                </div>
            </form>
        </div>

        @include('products.partials.grid', ['products' => $products, 'emptyMessage' => __('No products found for this brand yet.')])
    </div>
@endsection
