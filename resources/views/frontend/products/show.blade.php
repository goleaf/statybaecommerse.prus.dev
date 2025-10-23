@extends('frontend.layouts.app')

@section('title', $product->seo_title ?: $product->name)
@section('description', $product->seo_description ?: str($product->short_description)->stripTags()->limit(160))

@section('content')
    <div class="bg-gray-50 py-12">
        <div class="mx-auto max-w-6xl space-y-12 px-4 sm:px-6 lg:px-8">
            <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
                <ol class="flex flex-wrap items-center gap-2">
                    <li>
                        <a href="{{ route('frontend.home') }}" class="text-blue-600 hover:text-blue-700">{{ __('frontend.navigation.home') }}</a>
                    </li>
                    <li>/</li>
                    <li>
                        <a href="{{ route('frontend.products.index') }}" class="text-blue-600 hover:text-blue-700">{{ __('Products') }}</a>
                    </li>
                    <li>/</li>
                    <li class="text-gray-700">{{ $product->name }}</li>
                </ol>
            </nav>

            <div class="mt-10 grid gap-10 lg:grid-cols-[1.1fr_1fr]">
                <div class="space-y-6">
                    <div class="overflow-hidden rounded-3xl border border-gray-200 bg-gray-50">
                        <img src="{{ $product->main_image ?: 'https://via.placeholder.com/1200x1200.png?text=Product' }}" alt="{{ $product->name }}" class="h-full w-full object-cover" loading="lazy" />
                    </div>
                    @if ($product->description)
                        <article class="prose prose-indigo max-w-none">
                            {!! $product->description !!}
                        </article>
                    @endif
                </div>

                <aside class="space-y-6 rounded-3xl border border-gray-200 bg-white p-8 shadow-sm">
                    <div class="space-y-3">
                        <span class="inline-flex items-center gap-2 rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-600">
                            {{ $product->brand?->name ?? __('Featured product') }}
                        </span>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $product->name }}</h1>
                        <p class="text-sm text-gray-600">{{ str($product->short_description ?: strip_tags($product->description))->limit(180) }}</p>
                    </div>

                    <div class="flex items-baseline gap-3">
                        <span class="text-3xl font-semibold text-indigo-600">{{ $product->formatted_price }}</span>
                        @if ($product->sale_price && $product->sale_price < $product->price)
                            <span class="text-lg font-medium text-gray-400 line-through">{{ app_money_format($product->price) }}</span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <x-untitledui-star class="h-5 w-5 text-amber-500" />
                        <span>{{ number_format($product->average_rating, 1) }} / 5</span>
                        <span class="text-gray-400">·</span>
                        <span>{{ __(':count reviews', ['count' => number_format($product->reviews_count)]) }}</span>
                    </div>

                    <div class="space-y-3 text-sm text-gray-600">
                        <div class="flex items-center gap-2">
                            <x-untitledui-tag class="h-5 w-5 text-gray-400" />
                            <span>{{ __('SKU: :sku', ['sku' => $product->sku]) }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-untitledui-layers-three class="h-5 w-5 text-gray-400" />
                            <span>{{ __('Type: :type', ['type' => ucfirst($product->type)]) }}</span>
                        </div>
                        @if ($product->categories->isNotEmpty())
                            <div class="flex items-start gap-2">
                                <x-untitledui-folder-open class="mt-0.5 h-5 w-5 text-gray-400" />
                                <span>
                                    {{ __('Categories:') }}
                                    {{ $product->categories->pluck('name')->join(', ') }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="space-y-4 rounded-3xl bg-indigo-50 p-6 text-sm text-indigo-900">
                        <p class="font-semibold">{{ __('Fast facts') }}</p>
                        <ul class="space-y-2">
                            <li class="flex items-center gap-2">
                                <x-untitledui-check class="h-4 w-4" />
                                <span>{{ $product->is_in_stock ? __('In stock and ready to ship') : __('Currently out of stock') }}</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <x-untitledui-check class="h-4 w-4" />
                                <span>{{ __('Ships from our Lithuanian warehouse') }}</span>
                            </li>
                        </ul>
                    </div>
                </aside>
            </div>

            <section class="mt-16">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-semibold text-gray-900">{{ __('Related products') }}</h2>
                    <a href="{{ route('frontend.products.index', ['category' => optional($primaryCategory)->getKey()]) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                        {{ __('Browse the category') }}
                        <x-untitledui-arrow-narrow-right class="h-4 w-4" />
                    </a>
                </div>
                <div class="mt-8">
                    @include('frontend.products.partials.product-grid', ['products' => $relatedProducts, 'emptyMessage' => __('No related products are available yet.')])
                </div>
            </section>
        </div>
    </div>
@endsection
