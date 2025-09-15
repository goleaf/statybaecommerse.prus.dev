@extends('layouts.app')

@section('title', __('product_variants.showcase.title'))

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">
                {{ __('product_variants.showcase.title') }}
            </h1>
            <p class="text-lg text-gray-600">
                {{ __('product_variants.showcase.subtitle') }}
            </p>
        </div>

        <!-- Product Selection -->
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                {{ __('product_variants.showcase.select_product') }}
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($products as $product)
                    <div class="border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer"
                         wire:click="selectProduct({{ $product->id }})"
                         wire:key="product-{{ $product->id }}">
                        @if($product->main_image)
                            <img src="{{ $product->main_image }}" 
                                 alt="{{ $product->name }}"
                                 class="w-full h-48 object-cover rounded-lg mb-3">
                        @endif
                        
                        <h3 class="font-semibold text-gray-900 mb-2">{{ $product->name }}</h3>
                        <p class="text-sm text-gray-600 mb-2">{{ $product->short_description }}</p>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-lg font-bold text-blue-600">
                                €{{ number_format($product->price, 2) }}
                            </span>
                            <span class="text-sm text-gray-500">
                                {{ $product->variants()->count() }} {{ __('product_variants.showcase.variants_count') }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @if($selectedProduct)
            <!-- Product Details -->
            <div class="bg-white rounded-lg shadow-sm border p-6 mb-8">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $selectedProduct->name }}</h2>
                        <p class="text-gray-600 mb-4">{{ $selectedProduct->description }}</p>
                        
                        <div class="flex items-center gap-4">
                            <span class="text-2xl font-bold text-blue-600">
                                €{{ number_format($selectedProduct->price, 2) }}
                            </span>
                            @if($selectedProduct->brand)
                                <span class="text-sm text-gray-500">
                                    {{ __('product_variants.showcase.brand') }}: {{ $selectedProduct->brand->name }}
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    @if($selectedProduct->main_image)
                        <img src="{{ $selectedProduct->main_image }}" 
                             alt="{{ $selectedProduct->name }}"
                             class="w-32 h-32 object-cover rounded-lg">
                    @endif
                </div>

                <!-- Variant Selector -->
                <livewire:product-variant-selector :product="$selectedProduct" />
            </div>

            <!-- Variant Comparison -->
            <div class="bg-white rounded-lg shadow-sm border p-6 mb-8">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    {{ __('product_variants.showcase.comparison_title') }}
                </h3>
                <livewire:components.variant-comparison-table :product="$selectedProduct" />
            </div>

            <!-- Analytics Dashboard -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    {{ __('product_variants.showcase.analytics_title') }}
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Total Variants -->
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-blue-600">{{ __('product_variants.showcase.total_variants') }}</p>
                                <p class="text-2xl font-bold text-blue-900">{{ $selectedProduct->variants()->count() }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- In Stock -->
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-green-600">{{ __('product_variants.showcase.in_stock') }}</p>
                                <p class="text-2xl font-bold text-green-900">{{ $selectedProduct->variants()->inStock()->count() }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Low Stock -->
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-yellow-600">{{ __('product_variants.showcase.low_stock') }}</p>
                                <p class="text-2xl font-bold text-yellow-900">{{ $selectedProduct->variants()->lowStock()->count() }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Out of Stock -->
                    <div class="bg-red-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-red-600">{{ __('product_variants.showcase.out_of_stock') }}</p>
                                <p class="text-2xl font-bold text-red-900">{{ $selectedProduct->variants()->outOfStock()->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
