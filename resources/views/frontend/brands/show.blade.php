@extends('frontend.layouts.app')

@section('title', $brand->seo_title ?: $brand->name)
@section('description', $brand->seo_description ?: str($brand->description)->stripTags()->limit(160))

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
                    <div class="flex flex-col gap-3 rounded-3xl bg-white/10 p-6 text-sm text-white/80">
                        <div class="flex items-center justify-between">
                            <span>{{ __('Products live') }}</span>
                            <span class="text-lg font-semibold text-white">{{ number_format($products->total()) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>{{ __('Categories served') }}</span>
                            <span class="text-lg font-semibold text-white">{{ number_format($relatedCategories->count()) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>{{ __('Sort options available') }}</span>
                            <span class="text-lg font-semibold text-white">{{ number_format(count($availableSorts)) }}</span>
                        </div>
                    </div>
                </div>
            </header>

            <section class="mt-10 space-y-8">
                <div class="flex flex-col gap-4 rounded-3xl border border-gray-200 bg-white p-6 sm:flex-row sm:items-center sm:justify-between">
                    <form method="get" class="flex flex-wrap items-center gap-3 text-sm font-medium text-gray-700">
                        <span class="text-gray-500">{{ __('Quick filters:') }}</span>
                        @foreach ($availableFilters as $key => $label)
                            <label class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-gray-50 px-3 py-1">
                                <input type="radio" name="filter" value="{{ $key }}" @checked($activeFilter === $key) class="h-4 w-4 text-rose-600" />
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                        <label class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-gray-50 px-3 py-1">
                            <input type="radio" name="filter" value="" @checked(! $activeFilter) class="h-4 w-4 text-rose-600" />
                            <span>{{ __('All products') }}</span>
                        </label>
                        <button type="submit" class="rounded-full bg-white/20 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow-sm hover:bg-white/30">{{ __('Apply') }}</button>
                    </form>

                    <form method="get" class="flex items-center gap-3 text-sm">
                        @foreach (request()->except('sort') as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}" />
                        @endforeach
                        <label for="sort" class="text-gray-500">{{ __('Sort by') }}</label>
                        <select id="sort" name="sort" class="rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 focus:border-rose-500 focus:outline-none">
                            @foreach ($availableSorts as $key => $label)
                                <option value="{{ $key }}" @selected($activeSort === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="rounded-full border border-rose-500 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50">{{ __('Update') }}</button>
                    </form>
                </div>

                @include('frontend.products.partials.product-grid', ['products' => $products, 'emptyMessage' => __('No products have been published for this brand yet.')])
            </section>

            <section class="mt-12 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900">{{ __('Categories this brand powers') }}</h2>
                <ul class="mt-4 grid gap-3 text-sm text-gray-700 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse ($relatedCategories as $category)
                        <li class="rounded-2xl border border-gray-200 bg-gray-50 p-4 transition hover:border-rose-500 hover:bg-white">
                            <a href="{{ route('frontend.categories.show', $category) }}" class="font-semibold text-gray-900 hover:text-rose-600">{{ $category->name }}</a>
                            <p class="mt-2 text-xs text-gray-600">{{ __(':count products in stock', ['count' => number_format($category->published_products_count ?? 0)]) }}</p>
                        </li>
                    @empty
                        <li class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-500">{{ __('Category highlights will appear as soon as inventory is linked.') }}</li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>
@endsection
