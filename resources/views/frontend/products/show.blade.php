@extends('frontend.layouts.app')

@section('title', $product->name)

@section('content')
    <div class="bg-gray-50 py-12">
        <div class="mx-auto max-w-6xl space-y-12 px-6">
            <nav class="text-sm text-gray-500">
                <ol class="flex flex-wrap items-center gap-2">
                    <li>
                        <a href="{{ route('home') }}" class="hover:text-indigo-600">{{ __('Home') }}</a>
                        <span aria-hidden="true">/</span>
                    </li>
                    <li>
                        <a href="{{ route('frontend.products.index') }}" class="hover:text-indigo-600">{{ __('Products') }}</a>
                        <span aria-hidden="true">/</span>
                    </li>
                    <li class="text-gray-700">{{ $product->name }}</li>
                </ol>
            </nav>

            <div class="grid gap-10 lg:grid-cols-2">
                <div class="space-y-4">
                    @php
                        $primaryImage = $product->getFirstMediaUrl('images', 'large') ?: $product->getFirstMediaUrl('images') ?: asset('images/placeholder-product.png');
                    @endphp
                    <div class="overflow-hidden rounded-2xl bg-white shadow">
                        <img src="{{ $primaryImage }}" alt="{{ $product->name }}" class="h-full w-full object-cover" loading="lazy">
                    </div>
                    @if($product->media->count() > 1)
                        <div class="grid grid-cols-4 gap-4">
                            @foreach($product->media->take(4) as $media)
                                <img src="{{ $media->getUrl('thumb') }}" alt="{{ $product->name }}" class="h-24 w-full rounded-xl object-cover" loading="lazy">
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="space-y-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $product->name }}</h1>
                        @if($product->brand)
                            <p class="mt-1 text-sm text-gray-600">{{ __('Brand:') }} <a href="{{ route('frontend.brands.show', $product->brand) }}" class="font-semibold text-indigo-600 hover:text-indigo-500">{{ $product->brand->name }}</a></p>
                        @endif
                        @if($product->categories->isNotEmpty())
                            <p class="mt-1 text-xs text-gray-500">
                                {{ __('Categories:') }}
                                @foreach($product->categories as $category)
                                    <a href="{{ route('frontend.categories.show', $category) }}" class="mr-2 text-indigo-600 hover:text-indigo-500">{{ $category->name }}</a>
                                @endforeach
                            </p>
                        @endif
                    </div>

                    @php
                        $priceRecord = $product->prices->first();
                        $priceAmount = $priceRecord->amount ?? $product->price;
                        $currencySymbol = $priceRecord?->currency?->symbol ?? $priceRecord?->currency?->code ?? '€';
                        $compareAmount = $priceRecord?->compare_amount ?? $product->compare_price;
                        $saleAmount = $product->sale_price ?? null;
                    @endphp

                    <div class="rounded-xl bg-white p-6 shadow">
                        <div class="flex items-center gap-4">
                            @if($priceAmount !== null)
                                <span class="text-3xl font-bold text-gray-900">{{ $currencySymbol }}{{ number_format((float) $priceAmount, 2) }}</span>
                            @endif
                            <div class="flex flex-col text-sm text-gray-500">
                                @if($compareAmount && $priceAmount !== null && (float) $compareAmount > (float) $priceAmount)
                                    <span class="line-through">{{ $currencySymbol }}{{ number_format((float) $compareAmount, 2) }}</span>
                                @endif
                                @if($saleAmount && (float) $saleAmount < (float) $priceAmount)
                                    <span class="text-sm font-semibold text-emerald-600">{{ __('Sale price:') }} {{ $currencySymbol }}{{ number_format((float) $saleAmount, 2) }}</span>
                                @endif
                            </div>
                        </div>
                        <p class="mt-4 text-sm text-gray-600">{{ __('SKU:') }} {{ $product->sku }}</p>
                        <p class="text-sm text-gray-600">{{ __('Availability:') }}
                            <span class="font-semibold text-gray-900">
                                {{ $product->is_in_stock ? __('In stock') : __('Out of stock') }}
                            </span>
                        </p>
                    </div>

                    <div class="prose max-w-none rounded-xl bg-white p-6 shadow">
                        {!! $product->description ?? '<p class="text-sm text-gray-600">'.__('Product description will be updated soon.').'</p>' !!}
                    </div>
                </div>
            </div>

            @if($relatedProducts->count() > 0)
                <section class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('You may also like') }}</h2>
                        <a href="{{ route('frontend.products.index', ['brand' => $product->brand?->slug]) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">
                            {{ __('View more') }}
                        </a>
                    </div>
                    @include('frontend.products.partials.grid', ['products' => $relatedProducts, 'emptyMessage' => __('More recommendations will appear soon.')])
                </section>
            @endif
        </div>
    </div>
@endsection
