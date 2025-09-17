@extends('layouts.app')

@section('title', __('locations.title'))
@section('description', __('locations.subtitle'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
            {{ __('locations.title') }}
        </h1>
        <p class="text-gray-600 dark:text-gray-400">
            {{ __('locations.subtitle') }}
        </p>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
        <form method="GET" action="{{ route('locations.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('locations.filters.search') }}
                    </label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="{{ __('locations.filters.search_placeholder') }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                </div>

                <!-- Type Filter -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('locations.filters.by_type') }}
                    </label>
                    <select id="type" 
                            name="type" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">{{ __('locations.filters.all_types') }}</option>
                        @foreach($types as $type)
                            <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>
                                {{ __('locations.type_' . $type) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Country Filter -->
                <div>
                    <label for="country" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('locations.filters.by_country') }}
                    </label>
                    <select id="country" 
                            name="country" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">{{ __('locations.filters.all_countries') }}</option>
                        @foreach($countries as $country)
                            <option value="{{ $country }}" {{ request('country') === $country ? 'selected' : '' }}>
                                {{ $country }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- City Filter -->
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('locations.filters.by_city') }}
                    </label>
                    <select id="city" 
                            name="city" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">{{ __('locations.filters.all_cities') }}</option>
                        @foreach($cities as $city)
                            <option value="{{ $city }}" {{ request('city') === $city ? 'selected' : '' }}>
                                {{ $city }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Filter Actions -->
            <div class="flex items-center space-x-4">
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                    {{ __('locations.filters.apply_filters') }}
                </button>
                
                <a href="{{ route('locations.index') }}" 
                   class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                    {{ __('locations.filters.clear_filters') }}
                </a>
            </div>
        </form>
    </div>

    <!-- Locations Grid -->
    @if($locations->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($locations as $location)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 overflow-hidden">
                    <!-- Header -->
                    <div class="p-4 border-b border-gray-200 dark:border-gray-600">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                            {{ $location->translated_name }}
                        </h3>
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                @if($location->type === 'warehouse') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                @elseif($location->type === 'store') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                @elseif($location->type === 'office') bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200
                                @elseif($location->type === 'pickup_point') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 @endif">
                                {{ __('locations.type_' . $location->type) }}
                            </span>
                            @if($location->is_open_now)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    <i class="fas fa-circle text-green-500 mr-1"></i>
                                    {{ __('locations.status.open') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="p-4">
                        <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                            @if($location->address_line_1)
                                <div class="flex items-start">
                                    <i class="fas fa-map-marker-alt text-gray-400 w-4 mt-0.5 mr-2"></i>
                                    <span>{{ $location->address_line_1 }}</span>
                                </div>
                            @endif
                            
                            @if($location->city)
                                <div class="flex items-center">
                                    <i class="fas fa-city text-gray-400 w-4 mr-2"></i>
                                    <span>{{ $location->city }}{{ $location->country_code ? ', ' . $location->country_code : '' }}</span>
                                </div>
                            @endif
                            
                            @if($location->phone)
                                <div class="flex items-center">
                                    <i class="fas fa-phone text-gray-400 w-4 mr-2"></i>
                                    <a href="tel:{{ $location->phone }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                                        {{ $location->phone }}
                                    </a>
                                </div>
                            @endif
                            
                            @if($location->email)
                                <div class="flex items-center">
                                    <i class="fas fa-envelope text-gray-400 w-4 mr-2"></i>
                                    <a href="mailto:{{ $location->email }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                                        {{ $location->email }}
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="mt-4 flex space-x-2">
                            <a href="{{ route('locations.show', $location) }}" 
                               class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-3 rounded-md text-sm font-medium transition duration-200">
                                {{ __('locations.actions.view_details') }}
                            </a>
                            
                            @if($location->has_coordinates)
                                <a href="{{ $location->google_maps_url }}" 
                                   target="_blank"
                                   class="bg-green-600 hover:bg-green-700 text-white text-center py-2 px-3 rounded-md text-sm font-medium transition duration-200">
                                    <i class="fas fa-map"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $locations->appends(request()->query())->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <div class="text-gray-400 dark:text-gray-500 text-6xl mb-4">
                <i class="fas fa-map-marker-alt"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                {{ __('locations.messages.no_locations_found') }}
            </h3>
            <p class="text-gray-600 dark:text-gray-400">
                {{ __('locations.messages.try_different_filters') }}
            </p>
        </div>
    @endif
</div>
@endsection