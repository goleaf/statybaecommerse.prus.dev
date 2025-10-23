@extends('frontend.layouts.app')

@section('title', __('Welcome to our store'))

@section('content')
    <div class="container mx-auto px-4 space-y-12" x-data>
        <section class="grid gap-8 lg:grid-cols-2 items-center">
            <div class="space-y-4">
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white">
                    {{ __('Discover professional tools for every project') }}
                </h1>
                <p class="text-lg text-gray-600 dark:text-gray-300">
                    {{ __('Browse featured products, explore trusted brands, and find the right equipment for your next build.') }}
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('frontend.products.index') }}"
                       class="inline-flex items-center justify-center rounded-md bg-blue-600 px-5 py-3 text-white font-semibold shadow hover:bg-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500">
                        {{ __('Shop all products') }}
                    </a>
                    <a href="{{ route('frontend.categories.index') }}"
                       class="inline-flex items-center justify-center rounded-md border border-blue-200 px-5 py-3 font-semibold text-blue-600 hover:bg-blue-50 dark:border-blue-900 dark:text-blue-300 dark:hover:bg-blue-950/40">
                        {{ __('Browse categories') }}
                    </a>
                </div>
            </div>
            <div class="rounded-3xl bg-gradient-to-br from-blue-600 via-blue-500 to-indigo-500 p-8 text-white shadow-xl">
                <p class="text-sm uppercase tracking-wide text-blue-100">{{ __('Trusted by builders across Lithuania') }}</p>
                <p class="mt-6 text-2xl font-semibold">{{ __('Premium equipment, expert guidance, and fast delivery to every region.') }}</p>
                <dl class="mt-8 grid grid-cols-2 gap-6 text-left">
                    <div>
                        <dt class="text-sm text-blue-100">{{ __('Featured products') }}</dt>
                        <dd class="text-3xl font-bold">{{ $featuredProducts->count() }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-blue-100">{{ __('New arrivals') }}</dt>
                        <dd class="text-3xl font-bold">{{ $latestProducts->count() }}</dd>
                    </div>
                </dl>
            </div>
        </section>

        <section aria-labelledby="home-featured-products" class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 id="home-featured-products" class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ __('Featured products') }}
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        {{ __('Our team highlights popular picks with outstanding performance and value.') }}
                    </p>
                </div>
                <a href="{{ route('frontend.products.index', ['sort' => 'latest']) }}"
                   class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-300 dark:hover:text-blue-200">
                    {{ __('View all products') }}
                </a>
            </div>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @forelse ($featuredProducts as $product)
                    <article class="group rounded-xl bg-white/80 p-5 shadow-sm ring-1 ring-gray-100 transition hover:-translate-y-1 hover:shadow-md dark:bg-slate-900/70 dark:ring-slate-800">
                        <a href="{{ route('frontend.products.show', $product) }}" class="block space-y-3">
                            <div class="aspect-video w-full overflow-hidden rounded-lg bg-gray-100 dark:bg-slate-800">
                                @if ($product->thumbnail)
                                    <img src="{{ $product->thumbnail }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full items-center justify-center text-sm text-gray-400">
                                        {{ __('Image coming soon') }}
                                    </div>
                                @endif
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs uppercase tracking-wide text-blue-600 dark:text-blue-300">
                                    {{ optional($product->brand)->name ?? __('Independent brand') }}
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
                        {{ __('No featured products are available yet. Please check back soon!') }}
                    </p>
                @endforelse
            </div>
        </section>

        <section aria-labelledby="home-latest-products" class="space-y-6">
            <h2 id="home-latest-products" class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ __('Latest arrivals') }}
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
                        {{ __('We are preparing new products. Come back later for updates!') }}
                    </p>
                @endforelse
            </div>
        </section>

        <section aria-labelledby="home-category-tree" class="space-y-6">
            <h2 id="home-category-tree" class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ __('Shop by category') }}
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
                                {{ __('View') }}
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
                                {{ __('Explore a curated collection of tools in this category.') }}
                            </p>
                        @endif
                    </div>
                @empty
                    <p class="col-span-full rounded-lg bg-white/80 p-6 text-center text-sm text-gray-600 ring-1 ring-gray-100 dark:bg-slate-900/60 dark:text-gray-300 dark:ring-slate-800">
                        {{ __('Categories are being configured. Please check back later.') }}
                    </p>
                @endforelse
            </div>
        </section>

        <section aria-labelledby="home-featured-brands" class="space-y-6">
            <h2 id="home-featured-brands" class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ __('Featured brands') }}
            </h2>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @forelse ($featuredBrands as $brand)
                    <article class="rounded-xl bg-white/80 p-5 ring-1 ring-gray-100 transition hover:-translate-y-1 hover:shadow-md dark:bg-slate-900/70 dark:ring-slate-800">
                        <a href="{{ route('frontend.brands.show', $brand) }}" class="block space-y-2">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $brand->name }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-3">
                                {{ strip_tags($brand->description) ?? __('Reliable manufacturer with trusted quality products.') }}
                            </p>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                {{ trans_choice('{0}No products yet|{1}1 available product|[2,*]:count available products', $brand->published_products_count ?? $brand->products_count, ['count' => $brand->published_products_count ?? $brand->products_count]) }}
                            </p>
                        </a>
                    </article>
                @empty
                    <p class="col-span-full rounded-lg bg-white/80 p-6 text-center text-sm text-gray-600 ring-1 ring-gray-100 dark:bg-slate-900/60 dark:text-gray-300 dark:ring-slate-800">
                        {{ __('Brands will appear here once they publish new products.') }}
                    </p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
