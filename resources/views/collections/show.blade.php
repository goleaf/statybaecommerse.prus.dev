@extends('layouts.app')

@section('title', $collection->meta_title ?: $collection->getTranslatedName())
@section('description', $collection->meta_description ?: $collection->getTranslatedDescription())

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                    <i class="fas fa-home mr-2"></i>
                    {{ __('common.home') }}
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                    <a href="{{ route('collections.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2 dark:text-gray-400 dark:hover:text-white">
                        {{ __('collections.title') }}
                    </a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">
                        {{ $collection->getTranslatedName() }}
                    </span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <!-- Collection Header -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-8">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                            {{ $collection->getTranslatedName() }}
                        </h1>
                        <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $collection->is_automatic ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' }}">
                                <i class="fas {{ $collection->is_automatic ? 'fa-cog' : 'fa-hand' }} mr-1"></i>
                                {{ $collection->is_automatic ? __('collections.types.automatic') : __('collections.types.manual') }}
                            </span>
                            <span>
                                <i class="fas fa-box mr-1"></i>
                                {{ $collection->getProductsCountAttribute() }} {{ __('collections.products') }}
                            </span>
                            <span>
                                <i class="fas fa-eye mr-1"></i>
                                {{ __('collections.display_types.' . $collection->display_type) }}
                            </span>
                        </div>
                    </div>
                </div>

                @if($collection->getTranslatedDescription())
                    <div class="prose dark:prose-invert max-w-none">
                        <p class="text-gray-700 dark:text-gray-300">
                            {{ $collection->getTranslatedDescription() }}
                        </p>
                    </div>
                @endif
            </div>

            <!-- Collection Image -->
            @if($collection->getImageUrl())
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-8">
                    <img src="{{ $collection->getImageUrl('lg') }}" 
                         alt="{{ $collection->getTranslatedName() }}"
                         class="w-full h-64 object-cover rounded-lg">
                </div>
            @endif

            <!-- Products Section -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                    {{ __('collections.products_in_collection') }}
                </h2>

                @if($collection->products->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($collection->products as $product)
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                                @if($product->getFirstMediaUrl('images'))
                                    <div class="aspect-w-16 aspect-h-9 mb-4">
                                        <img src="{{ $product->getFirstMediaUrl('images', 'image-sm') }}" 
                                             alt="{{ $product->getTranslatedName() }}"
                                             class="w-full h-32 object-cover rounded">
                                    </div>
                                @endif

                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                    {{ $product->getTranslatedName() }}
                                </h3>

                                @if($product->getTranslatedDescription())
                                    <p class="text-gray-600 dark:text-gray-300 text-sm mb-4 line-clamp-2">
                                        {{ Str::limit($product->getTranslatedDescription(), 80) }}
                                    </p>
                                @endif

                                <div class="flex items-center justify-between">
                                    @if($product->price)
                                        <span class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                            {{ number_format($product->price, 2) }} €
                                        </span>
                                    @endif
                                    <a href="{{ route('products.show', $product) }}" 
                                       class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
                                        {{ __('collections.actions.view_product') }}
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="text-gray-400 dark:text-gray-500 text-4xl mb-4">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                            {{ __('collections.empty_states.no_products') }}
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400">
                            {{ __('collections.empty_states.no_products_description') }}
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Collection Info -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('collections.collection_info') }}
                </h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">{{ __('collections.type') }}:</span>
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ $collection->is_automatic ? __('collections.types.automatic') : __('collections.types.manual') }}
                        </span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">{{ __('collections.products_count') }}:</span>
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ $collection->getProductsCountAttribute() }}
                        </span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">{{ __('collections.display_type') }}:</span>
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ __('collections.display_types.' . $collection->display_type) }}
                        </span>
                    </div>
                    
                    @if($collection->products_per_page)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-300">{{ __('collections.products_per_page') }}:</span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ $collection->products_per_page }}
                            </span>
                        </div>
                    @endif
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">{{ __('collections.show_filters') }}:</span>
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ $collection->show_filters ? __('common.yes') : __('common.no') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Related Collections -->
            @if($relatedCollections->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('collections.related_collections') }}
                    </h3>
                    
                    <div class="space-y-4">
                        @foreach($relatedCollections as $relatedCollection)
                            <div class="flex items-center space-x-3">
                                @if($relatedCollection->getImageUrl())
                                    <img src="{{ $relatedCollection->getImageUrl('sm') }}" 
                                         alt="{{ $relatedCollection->getTranslatedName() }}"
                                         class="w-12 h-12 object-cover rounded">
                                @else
                                    <div class="w-12 h-12 bg-gray-200 dark:bg-gray-600 rounded flex items-center justify-center">
                                        <i class="fas fa-layer-group text-gray-400"></i>
                                    </div>
                                @endif
                                
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ $relatedCollection->getTranslatedName() }}
                                    </h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $relatedCollection->getProductsCountAttribute() }} {{ __('collections.products') }}
                                    </p>
                                </div>
                                
                                <a href="{{ route('collections.show', $relatedCollection) }}" 
                                   class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection