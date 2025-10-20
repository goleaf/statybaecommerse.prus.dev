@extends('frontend.layouts.app')

@section('title', __('Brands'))
@section('description', __('Meet the manufacturers and labels powering the catalogue with professional-grade inventory.'))

@section('content')
    <div class="bg-white py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <header class="rounded-3xl bg-gradient-to-r from-purple-600 via-fuchsia-500 to-rose-500 p-10 text-white shadow-xl">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="max-w-2xl space-y-3">
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide">{{ __('Browse by brand') }}</span>
                        <h1 class="text-3xl font-semibold sm:text-4xl">{{ __('Featured catalogue brands') }}</h1>
                        <p class="text-sm text-white/80 sm:text-base">{{ __('Verified suppliers with active inventory and real-time product availability across multiple categories.') }}</p>
                    </div>
                    <div class="grid gap-3 text-sm text-white/80">
                        <div class="flex items-center justify-between rounded-3xl bg-white/10 px-5 py-3">
                            <span>{{ __('Brands highlighted today') }}</span>
                            <span class="text-lg font-semibold text-white">{{ number_format($highlightedBrands->count()) }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-3xl bg-white/10 px-5 py-3">
                            <span>{{ __('Featured products ready to ship') }}</span>
                            <span class="text-lg font-semibold text-white">{{ number_format($featuredProducts->count()) }}</span>
                        </div>
                    </div>
                </div>
            </header>

            <section class="mt-10 grid gap-8 lg:grid-cols-[1fr_320px]">
                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-semibold text-gray-900">{{ __('All brands') }}</h2>
                    <ul class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($brands as $brand)
                            <li class="group flex flex-col justify-between rounded-2xl border border-gray-200 bg-gray-50 p-5 transition hover:border-rose-500 hover:bg-white">
                                <div class="space-y-2">
                                    <a href="{{ route('frontend.brands.show', $brand) }}" class="text-lg font-semibold text-gray-900 group-hover:text-rose-600">{{ $brand->name }}</a>
                                    @if ($brand->description)
                                        <p class="text-sm text-gray-600">{!! str($brand->description)->stripTags()->limit(120) !!}</p>
                                    @endif
                                </div>
                                <div class="mt-4 flex items-center justify-between text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    <span>{{ __('Products') }}</span>
                                    <span class="rounded-full bg-gray-200 px-2 py-0.5 text-gray-700">{{ number_format($brand->published_products_count ?? 0) }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-8">
                        {{ $brands->links() }}
                    </div>
                </div>

                <aside class="space-y-6">
                    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('Trending brands') }}</h2>
                        <ul class="mt-4 space-y-3 text-sm text-gray-700">
                            @foreach ($highlightedBrands as $brand)
                                <li class="flex items-center justify-between">
                                    <a href="{{ route('frontend.brands.show', $brand) }}" class="hover:text-rose-600">{{ $brand->name }}</a>
                                    <span class="rounded-full bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-600">{{ number_format($brand->published_products_count ?? 0) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('Featured picks') }}</h2>
                        <ul class="mt-4 space-y-3 text-sm text-gray-700">
                            @forelse ($featuredProducts as $product)
                                <li class="flex items-center justify-between">
                                    <a href="{{ route('frontend.products.show', $product) }}" class="hover:text-rose-600">{{ $product->name }}</a>
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600">{{ $product->formatted_price }}</span>
                                </li>
                            @empty
                                <li class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-4 text-center text-xs text-gray-500">{{ __('Featured products will appear soon.') }}</li>
                            @endforelse
                        </ul>
                    </div>
                </aside>
            </section>
        </div>
    </div>
@endsection
