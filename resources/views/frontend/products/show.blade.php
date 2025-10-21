@extends('frontend.layouts.app')

@section('title', $product->name)

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

            <div class="grid gap-10 lg:grid-cols-2 lg:items-start">
                <div class="space-y-6">
                    @php
                        $image = $product->getFirstMediaUrl('images', 'image-lg') ?: $product->getFirstMediaUrl('images');
                    @endphp
                    <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
                        @if($image)
                            <img src="{{ $image }}" alt="{{ $product->name }}" class="w-full object-cover" loading="lazy">
                        @else
                            <div class="flex h-96 items-center justify-center bg-gray-100 text-gray-400">
                                <x-untitledui-image class="h-12 w-12" />
                            </div>
                        @endif
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="space-y-2">
                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                            <x-untitledui-check-badge class="h-3.5 w-3.5" />
                            {{ __('In stock catalogue item') }}
                        </span>
                        <h1 class="text-3xl font-bold text-gray-900 sm:text-4xl">{{ $product->name }}</h1>
                        @if($product->brand)
                            <div class="text-sm text-gray-500">
                                {{ __('By :brand', ['brand' => $product->brand->name]) }}
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center gap-4">
                        @php
                            $effectivePrice = $product->sale_price && $product->sale_price < $product->price ? $product->sale_price : $product->price;
                        @endphp
                        <p class="text-3xl font-semibold text-gray-900">
                            {{ \Illuminate\Support\Number::currency((float) $effectivePrice, function_exists('current_currency') ? current_currency() : 'EUR', app()->getLocale()) }}
                        </p>
                        @if($product->sale_price && $product->sale_price < $product->price)
                            <p class="text-lg text-gray-400 line-through">
                                {{ \Illuminate\Support\Number::currency((float) $product->price, function_exists('current_currency') ? current_currency() : 'EUR', app()->getLocale()) }}
                            </p>
                        @endif
                    </div>

                    <div class="space-y-3 text-gray-600">
                        {!! $product->description ?? '<p>'.e($product->short_description ?? __('Product description coming soon.')) .'</p>' !!}
                    </div>

                    @if($product->categories->isNotEmpty())
                        <div class="space-y-2">
                            <h2 class="text-sm font-semibold text-gray-900">{{ __('Categories') }}</h2>
                            <div class="flex flex-wrap gap-2">
                                @foreach($product->categories as $category)
                                    <a href="{{ route('frontend.categories.show', $category) }}" class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                        {{ $category->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($product->brand)
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <h2 class="text-sm font-semibold text-gray-900">{{ __('About the brand') }}</h2>
                            <p class="mt-2 text-sm text-gray-600">
                                {{ $product->brand->description ?? __('Trusted construction equipment supplier across the Baltics.') }}
                            </p>
                            @if($product->brand->website)
                                <a href="{{ $product->brand->website }}" class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700">
                                    <x-untitledui-globe class="h-4 w-4" />
                                    {{ __('Visit brand site') }}
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <section class="space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-semibold text-gray-900">{{ __('Related products') }}</h2>
                    <a href="{{ route('frontend.products.index', ['filter' => 'featured']) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">
                        {{ __('View all') }}
                    </a>
                </div>
                @include('frontend.catalogue.product-grid', [
                    'products' => $relatedProducts,
                    'columns' => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
                    'emptyMessage' => __('More related items will appear as the catalogue grows.'),
                ])
            </section>
        </div>
    </div>
@endsection

