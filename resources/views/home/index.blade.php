@extends('frontend.layouts.app')

@section('title', __('messages.frontend'))

@section('content')
    <div class="container mx-auto px-4 space-y-12" x-data>
        <section class="grid gap-8 lg:grid-cols-2 items-center">
            <div class="space-y-4">
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white">
                    {{ __('messages.frontend_home') }}
                </h1>
                <p class="text-lg text-gray-600 dark:text-gray-300">
                    {{ __('frontend.home.hero_description') }}
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('frontend.products.index') }}"
                       class="inline-flex items-center justify-center rounded-md bg-blue-600 px-5 py-3 text-white font-semibold shadow hover:bg-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500">
                        {{ __('frontend.home.shop_all_products') }}
                    </a>
                    <a href="{{ route('frontend.categories.index') }}"
                       class="inline-flex items-center justify-center rounded-md border border-blue-200 px-5 py-3 font-semibold text-blue-600 hover:bg-blue-50 dark:border-blue-900 dark:text-blue-300 dark:hover:bg-blue-950/40">
                        {{ __('frontend.home.browse_categories') }}
                    </a>
                </div>
            </div>
            <div class="rounded-3xl bg-gradient-to-br from-blue-600 via-blue-500 to-indigo-500 p-8 text-white shadow-xl">
                <p class="text-sm uppercase tracking-wide text-blue-100">{{ __('frontend.home.trusted_by') }}</p>
                <p class="mt-6 text-2xl font-semibold">{{ __('frontend.home.premium_message') }}</p>
                <dl class="mt-8 grid grid-cols-2 gap-6 text-left">
                    <div>
                        <dt class="text-sm text-blue-100">{{ __('frontend.home.featured_products') }}</dt>
                        <dd class="text-3xl font-bold">{{ $featuredProducts->count() }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-blue-100">{{ __('frontend.home.new_arrivals') }}</dt>
                        <dd class="text-3xl font-bold">{{ $latestProducts->count() }}</dd>
                    </div>
                </dl>
            </div>
        </section>

        <section aria-labelledby="home-featured-products" class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 id="home-featured-products" class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ __('frontend.home.featured_products') }}
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        {{ __('frontend.home.featured_products_description') }}
                    </p>
                </div>
                <a href="{{ route('frontend.products.index', ['sort' => 'latest']) }}"
                   class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-300 dark:hover:text-blue-200">
                    {{ __('frontend.home.view_all_products') }}
                </a>
            </div>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @forelse ($featuredProducts as $product)
                    <article class="group rounded-xl bg-white/80 p-5 shadow-sm ring-1 ring-gray-100 transition hover:-translate-y-1 hover:shadow-md dark:bg-slate-900/70 dark:ring-slate-800">
                        <a href="{{ route('frontend.products.show', $product) }}" class="block space-y-3">
                            <div class="aspect-video w-full overflow-hidden rounded-lg bg-gray-100 dark:bg-slate-800">
                                <img src="{{ $product->thumbnail ?: asset('images/placeholder-product.jpg') }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs uppercase tracking-wide text-blue-600 dark:text-blue-300">
                                    {{ optional($product->brand)->name ?? __('frontend.home.independent_brand') }}
                                </p>
                                <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-300">
                                    {{ $product->name }}
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2">
                                    {{ strip_tags($product->short_description ?? $product->description) }}
                                </p>
                                @if ($product->price)
                                    <p class="text-base font-semibold text-gray-900 dark:text-white">
                                        €{{ number_format((float) $product->price, 2) }}
                                    </p>
                                @endif
                            </div>
                        </a>
                    </article>
                @empty
                    <p class="col-span-full rounded-lg bg-white/80 p-6 text-center text-sm text-gray-600 ring-1 ring-gray-100 dark:bg-slate-900/60 dark:text-gray-300 dark:ring-slate-800">
                        {{ __('frontend.home.featured_empty') }}
                    </p>
                @endforelse
            </div>
        </section>

        <section aria-labelledby="home-latest-products" class="space-y-6">
            <h2 id="home-latest-products" class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ __('frontend.home.latest_arrivals') }}
            </h2>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @forelse ($latestProducts as $product)
                    <article class="rounded-xl bg-white/80 p-5 shadow-sm ring-1 ring-gray-100 transition hover:-translate-y-1 hover:shadow-md dark:bg-slate-900/70 dark:ring-slate-800">
                        <a href="{{ route('frontend.products.show', $product) }}" class="block space-y-2">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $product->name }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2">
                                {{ strip_tags($product->short_description ?? $product->description) }}
                            </p>
                            @if ($product->price)
                                <p class="text-base font-semibold text-gray-900 dark:text-white">
                                    €{{ number_format((float) $product->price, 2) }}
                                </p>
                            @endif
                        </a>
                    </article>
                @empty
                    <p class="col-span-full rounded-lg bg-white/80 p-6 text-center text-sm text-gray-600 ring-1 ring-gray-100 dark:bg-slate-900/60 dark:text-gray-300 dark:ring-slate-800">
                        {{ __('frontend.home.latest_empty') }}
                    </p>
                @endforelse
            </div>
        </section>

        <section aria-labelledby="home-category-tree" class="space-y-6">
            <h2 id="home-category-tree" class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ __('frontend.home.shop_by_category') }}
            </h2>
            <div class="grid gap-6 lg:grid-cols-3">
                @forelse ($categoryTree as $category)
                    <div class="rounded-xl bg-white/80 p-6 ring-1 ring-gray-100 dark:bg-slate-900/70 dark:ring-slate-800">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                <a href="{{ route('frontend.categories.show', $category) }}" class="hover:text-blue-600 dark:hover:text-blue-300">
                                    {{ $category->name }}
                                </a>
                            </h3>
                            <a href="{{ route('frontend.categories.show', $category) }}"
                               class="text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-300 dark:hover:text-blue-200">
                                {{ __('frontend.home.view_category') }}
                            </a>
                        </div>
                        @if ($category->children->isNotEmpty())
                            <ul class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                                @foreach ($category->children->take(6) as $child)
                                    <li>
                                        <a href="{{ route('frontend.categories.show', $child) }}" class="hover:text-blue-600 dark:hover:text-blue-300">
                                            {{ $child->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('frontend.home.category_fallback') }}
                            </p>
                        @endif
                    </div>
                @empty
                    <p class="col-span-full rounded-lg bg-white/80 p-6 text-center text-sm text-gray-600 ring-1 ring-gray-100 dark:bg-slate-900/60 dark:text-gray-300 dark:ring-slate-800">
                        {{ __('frontend.home.categories_empty') }}
                    </p>
                @endforelse
            </div>
        </section>

        <section aria-labelledby="home-featured-brands" class="space-y-6">
            <h2 id="home-featured-brands" class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ __('frontend.home.featured_brands') }}
            </h2>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @forelse ($featuredBrands as $brand)
                    <article class="rounded-xl bg-white/80 p-5 ring-1 ring-gray-100 transition hover:-translate-y-1 hover:shadow-md dark:bg-slate-900/70 dark:ring-slate-800">
                        <a href="{{ route('frontend.brands.show', $brand) }}" class="block space-y-2">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $brand->name }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-3">
                                {{ strip_tags($brand->description) ?? __('frontend.home.brand_description_fallback') }}
                            </p>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                {{ trans_choice('frontend.home.brand_product_count', $brand->published_products_count ?? $brand->products_count, ['count' => $brand->published_products_count ?? $brand->products_count]) }}
                            </p>
                        </a>
                    </article>
                @empty
                    <p class="col-span-full rounded-lg bg-white/80 p-6 text-center text-sm text-gray-600 ring-1 ring-gray-100 dark:bg-slate-900/60 dark:text-gray-300 dark:ring-slate-800">
                        {{ __('frontend.home.brands_empty') }}
                    </p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
