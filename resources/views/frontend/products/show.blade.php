@extends('frontend.layouts.app')

@section('title', $product->seo_title ?: $product->name)
@section('description', $product->seo_description ?: str($product->short_description)->stripTags()->limit(160))

@section('content')
    <div class="bg-gray-50 py-12">
        <div class="mx-auto max-w-6xl space-y-12 px-4 sm:px-6 lg:px-8">
            <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
                <ol class="flex flex-wrap items-center gap-2">
                    <li>
                        <a href="{{ route('home') }}" class="text-blue-600 hover:text-blue-700">{{ __('messages.frontend') }}</a>
                    </li>
                    <li>/</li>
                    <li>
                        <a href="{{ route('frontend.products.index') }}" class="text-blue-600 hover:text-blue-700">{{ __('messages.products') }}</a>
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
                            {{ $product->brand?->name ?? __('ui.featured_product') }}
                        </span>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $product->name }}</h1>
                        <p class="text-sm text-gray-600">{{ str($product->short_description ?: strip_tags($product->description))->limit(180) }}</p>
                    </div>

                    <div class="flex items-baseline gap-3">
                        <span class="text-3xl font-semibold text-indigo-600">{{ $product->formatted_price }}</span>
                    </div>

                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <x-untitledui-star class="h-5 w-5 text-amber-500" />
                        <span>{{ number_format($product->average_rating, 1) }} / 5</span>
                        <span class="text-gray-400">·</span>
                        <span>{{ __('ui.count_reviews', ['count' => number_format($product->reviews_count)]) }}</span>
                    </div>

                    <div class="space-y-3 text-sm text-gray-600">
                        <div class="flex items-center gap-2">
                            <x-untitledui-tag class="h-5 w-5 text-gray-400" />
                            <span>{{ __('ui.sku_sku', ['sku' => $product->sku]) }}</span>
                        </div>
                        @if ($product->categories->isNotEmpty())
                            <div class="flex items-start gap-2">
                                <x-untitledui-folder-open class="mt-0.5 h-5 w-5 text-gray-400" />
                                <span>
                                    {{ __('ui.categories') }}
                                    {{ $product->categories->pluck('name')->join(', ') }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="space-y-4 rounded-3xl bg-indigo-50 p-6 text-sm text-indigo-900">
                        <p class="font-semibold">{{ __('ui.fast_facts') }}</p>
                        <ul class="space-y-2">
                            <li class="flex items-center gap-2">
                                <x-untitledui-check class="h-4 w-4" />
                                <span>{{ $product->is_in_stock ? __('ui.in_stock_and_ready_to_ship') : __('ui.currently_out_of_stock') }}</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <x-untitledui-check class="h-4 w-4" />
                                <span>{{ __('ui.ships_from_our_lithuanian_warehouse') }}</span>
                            </li>
                        </ul>
                    </div>
                </aside>
            </div>

            <section class="mt-16">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-semibold text-gray-900">{{ __('ui.related_products') }}</h2>
                    <a href="{{ route('frontend.products.index', ['category' => optional($primaryCategory)->getKey()]) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                        {{ __('ui.browse_the_category') }}
                        <x-untitledui-arrow-narrow-right class="h-4 w-4" />
                    </a>
                </div>
                <div class="mt-8">
                    @include('frontend.products.partials.product-grid', ['products' => $relatedProducts, 'emptyMessage' => __('ui.no_related_products_are_available_yet')])
                </div>
            </section>
        </div>
    </div>
@endsection
