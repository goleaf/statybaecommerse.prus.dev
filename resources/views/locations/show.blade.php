@extends('components.layouts.base')

@section('title', $location->translated_name . ' - ' . __('locations.title'))
@section('description', $location->translated_description ?: __('locations.subtitle'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-6">
        <ol class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
            <li>
                <a href="{{ route('locations.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                    {{ __('locations.title') }}
                </a>
            </li>
            <li class="flex items-center">
                <i class="fas fa-chevron-right mx-2"></i>
                <span class="text-gray-900 dark:text-white">{{ $location->translated_name }}</span>
            </li>
        </ol>
    </nav>

    <!-- Location Header -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
        <div class="flex flex-col md:flex-row items-start md:items-center space-y-4 md:space-y-0 md:space-x-6">
            <!-- Icon -->
            <div class="flex-shrink-0">
                <div class="h-24 w-24 rounded-full flex items-center justify-center
                    @if($location->type === 'warehouse') bg-blue-100 dark:bg-blue-900
                    @elseif($location->type === 'store') bg-green-100 dark:bg-green-900
                    @elseif($location->type === 'office') bg-purple-100 dark:bg-purple-900
                    @elseif($location->type === 'pickup_point') bg-yellow-100 dark:bg-yellow-900
                    @else bg-gray-100 dark:bg-gray-700 @endif">
                    <i class="fas 
                        @if($location->type === 'warehouse') fa-warehouse
                        @elseif($location->type === 'store') fa-store
                        @elseif($location->type === 'office') fa-building
                        @elseif($location->type === 'pickup_point') fa-truck
                        @else fa-map-marker-alt @endif
                        text-3xl
                        @if($location->type === 'warehouse') text-blue-600 dark:text-blue-400
                        @elseif($location->type === 'store') text-green-600 dark:text-green-400
                        @elseif($location->type === 'office') text-purple-600 dark:text-purple-400
                        @elseif($location->type === 'pickup_point') text-yellow-600 dark:text-yellow-400
                        @else text-gray-600 dark:text-gray-400 @endif"></i>
                </div>
            </div>

            <!-- Location Info -->
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                    {{ $location->translated_name }}
                </h1>
                
                @if($location->translated_description)
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        {{ $location->translated_description }}
                    </p>
                @endif

                <!-- Quick Info -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $location->code }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('locations.fields.code') }}</div>
                    </div>
                    
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ __('locations.type_' . $location->type) }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('locations.fields.type') }}</div>
                    </div>
                    
                    @if($location->city)
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $location->city }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('locations.fields.city') }}</div>
                        </div>
                    @endif
                    
                    @if($location->is_open_now)
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                                <i class="fas fa-circle text-green-500"></i>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('locations.status.open') }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Location Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('locations.details.basic_info') }}
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('locations.fields.name') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $location->translated_name }}</p>
                    </div>
                    
                    @if($location->code)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('locations.fields.code') }}
                            </label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $location->code }}</p>
                        </div>
                    @endif
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('locations.fields.type') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ __('locations.type_' . $location->type) }}</p>
                    </div>
                    
                    @if($location->sort_order)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('locations.fields.sort_order') }}
                            </label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $location->sort_order }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Address Information -->
            @if($location->address_line_1 || $location->city)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('locations.details.address_info') }}
                    </h2>
                    
                    <div class="space-y-3">
                        @if($location->full_address)
                            <div class="flex items-start">
                                <i class="fas fa-map-marker-alt text-gray-400 w-5 mt-0.5 mr-3"></i>
                                <div>
                                    <p class="text-gray-900 dark:text-white font-medium">{{ $location->full_address }}</p>
                                    @if($location->country_code)
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $location->country_code }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif
                        
                        @if($location->has_coordinates)
                            <div class="flex items-center">
                                <i class="fas fa-globe text-gray-400 w-5 mr-3"></i>
                                <div>
                                    <p class="text-sm text-gray-900 dark:text-white">{{ $location->coordinates }}</p>
                                    <a href="{{ $location->google_maps_url }}" target="_blank" 
                                       class="text-blue-600 dark:text-blue-400 hover:underline text-sm">
                                        {{ __('locations.actions.show_on_map') }}
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Contact Information -->
            @if($location->phone || $location->email)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('locations.details.contact_info') }}
                    </h2>
                    
                    <div class="space-y-3">
                        @if($location->phone)
                            <div class="flex items-center">
                                <i class="fas fa-phone text-gray-400 w-5 mr-3"></i>
                                <a href="tel:{{ $location->phone }}" 
                                   class="text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400">
                                    {{ $location->phone }}
                                </a>
                            </div>
                        @endif
                        
                        @if($location->email)
                            <div class="flex items-center">
                                <i class="fas fa-envelope text-gray-400 w-5 mr-3"></i>
                                <a href="mailto:{{ $location->email }}" 
                                   class="text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400">
                                    {{ $location->email }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Opening Hours -->
            @if($location->has_opening_hours)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('locations.details.opening_hours_info') }}
                    </h2>
                    
                    <div class="space-y-2">
                        @foreach($location->getFormattedOpeningHours() as $day => $hours)
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ $hours['day'] }}
                                </span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    @if($hours['is_closed'])
                                        {{ __('locations.status.closed') }}
                                    @elseif($hours['open_time'] && $hours['close_time'])
                                        {{ $hours['open_time'] }} - {{ $hours['close_time'] }}
                                    @else
                                        {{ __('locations.status.closed') }}
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Description -->
            @if($location->translated_description)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('locations.fields.description') }}
                    </h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {{ $location->translated_description }}
                    </p>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Business Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('locations.details.business_info') }}
                </h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('locations.fields.type') }}:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ __('locations.type_' . $location->type) }}
                        </span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('locations.fields.is_enabled') }}:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            @if($location->is_enabled)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    <i class="fas fa-check mr-1"></i>
                                    {{ __('locations.fields.yes') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                    <i class="fas fa-times mr-1"></i>
                                    {{ __('locations.fields.no') }}
                                </span>
                            @endif
                        </span>
                    </div>
                    
                    @if($location->has_coordinates)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('locations.fields.coordinates') }}:</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                <i class="fas fa-map-marker-alt text-green-500 mr-1"></i>
                                {{ __('locations.fields.yes') }}
                            </span>
                        </div>
                    @endif
                    
                    @if($location->has_opening_hours)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('locations.fields.opening_hours') }}:</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                <i class="fas fa-clock text-blue-500 mr-1"></i>
                                {{ __('locations.fields.yes') }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('locations.details.actions') }}
                </h3>
                
                <div class="space-y-3">
                    <a href="{{ route('locations.index') }}" 
                       class="w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-4 rounded-md text-sm font-medium transition duration-200 block">
                        {{ __('locations.actions.back_to_list') }}
                    </a>
                    
                    @if($location->has_coordinates)
                        <a href="{{ $location->google_maps_url }}" 
                           target="_blank"
                           class="w-full bg-green-600 hover:bg-green-700 text-white text-center py-2 px-4 rounded-md text-sm font-medium transition duration-200 block">
                            {{ __('locations.actions.show_on_map') }}
                        </a>
                    @endif
                    
                    @if($location->phone)
                        <a href="tel:{{ $location->phone }}" 
                           class="w-full bg-purple-600 hover:bg-purple-700 text-white text-center py-2 px-4 rounded-md text-sm font-medium transition duration-200 block">
                            {{ __('locations.actions.contact_location') }}
                        </a>
                    @endif
                </div>
            </div>

            <!-- Related Locations -->
            @if($relatedLocations->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('locations.details.related_locations') }}
                    </h3>
                    
                    <div class="space-y-2">
                        @foreach($relatedLocations->take(5) as $relatedLocation)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ $relatedLocation->translated_name }}
                                </span>
                                <a href="{{ route('locations.show', $relatedLocation) }}" 
                                   class="text-blue-600 dark:text-blue-400 hover:underline text-xs">
                                    {{ __('locations.actions.view') }}
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