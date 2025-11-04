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
<div class="min-h-screen bg-sage">
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
        background="bg-dark"
        :breadcrumbLinkClass="'text-ash hover:text-ash'"
        :breadcrumbCurrentClass="'text-white font-semibold'"
        :breadcrumbSeparatorClass="'text-ash/70'"
        :centered="false"
    >
        <x-slot name="actions">
            <div class="flex flex-wrap items-center gap-3">
                @if($brand->website)
                    <a href="{{ $brand->website }}" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="inline-flex items-center justify-center h-10 px-5 rounded-md text-sm font-semibold border border-sage/40 bg-sage text-dark hover:bg-sage/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sage">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        {{ __('frontend/brands.show.visit_website') }}
                    </a>
                @endif
                <span class="inline-flex items-center justify-center h-10 px-5 rounded-md text-sm font-semibold border border-ash/40 text-white hover:bg-ash/10">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18M9 7h6m-3 0v14" />
                    </svg>
                    {{ trans_choice('frontend/brands.products', $products->count(), ['count' => $products->count()]) }}
                </span>
            </div>
        </x-slot>
    </x-shared.page-header>

    @if($brand->getFirstMediaUrl('banner'))
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 pb-8">
            <img 
                src="{{ $brand->getFirstMediaUrl('banner') }}" 
                alt="{{ $brand->getTranslatedName() }}"
                class="h-48 w-full lg:h-64 object-cover rounded-lg border border-ash/20"
            />
        </div>
    @endif

    <div class="container mx-auto px-4 py-12 sm:px-6 lg:px-8">

        {{-- Products Section --}}
        <div class="mb-12">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-bold text-dark">
                    {{ __('frontend/brands.show.products_by_brand', ['brand' => $brand->getTranslatedName()]) }}
                </h2>
                
                @if($products->count() > 12)
                    <a href="{{ route('localized.products.index', ['locale' => app()->getLocale(), 'brand' => $brand->getTranslatedSlug()]) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        {{ __('frontend/brands.show.view_all_products') }}
                        <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                @endif
            </div>

            @if($products->count() > 0)
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($products as $product)
                        @include('livewire.home.partials.product-card', [
                            'product' => $product,
                            'preset' => 'featured',
                        ])
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('frontend/brands.show.empty_title') }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('frontend/brands.show.empty_desc') }}</p>
                    <div class="mt-6">
                        <a href="{{ route('localized.brands.index', ['locale' => app()->getLocale()]) }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ __('frontend/brands.show.browse_other_brands') }}
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
                <h2 class="text-2xl font-bold text-gray-900 dark:text-dark mb-8">
                    {{ __('frontend/brands.show.other_brands') }}
                </h2>
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($relatedBrands as $relatedBrand)
                        <x-shared.card hover="true" class="group !bg-white dark:!bg-white text-dark border border-ash/30 hover:border-ash/60 shadow-sm hover:shadow-md">
                            <div class="aspect-w-16 aspect-h-9 overflow-hidden rounded-t-lg">
                                @if($relatedBrand->getFirstMediaUrl('logo'))
                                    <img 
                                        src="{{ $relatedBrand->getFirstMediaUrl('logo') }}" 
                                        alt="{{ $relatedBrand->getTranslatedName() }}"
                                        loading="lazy"
                                        class="h-32 w-full object-contain object-center transition-transform duration-300 group-hover:scale-105 p-4"
                                    />
                                @else
                                    <div class="flex h-32 items-center justify-center bg-ash/10" aria-hidden="true">
                                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            
                            <h3 class="text-lg font-semibold text-dark group-hover:text-stone transition-colors duration-200 p-4">
                                <a href="{{ route('localized.brands.show', ['locale' => app()->getLocale(), 'slug' => $relatedBrand->getTranslatedSlug()]) }}" class="stretched-link">
                                    {{ $relatedBrand->getTranslatedName() }}
                                </a>
                            </h3>
                            
                            <x-slot name="footer">
                                <div class="flex items-center justify-between p-4 pt-0">
                                    <x-shared.badge variant="primary" size="sm">
                                        {{ trans_choice('frontend/brands.products', $relatedBrand->products_count, ['count' => $relatedBrand->products_count]) }}
                                    </x-shared.badge>
                                    
                                    <svg class="h-5 w-5 text-stone group-hover:text-dark transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
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
