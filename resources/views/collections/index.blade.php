@extends('layouts.app')

@section('title', __('collections.title'))
@section('description', __('collections.meta_description'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ __('collections.title') }}</h1>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">{{ __('collections.description') }}</p>
    </div>

    <!-- Search and Filters -->
    <div class="mb-8">
        <form method="GET" action="{{ route('collections.index') }}" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="{{ __('collections.search_placeholder') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="flex gap-2">
                <select name="type" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">{{ __('collections.filters.all_types') }}</option>
                    <option value="manual" {{ request('type') === 'manual' ? 'selected' : '' }}>{{ __('collections.filters.manual') }}</option>
                    <option value="automatic" {{ request('type') === 'automatic' ? 'selected' : '' }}>{{ __('collections.filters.automatic') }}</option>
                </select>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    {{ __('collections.search') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Collections Grid -->
    @if($collections->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($collections as $collection)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <!-- Collection Image -->
                    <div class="aspect-w-16 aspect-h-9 bg-gray-200">
                        @if($collection->image)
                            <img src="{{ $collection->getImageUrl('md') }}" 
                                 alt="{{ $collection->name }}"
                                 class="w-full h-48 object-cover">
                        @else
                            <div class="w-full h-48 bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                                <svg class="w-16 h-16 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"/>
                                </svg>
                            </div>
                        @endif
                    </div>

                    <!-- Collection Content -->
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-xl font-semibold text-gray-900 line-clamp-1">
                                {{ $collection->name }}
                            </h3>
                            @if($collection->is_automatic)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ __('collections.automatic') }}
                                </span>
                            @endif
                        </div>

                        @if($collection->description)
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                                {!! Str::limit(strip_tags($collection->description), 100) !!}
                            </p>
                        @endif

                        <div class="flex items-center justify-between">
                            <div class="flex items-center text-sm text-gray-500">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                                </svg>
                                {{ $collection->products_count }} {{ __('collections.products') }}
                            </div>
                            <a href="{{ route('collections.show', $collection) }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                {{ __('collections.view_collection') }}
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-12">
            {{ $collections->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <svg class="w-24 h-24 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('collections.no_collections_found') }}</h3>
            <p class="text-gray-500">{{ __('collections.try_different_search') }}</p>
        </div>
    @endif
</div>
@endsection
