@extends('frontend.layouts.app')

@section('title', __('regions.all_regions'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ __('regions.all_regions') }}</h1>
        <p class="text-gray-600">{{ __('regions.regions_description') }}</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <form method="GET" action="{{ route('frontend.regions.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">{{ __('regions.search') }}</label>
                <input type="text" 
                       id="search" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="{{ __('regions.search_placeholder') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label for="country" class="block text-sm font-medium text-gray-700 mb-2">{{ __('regions.country') }}</label>
                <select id="country" name="country" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('regions.all_countries') }}</option>
                    @foreach($countries as $country)
                        <option value="{{ $country->id }}" {{ request('country') == $country->id ? 'selected' : '' }}>
                            {{ $country->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="zone" class="block text-sm font-medium text-gray-700 mb-2">{{ __('regions.zone') }}</label>
                <select id="zone" name="zone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('regions.all_zones') }}</option>
                    @foreach($zones as $zone)
                        <option value="{{ $zone->id }}" {{ request('zone') == $zone->id ? 'selected' : '' }}>
                            {{ $zone->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="level" class="block text-sm font-medium text-gray-700 mb-2">{{ __('regions.level') }}</label>
                <select id="level" name="level" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('regions.all_levels') }}</option>
                    <option value="0" {{ request('level') == '0' ? 'selected' : '' }}>Root</option>
                    <option value="1" {{ request('level') == '1' ? 'selected' : '' }}>State/Province</option>
                    <option value="2" {{ request('level') == '2' ? 'selected' : '' }}>County</option>
                    <option value="3" {{ request('level') == '3' ? 'selected' : '' }}>District</option>
                </select>
            </div>
            
            <div class="md:col-span-2 lg:col-span-4 flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    {{ __('regions.filter') }}
                </button>
                <a href="{{ route('frontend.regions.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    {{ __('regions.clear_filters') }}
                </a>
            </div>
        </form>
    </div>

    <!-- Regions Grid -->
    @if($regions->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($regions as $region)
                <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-gray-900">{{ $region->translated_name }}</h3>
                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                {{ $region->code }}
                            </span>
                        </div>
                        
                        @if($region->translated_description)
                            <p class="text-gray-600 mb-4">{{ Str::limit($region->translated_description, 100) }}</p>
                        @endif
                        
                        <div class="space-y-2 mb-4">
                            @if($region->country)
                                <div class="flex items-center text-sm text-gray-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $region->country->name }}
                                </div>
                            @endif
                            
                            @if($region->zone)
                                <div class="flex items-center text-sm text-gray-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    {{ $region->zone->name }}
                                </div>
                            @endif
                            
                            <div class="flex items-center text-sm text-gray-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                {{ __('regions.level') }}: {{ $region->level }}
                            </div>
                            
                            <div class="flex items-center text-sm text-gray-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                {{ $region->cities()->count() }} {{ __('regions.cities') }}
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <a href="{{ route('frontend.regions.show', $region) }}" 
                               class="text-blue-600 hover:text-blue-800 font-medium">
                                {{ __('regions.view_details') }}
                            </a>
                            
                            @if($region->children()->count() > 0)
                                <span class="text-sm text-gray-500">
                                    {{ $region->children()->count() }} {{ __('regions.sub_regions') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
        <div class="mt-8">
            {{ $regions->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('regions.no_regions_found') }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ __('regions.try_different_filters') }}</p>
        </div>
    @endif
</div>
@endsection

