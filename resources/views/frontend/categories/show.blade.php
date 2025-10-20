@extends('frontend.layouts.app')

@section('title', $category->name)

@section('content')
    <div class="bg-gray-50 py-12">
        <div class="mx-auto max-w-7xl space-y-12 px-4 sm:px-6 lg:px-8">
            <div class="space-y-4">
                <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
                    <ol class="flex flex-wrap items-center gap-2">
                        <li><a href="{{ route('frontend.home') }}" class="text-blue-600 hover:text-blue-700">{{ __('Home') }}</a></li>
                        <li>/</li>
                        <li><a href="{{ route('frontend.categories.index') }}" class="text-blue-600 hover:text-blue-700">{{ __('Categories') }}</a></li>
                        <li>/</li>
                        <li class="text-gray-700">{{ $category->name }}</li>
                    </ol>
                </nav>
                <div class="flex flex-col gap-6 rounded-3xl border border-gray-200 bg-white p-8 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                    <div class="space-y-3">
                        <span class="inline-flex items-center gap-2 rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-700">
                            <x-untitledui-tag class="h-4 w-4" />
                            {{ __('Category spotlight') }}
                        </span>
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">{{ $category->name }}</h1>
                        <p class="text-gray-600">{{ $category->description }}</p>
                        @if($category->parent)
                            <p class="text-sm text-gray-500">
                                {{ __('Part of :parent', ['parent' => $category->parent->name]) }}
                            </p>
                        @endif
                    </div>
                    <div class="flex flex-col gap-4 text-sm text-gray-600">
                        <div class="flex items-center gap-2">
                            <x-untitledui-cube class="h-4 w-4 text-blue-500" />
                            {{ __('Visible products: :count', ['count' => method_exists($products, 'total') ? $products->total() : $products->count()]) }}
                        </div>
                        @if($category->children->isNotEmpty())
                            <div>
                                <p class="font-semibold text-gray-900">{{ __('Popular subcategories') }}</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach($category->children->take(6) as $child)
                                        <a href="{{ route('frontend.categories.show', $child) }}" class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                            {{ $child->name }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                <form method="GET" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="space-y-2">
                        <label for="category-sort" class="text-sm font-semibold text-gray-700">{{ __('Sort by') }}</label>
                        <select id="category-sort" name="sort" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach($availableSorts as $value => $label)
                                <option value="{{ $value }}" @selected($appliedSort === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label for="category-filter" class="text-sm font-semibold text-gray-700">{{ __('Filter') }}</label>
                        <select id="category-filter" name="filter" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach($availableFilters as $value => $label)
                                <option value="{{ $value }}" @selected($appliedFilter === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label for="category-per-page" class="text-sm font-semibold text-gray-700">{{ __('Results per page') }}</label>
                        <input id="category-per-page" type="number" min="6" max="60" name="per_page" value="{{ $perPage }}" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">
                            {{ __('Update view') }}
                        </button>
                    </div>
                </form>
            </div>

            <section class="space-y-6">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <h2 class="text-2xl font-semibold text-gray-900">{{ __('Catalogue for :category', ['category' => $category->name]) }}</h2>
                    @if($searchTerm)
                        <p class="text-sm text-gray-500">{{ __('Search term: ":term"', ['term' => $searchTerm]) }}</p>
                    @endif
                </div>
                @include('frontend.catalogue.product-grid', ['products' => $products])
            </section>
        </div>
    </div>
@endsection

