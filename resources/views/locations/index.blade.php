@extends('layouts.app')

@section('title', __('locations.page_title'))
@section('description', __('locations.page_description'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ __('locations.page_title') }}</h1>
        <p class="text-gray-600">{{ __('locations.page_description') }}</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('locations.search_locations') }}
                </label>
                <input type="text" 
                       id="search" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="{{ __('locations.search_placeholder') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('locations.filter_by_type') }}
                </label>
                <select id="type" name="type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('locations.all_types') }}</option>
                    @foreach($types as $value => $label)
                        <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('locations.filter_by_city') }}
                </label>
                <select id="city" name="city" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('locations.all_cities') }}</option>
                    @foreach($cities as $city)
                        <option value="{{ $city }}" {{ request('city') === $city ? 'selected' : '' }}>
                            {{ $city }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    {{ __('locations.search_locations') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Results -->
    @if($locations->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($locations as $location)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-gray-900">{{ $location->display_name }}</h3>
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $location->type === 'warehouse' ? 'bg-blue-100 text-blue-800' : ($location->type === 'store' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ $location->type_label }}
                            </span>
                        </div>

                        @if($location->full_address)
                            <p class="text-gray-600 mb-2">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                {{ $location->full_address }}
                            </p>
                        @endif

                        @if($location->phone)
                            <p class="text-gray-600 mb-2">
                                <i class="fas fa-phone mr-2"></i>
                                <a href="tel:{{ $location->phone }}" class="hover:text-blue-600">{{ $location->phone }}</a>
                            </p>
                        @endif

                        @if($location->email)
                            <p class="text-gray-600 mb-4">
                                <i class="fas fa-envelope mr-2"></i>
                                <a href="mailto:{{ $location->email }}" class="hover:text-blue-600">{{ $location->email }}</a>
                            </p>
                        @endif

                        @if($location->translated_description)
                            <p class="text-gray-700 mb-4 line-clamp-3">{{ $location->translated_description }}</p>
                        @endif

                        <div class="flex space-x-2">
                            <a href="{{ route('locations.show', $location) }}" 
                               class="flex-1 bg-blue-600 text-white text-center px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                                {{ __('locations.view_details') }}
                            </a>
                            
                            @if($location->hasCoordinates())
                                <a href="{{ $location->google_maps_url }}" 
                                   target="_blank"
                                   class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors">
                                    <i class="fas fa-map-marker-alt"></i>
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
        <div class="text-center py-12">
            <i class="fas fa-map-marker-alt text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('locations.no_locations_found') }}</h3>
            <p class="text-gray-600">{{ __('locations.no_search_results') }}</p>
        </div>
    @endif
</div>
@endsection