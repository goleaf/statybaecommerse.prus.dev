@extends('frontend.layouts.app')

@section('title', __('Home'))

@section('content')
    <div class="bg-gray-50">
        <section class="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-indigo-500 to-indigo-400 py-16 text-white">
            <div class="mx-auto flex max-w-6xl flex-col gap-10 px-6 lg:flex-row lg:items-center">
                <div class="w-full lg:w-1/2">
                    <span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider">
                        {{ __('Live catalogue') }}
                    </span>
                    <h1 class="mt-6 text-4xl font-extrabold leading-tight sm:text-5xl">
                        {{ __('Discover building supplies for every project') }}
                    </h1>
                    <p class="mt-4 text-lg text-indigo-100">
                        {{ __('Browse featured tools, fresh arrivals, and trending gear sourced directly from our live product data providers.') }}
                    </p>
                    <div class="mt-8 flex flex-wrap gap-4">
                        <a href="{{ route('frontend.products.index') }}" class="inline-flex items-center rounded-full bg-white px-5 py-3 font-semibold text-indigo-700 shadow hover:bg-indigo-50">
                            {{ __('Start shopping') }}
                        </a>
                        <a href="{{ route('frontend.categories.index') }}" class="inline-flex items-center rounded-full border border-white/60 px-5 py-3 font-semibold text-white hover:bg-white/10">
                            {{ __('Browse categories') }}
                        </a>
                    </div>
                </div>
                <div class="w-full lg:w-1/2">
                    <div class="grid gap-4 rounded-2xl bg-white/10 p-6 backdrop-blur">
                        <div class="grid grid-cols-2 gap-4 text-center">
                            <div class="rounded-xl bg-white/10 p-4">
                                <p class="text-sm uppercase tracking-wide text-indigo-100">{{ __('Products') }}</p>
                                <p class="mt-2 text-3xl font-bold">{{ number_format($stats['products'] ?? 0) }}</p>
                            </div>
                            <div class="rounded-xl bg-white/10 p-4">
                                <p class="text-sm uppercase tracking-wide text-indigo-100">{{ __('Categories') }}</p>
                                <p class="mt-2 text-3xl font-bold">{{ number_format($stats['categories'] ?? 0) }}</p>
                            </div>
                            <div class="rounded-xl bg-white/10 p-4">
                                <p class="text-sm uppercase tracking-wide text-indigo-100">{{ __('Brands') }}</p>
                                <p class="mt-2 text-3xl font-bold">{{ number_format($stats['brands'] ?? 0) }}</p>
                            </div>
                            <div class="rounded-xl bg-white/10 p-4">
                                <p class="text-sm uppercase tracking-wide text-indigo-100">{{ __('Reviews') }}</p>
                                <p class="mt-2 text-3xl font-bold">{{ number_format($stats['reviews'] ?? 0) }}</p>
                                <span class="text-xs text-indigo-100">{{ __('Avg.') }} {{ number_format($stats['average_rating'] ?? 0, 1) }}/5</span>
                            </div>
                        </div>
                        <p class="text-sm text-indigo-100">
                            {{ __('Figures refresh automatically from the live commerce engine so you always have up-to-date insight.') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-6xl space-y-16 px-6 py-16">
            <div class="space-y-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('Featured products') }}</h2>
                        <p class="text-sm text-gray-600">{{ __('Hand-picked items selected by merchandising and recommendation engines.') }}</p>
                    </div>
                    <a href="{{ route('frontend.products.index', ['sort' => 'popular']) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">
                        {{ __('View all') }}
                    </a>
                </div>
                @include('frontend.products.partials.grid', ['products' => $featuredProducts, 'emptyMessage' => __('Featured products will appear soon.')])
            </div>

            <div class="space-y-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('New arrivals') }}</h2>
                        <p class="text-sm text-gray-600">{{ __('Fresh additions that just landed in store.') }}</p>
                    </div>
                    <a href="{{ route('frontend.products.index', ['sort' => 'latest']) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">
                        {{ __('Browse catalogue') }}
                    </a>
                </div>
                @include('frontend.products.partials.grid', ['products' => $newArrivals, 'emptyMessage' => __('We are preparing new arrivals for you.')])
            </div>

            <div class="space-y-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('Trending now') }}</h2>
                        <p class="text-sm text-gray-600">{{ __('Popular products shoppers are engaging with in real time.') }}</p>
                    </div>
                    <a href="{{ route('frontend.products.index', ['sort' => 'popular']) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">
                        {{ __('See more') }}
                    </a>
                </div>
                @include('frontend.products.partials.grid', ['products' => $trendingProducts, 'emptyMessage' => __('Trending items will reappear shortly.')])
            </div>
        </section>

        <section class="border-t border-gray-200 bg-white py-16">
            <div class="mx-auto max-w-6xl px-6">
                <div class="flex flex-col gap-4 text-center">
                    <h2 class="text-2xl font-bold text-gray-900">{{ __('Shop by category') }}</h2>
                    <p class="text-sm text-gray-600">{{ __('Jump into a product family curated from live inventory counts.') }}</p>
                </div>

                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse($topCategories as $category)
                        <a href="{{ route('frontend.categories.show', $category) }}" class="group rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                            <div class="flex items-start justify-between">
                                <h3 class="text-lg font-semibold text-gray-900 group-hover:text-indigo-600">{{ $category->name }}</h3>
                                <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">
                                    {{ trans_choice('{0}No products|{1}1 product|[2,*] :count products', $category->products_count, ['count' => $category->products_count]) }}
                                </span>
                            </div>
                            @if($category->description)
                                <p class="mt-3 text-sm text-gray-600">{{ \Illuminate\Support\Str::limit(strip_tags($category->description), 120) }}</p>
                            @endif
                        </a>
                    @empty
                        <p class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-8 text-center text-sm text-gray-600">
                            {{ __('Categories are being prepared. Check back soon!') }}
                        </p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="bg-gray-100 py-16">
            <div class="mx-auto max-w-6xl px-6">
                <div class="flex flex-col gap-4 text-center">
                    <h2 class="text-2xl font-bold text-gray-900">{{ __('Brands we trust') }}</h2>
                    <p class="text-sm text-gray-600">{{ __('Recognised manufacturers supplying the marketplace.') }}</p>
                </div>
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse($topBrands as $brand)
                        <a href="{{ route('frontend.brands.show', $brand) }}" class="flex flex-col items-start rounded-xl bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                                    <span class="text-base font-semibold">{{ \Illuminate\Support\Str::upper(mb_substr($brand->name, 0, 2)) }}</span>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $brand->name }}</h3>
                                    <p class="text-xs uppercase tracking-wide text-gray-500">{{ trans_choice('{0}No products|{1}1 product|[2,*] :count products', $brand->products_count, ['count' => $brand->products_count]) }}</p>
                                </div>
                            </div>
                            @if($brand->description)
                                <p class="mt-4 text-sm text-gray-600">{{ \Illuminate\Support\Str::limit(strip_tags($brand->description), 110) }}</p>
                            @endif
                        </a>
                    @empty
                        <p class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-8 text-center text-sm text-gray-600">
                            {{ __('Brands will appear once the catalogue is synced.') }}
                        </p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
@endsection
