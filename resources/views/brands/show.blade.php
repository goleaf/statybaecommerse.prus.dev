@extends('components.layouts.base')

@section('title', $seoTitle)
@section('description', $seoDescription)

@section('meta')
    <x-meta
        :title="$seoTitle"
        :description="$seoDescription"
        canonical="{{ url()->current() }}" />
@endsection

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    {{-- Page Header --}}
    <x-shared.page-header
        :title="$brand->getTranslatedName()"
        :description="$brand->getTranslatedDescription()"
        icon="heroicon-o-tag"
        :breadcrumbs="[
            ['title' => __('shared.home'), 'url' => route('localized.home', ['locale' => app()->getLocale()])],
            ['title' => __('shared.brands'), 'url' => route('localized.brands.index', ['locale' => app()->getLocale()])],
            ['title' => $brand->getTranslatedName()]
        ]"
    />

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        {{-- Brand Information --}}
        <div class="mb-12">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-8">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex-1">
                            @if($brand->getFirstMediaUrl('logo'))
                                <div class="mb-6">
                                    <img 
                                        src="{{ $brand->getFirstMediaUrl('logo') }}" 
                                        alt="{{ $brand->getTranslatedName() }}"
                                        class="h-24 w-auto object-contain"
                                    />
                                </div>
                            @endif
                            
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                                {{ $brand->getTranslatedName() }}
                            </h1>
                            
                            @if($brand->getTranslatedDescription())
                                <div class="prose max-w-none text-gray-600 dark:text-gray-300 mb-6">
                                    {!! nl2br(e($brand->getTranslatedDescription())) !!}
                                </div>
                            @endif
                            
                            <div class="flex flex-wrap gap-4">
                                @if($brand->website)
                                    <a href="{{ $brand->website }}" 
                                       target="_blank" 
                                       rel="noopener noreferrer"
                                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                        {{ __('Visit Website') }}
                                    </a>
                                @endif
                                
                                <x-shared.badge variant="primary" size="lg">
                                    {{ $products->count() }} {{ trans_choice('products', $products->count()) }}
                                </x-shared.badge>
                            </div>
                        </div>
                        
                        @if($brand->getFirstMediaUrl('banner'))
                            <div class="mt-8 lg:mt-0 lg:ml-8">
                                <img 
                                    src="{{ $brand->getFirstMediaUrl('banner') }}" 
                                    alt="{{ $brand->getTranslatedName() }}"
                                    class="h-48 w-full lg:h-64 lg:w-80 object-cover rounded-lg"
                                />
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Products Section --}}
        <div class="mb-12">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ __('Products by :brand', ['brand' => $brand->getTranslatedName()]) }}
                </h2>
                
                @if($products->count() > 12)
                    <a href="{{ route('localized.products.index', ['locale' => app()->getLocale(), 'brand' => $brand->getTranslatedSlug()]) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        {{ __('View All Products') }}
                        <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                @endif
            </div>

            @if($products->count() > 0)
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($products as $product)
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow duration-200">
                            <div class="aspect-w-16 aspect-h-9 bg-gray-100 dark:bg-gray-700">
                                @if($product->getFirstMediaUrl('images'))
                                    <img src="{{ $product->getFirstMediaUrl('images') }}" 
                                         alt="{{ $product->getTranslatedName() ?? $product->name }}"
                                         class="w-full h-48 object-cover object-center">
                                @else
                                    <div class="flex items-center justify-center h-48 bg-gray-100 dark:bg-gray-700">
                                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                    <a href="{{ route('product.show', $product->getTranslatedSlug() ?? $product->slug) }}" 
                                       class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200">
                                        {{ $product->getTranslatedName() ?? $product->name }}
                                    </a>
                                </h3>
                                
                                @if($product->getTranslatedSummary() ?? $product->summary)
                                    <p class="text-gray-600 dark:text-gray-300 text-sm mb-4 line-clamp-2">
                                        {{ $product->getTranslatedSummary() ?? $product->summary }}
                                    </p>
                                @endif
                                
                                <div class="flex items-center justify-between">
                                    <div class="text-lg font-bold text-gray-900 dark:text-white">
                                        €{{ number_format($product->price, 2) }}
                                    </div>
                                    
                                    <a href="{{ route('product.show', $product->getTranslatedSlug() ?? $product->slug) }}" 
                                       class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                        {{ __('View Details') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('No products found') }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('No products are available for this brand yet.') }}</p>
                    <div class="mt-6">
                        <a href="{{ route('localized.brands.index', ['locale' => app()->getLocale()]) }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ __('Browse Other Brands') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>

        {{-- Related Brands --}}
        @php
            $relatedBrands = \App\Models\Brand::query()
                ->where('is_enabled', true)
                ->where('id', '!=', $brand->id)
                ->withCount('products')
                ->having('products_count', '>', 0)
                ->inRandomOrder()
                ->limit(4)
                ->get();
        @endphp

        @if($relatedBrands->count() > 0)
            <div class="mb-12">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8">
                    {{ __('Other Brands') }}
                </h2>
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($relatedBrands as $relatedBrand)
                        <x-shared.card hover="true" class="group">
                            <div class="aspect-w-16 aspect-h-9 overflow-hidden rounded-t-lg">
                                @if($relatedBrand->getFirstMediaUrl('logo'))
                                    <img 
                                        src="{{ $relatedBrand->getFirstMediaUrl('logo') }}" 
                                        alt="{{ $relatedBrand->getTranslatedName() }}"
                                        loading="lazy"
                                        class="h-32 w-full object-contain object-center transition-transform duration-300 group-hover:scale-105 p-4"
                                    />
                                @else
                                    <div class="flex h-32 items-center justify-center bg-gray-100 dark:bg-gray-700" aria-hidden="true">
                                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-200 p-4">
                                <a href="{{ route('localized.brands.show', ['locale' => app()->getLocale(), 'slug' => $relatedBrand->getTranslatedSlug()]) }}" class="stretched-link">
                                    {{ $relatedBrand->getTranslatedName() }}
                                </a>
                            </h3>
                            
                            <x-slot name="footer">
                                <div class="flex items-center justify-between p-4 pt-0">
                                    <x-shared.badge variant="primary" size="sm">
                                        {{ $relatedBrand->products_count }} {{ trans_choice('products', $relatedBrand->products_count) }}
                                    </x-shared.badge>
                                    
                                    <svg class="h-5 w-5 text-gray-400 group-hover:text-blue-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </x-slot>
                        </x-shared.card>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
