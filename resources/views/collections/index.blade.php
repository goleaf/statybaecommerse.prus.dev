@extends('layouts.app')

@section('title', __('collections.title'))
@section('description', __('collections.description'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
            {{ __('collections.title') }}
        </h1>
        <p class="text-gray-600 dark:text-gray-300">
            {{ __('collections.description') }}
        </p>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('collections.filters.search') }}
                </label>
                <input type="text" 
                       id="search" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="{{ __('collections.placeholders.search') }}"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('collections.filters.type') }}
                </label>
                <select id="type" 
                        name="type"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">{{ __('collections.filters.all_types') }}</option>
                    <option value="manual" {{ request('type') === 'manual' ? 'selected' : '' }}>
                        {{ __('collections.types.manual') }}
                    </option>
                    <option value="automatic" {{ request('type') === 'automatic' ? 'selected' : '' }}>
                        {{ __('collections.types.automatic') }}
                    </option>
                </select>
            </div>

            <div>
                <label for="display_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('collections.filters.display_type') }}
                </label>
                <select id="display_type" 
                        name="display_type"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">{{ __('collections.filters.all_display_types') }}</option>
                    <option value="grid" {{ request('display_type') === 'grid' ? 'selected' : '' }}>
                        {{ __('collections.display_types.grid') }}
                    </option>
                    <option value="list" {{ request('display_type') === 'list' ? 'selected' : '' }}>
                        {{ __('collections.display_types.list') }}
                    </option>
                    <option value="carousel" {{ request('display_type') === 'carousel' ? 'selected' : '' }}>
                        {{ __('collections.display_types.carousel') }}
                    </option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" 
                        class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    {{ __('collections.actions.filter') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Collections Grid -->
    @if($collections->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($collections as $collection)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">
                    @if($collection->getImageUrl())
                        <div class="aspect-w-16 aspect-h-9">
                            <img src="{{ $collection->getImageUrl('md') }}" 
                                 alt="{{ $collection->getTranslatedName() }}"
                                 class="w-full h-48 object-cover">
                        </div>
                    @endif

                    <div class="p-6">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $collection->getTranslatedName() }}
                            </h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $collection->is_automatic ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' }}">
                                {{ $collection->is_automatic ? __('collections.types.automatic') : __('collections.types.manual') }}
                            </span>
                        </div>

                        @if($collection->getTranslatedDescription())
                            <p class="text-gray-600 dark:text-gray-300 text-sm mb-4 line-clamp-2">
                                {{ Str::limit($collection->getTranslatedDescription(), 100) }}
                            </p>
                        @endif

                        <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400 mb-4">
                            <span>
                                <i class="fas fa-box mr-1"></i>
                                {{ $collection->getProductsCountAttribute() }} {{ __('collections.products') }}
                            </span>
                            <span>
                                <i class="fas fa-eye mr-1"></i>
                                {{ __('collections.display_types.' . $collection->display_type) }}
                            </span>
                        </div>

                        <a href="{{ route('collections.show', $collection) }}" 
                           class="w-full bg-blue-600 text-white text-center px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors inline-block">
                            {{ __('collections.actions.view') }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $collections->appends(request()->query())->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <div class="text-gray-400 dark:text-gray-500 text-6xl mb-4">
                <i class="fas fa-layer-group"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                {{ __('collections.empty_states.no_collections') }}
            </h3>
            <p class="text-gray-500 dark:text-gray-400">
                {{ __('collections.empty_states.no_collections_description') }}
            </p>
        </div>
    @endif
</div>
@endsection