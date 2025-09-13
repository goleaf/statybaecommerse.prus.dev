@extends('layouts.app')

@section('title', __('regions.title'))
@section('description', __('regions.subtitle'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
            {{ __('regions.title') }}
        </h1>
        <p class="text-gray-600 dark:text-gray-400">
            {{ __('regions.subtitle') }}
        </p>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
        <form method="GET" action="{{ route('regions.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('regions.filters.search') }}
                    </label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="{{ __('regions.filters.search_placeholder') }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                </div>

                <!-- Country Filter -->
                <div>
                    <label for="country" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('regions.filters.by_country') }}
                    </label>
                    <select id="country" 
                            name="country" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">{{ __('regions.filters.all_countries') }}</option>
                        @foreach($countries as $id => $name)
                            <option value="{{ $id }}" {{ request('country') == $id ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Level Filter -->
                <div>
                    <label for="level" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('regions.filters.by_level') }}
                    </label>
                    <select id="level" 
                            name="level" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">{{ __('regions.filters.all_levels') }}</option>
                        @foreach($levels as $level)
                            <option value="{{ $level }}" {{ request('level') == $level ? 'selected' : '' }}>
                                {{ __('regions.levels.' . $level) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Parent Filter -->
                <div>
                    <label for="parent" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('regions.filters.by_parent') }}
                    </label>
                    <select id="parent" 
                            name="parent" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">{{ __('regions.filters.all_parents') }}</option>
                        @foreach($parents as $id => $name)
                            <option value="{{ $id }}" {{ request('parent') == $id ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Filter Actions -->
            <div class="flex items-center space-x-4">
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                    {{ __('regions.filters.apply_filters') }}
                </button>
                
                <a href="{{ route('regions.index') }}" 
                   class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                    {{ __('regions.filters.clear_filters') }}
                </a>
            </div>
        </form>
    </div>

    <!-- Regions Grid -->
    @if($regions->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($regions as $region)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 overflow-hidden">
                    <!-- Header -->
                    <div class="p-4 border-b border-gray-200 dark:border-gray-600">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                            {{ $region->translated_name }}
                        </h3>
                        @if($region->parent)
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ __('regions.fields.parent') }}: {{ $region->parent->translated_name }}
                            </p>
                        @endif
                    </div>
                    
                    <div class="p-4">
                        <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                            <div class="flex justify-between">
                                <span>{{ __('regions.fields.code') }}:</span>
                                <span class="font-medium">{{ $region->code ?: 'N/A' }}</span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span>{{ __('regions.fields.level') }}:</span>
                                <span class="font-medium">{{ __('regions.levels.' . $region->level) }}</span>
                            </div>
                            
                            @if($region->country)
                                <div class="flex justify-between">
                                    <span>{{ __('regions.fields.country') }}:</span>
                                    <span class="font-medium">{{ $region->country->translated_name }}</span>
                                </div>
                            @endif
                            
                            <div class="flex justify-between">
                                <span>{{ __('regions.fields.cities') }}:</span>
                                <span class="font-medium">{{ $region->cities->count() }}</span>
                            </div>
                        </div>

                        <!-- Status Badges -->
                        <div class="flex flex-wrap gap-2 mt-3">
                            @if($region->is_default)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                    <i class="fas fa-star mr-1"></i>
                                    {{ __('regions.status.default') }}
                                </span>
                            @endif
                            
                            @if($region->children->count() > 0)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    <i class="fas fa-sitemap mr-1"></i>
                                    {{ $region->children->count() }} {{ __('regions.fields.children') }}
                                </span>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="mt-4 flex space-x-2">
                            <a href="{{ route('regions.show', $region) }}" 
                               class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-3 rounded-md text-sm font-medium transition duration-200">
                                {{ __('regions.actions.view_details') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $regions->appends(request()->query())->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <div class="text-gray-400 dark:text-gray-500 text-6xl mb-4">
                <i class="fas fa-map"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                {{ __('regions.messages.no_regions_found') }}
            </h3>
            <p class="text-gray-600 dark:text-gray-400">
                {{ __('regions.messages.try_different_filters') }}
            </p>
        </div>
    @endif
</div>
@endsection