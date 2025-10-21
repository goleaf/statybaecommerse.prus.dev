@extends('frontend.layouts.app')

@section('title', $brand->name)

@section('content')
    <div class="bg-gray-50 py-12">
        <div class="mx-auto max-w-7xl space-y-12 px-4 sm:px-6 lg:px-8">
            <div class="space-y-4">
                <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
                    <ol class="flex flex-wrap items-center gap-2">
                        <li><a href="{{ route('frontend.home') }}" class="text-emerald-600 hover:text-emerald-700">{{ __('frontend.navigation.home') }}</a></li>
                        <li>/</li>
                        <li><a href="{{ route('frontend.brands.index') }}" class="text-emerald-600 hover:text-emerald-700">{{ __('Brands') }}</a></li>
                        <li>/</li>
                        <li class="text-gray-700">{{ $brand->name }}</li>
                    </ol>
                </nav>
                <div class="flex flex-col gap-6 rounded-3xl border border-gray-200 bg-white p-8 shadow-sm lg:flex-row lg:items-center lg:justify-between">
                    <div class="space-y-3">
                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-700">
                            <x-untitledui-sparkle class="h-4 w-4" />
                            {{ __('Brand spotlight') }}
                        </span>
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">{{ $brand->name }}</h1>
                        <p class="text-gray-600">{{ $brand->description ?? __('Premium-grade tools and construction supplies trusted across Europe.') }}</p>
                        @if($brand->website)
                            <a href="{{ $brand->website }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-600 hover:text-emerald-700">
                                <x-untitledui-link-05 class="h-4 w-4" />
                                {{ parse_url($brand->website, PHP_URL_HOST) ?? $brand->website }}
                            </a>
                        @endif
                    </div>
                    <div class="grid gap-4 rounded-2xl border border-emerald-100 bg-emerald-50 p-6 text-sm text-emerald-900">
                        <div class="flex items-center gap-3">
                            <x-untitledui-layers-three class="h-5 w-5" />
                            {{ __('Active catalogue items: :count', ['count' => method_exists($products, 'total') ? $products->total() : $products->count()]) }}
                        </div>
                        <div class="flex items-center gap-3">
                            <x-untitledui-map-pin class="h-5 w-5" />
                            {{ __('Preferred across Lithuania and neighbouring markets') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                <form method="GET" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="space-y-2">
                        <label for="brand-sort" class="text-sm font-semibold text-gray-700">{{ __('Sort by') }}</label>
                        <select id="brand-sort" name="sort" class="w-full rounded-xl border-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            @foreach($availableSorts as $value => $label)
                                <option value="{{ $value }}" @selected($appliedSort === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label for="brand-filter" class="text-sm font-semibold text-gray-700">{{ __('Filter') }}</label>
                        <select id="brand-filter" name="filter" class="w-full rounded-xl border-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            @foreach($availableFilters as $value => $label)
                                <option value="{{ $value }}" @selected($appliedFilter === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label for="brand-per-page" class="text-sm font-semibold text-gray-700">{{ __('Results per page') }}</label>
                        <input id="brand-per-page" type="number" min="6" max="60" name="per_page" value="{{ $perPage }}" class="w-full rounded-xl border-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
                            {{ __('Update view') }}
                        </button>
                    </div>
                </form>
            </div>

            <section class="space-y-6">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <h2 class="text-2xl font-semibold text-gray-900">{{ __('Products by :brand', ['brand' => $brand->name]) }}</h2>
                    @if($searchTerm)
                        <p class="text-sm text-gray-500">{{ __('Search term: ":term"', ['term' => $searchTerm]) }}</p>
                    @endif
                </div>
                @include('frontend.catalogue.product-grid', ['products' => $products])
            </section>
        </div>
    </div>
@endsection

