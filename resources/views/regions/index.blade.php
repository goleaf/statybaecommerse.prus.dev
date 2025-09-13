@extends('layouts.app')

@section('title', __('regions.title'))
@section('description', __('regions.subtitle'))

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('regions.title') ?: 'Regions' }}</h1>
                    <p class="mt-2 text-gray-600">{{ __('regions.subtitle') ?: 'Browse and explore regions' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <form method="GET" action="{{ route('regions.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">{{ __('regions.filters.search') }}</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" 
                           placeholder="{{ __('regions.filters.search_placeholder') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Country -->
                <div>
                    <label for="country_id" class="block text-sm font-medium text-gray-700">{{ __('regions.filters.country') }}</label>
                    <select name="country_id" id="country_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('regions.filters.all_countries') }}</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}" {{ request('country_id') == $country->id ? 'selected' : '' }}>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Zone -->
                <div>
                    <label for="zone_id" class="block text-sm font-medium text-gray-700">{{ __('regions.filters.zone') }}</label>
                    <select name="zone_id" id="zone_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('regions.filters.all_zones') }}</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone->id }}" {{ request('zone_id') == $zone->id ? 'selected' : '' }}>
                                {{ $zone->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Level -->
                <div>
                    <label for="level" class="block text-sm font-medium text-gray-700">{{ __('regions.filters.level') }}</label>
                    <select name="level" id="level" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('regions.filters.all_levels') }}</option>
                        @foreach($levels as $value => $label)
                            <option value="{{ $value }}" {{ request('level') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Enabled -->
                <div>
                    <label for="is_enabled" class="block text-sm font-medium text-gray-700">{{ __('regions.filters.enabled') }}</label>
                    <select name="is_enabled" id="is_enabled" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('regions.filters.all') }}</option>
                        <option value="1" {{ request('is_enabled') === '1' ? 'selected' : '' }}>{{ __('regions.filters.enabled') }}</option>
                        <option value="0" {{ request('is_enabled') === '0' ? 'selected' : '' }}>{{ __('regions.filters.disabled') }}</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="flex gap-2">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                        {{ __('regions.filters.apply_filters') }}
                    </button>
                    <a href="{{ route('regions.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                        {{ __('regions.filters.clear_filters') }}
                    </a>
                </div>
            </form>
        </div>

        <!-- Results -->
        @if($regions->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($regions as $region)
                    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200">
                        <div class="p-6">
                            <!-- Region Header -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                        {{ $region->translated_name }}
                                    </h3>
                                    @if($region->code)
                                        <p class="text-sm text-gray-500">{{ $region->code }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center space-x-1">
                                    @if($region->is_default)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ __('regions.fields.default') }}
                                        </span>
                                    @endif
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $region->is_enabled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $region->is_enabled ? __('regions.fields.enabled') : __('regions.fields.disabled') }}
                                    </span>
                                </div>
                            </div>

                            <!-- Region Info -->
                            <div class="space-y-2">
                                @if($region->country)
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-globe mr-2"></i>
                                        <span>{{ $region->country->translated_name }}</span>
                                    </div>
                                @endif

                                @if($region->zone)
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-map-marker-alt mr-2"></i>
                                        <span>{{ $region->zone->name }}</span>
                                    </div>
                                @endif

                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-layer-group mr-2"></i>
                                    <span>{{ $levels[$region->level] ?? __('regions.fields.level') . ' ' . $region->level }}</span>
                                </div>

                                @if($region->parent)
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-arrow-up mr-2"></i>
                                        <span>{{ $region->parent->translated_name }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Description -->
                            @if($region->translated_description)
                                <div class="mt-4">
                                    <p class="text-sm text-gray-600 line-clamp-2">{{ Str::limit($region->translated_description, 100) }}</p>
                                </div>
                            @endif

                            <!-- Stats -->
                            <div class="mt-4 grid grid-cols-2 gap-2 text-xs text-gray-500">
                                <div class="flex items-center">
                                    <i class="fas fa-city mr-1"></i>
                                    <span>{{ $region->cities_count ?? 0 }} {{ __('regions.fields.cities') }}</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-sitemap mr-1"></i>
                                    <span>{{ $region->children_count ?? 0 }} {{ __('regions.fields.children') }}</span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <a href="{{ route('regions.show', $region) }}" 
                                   class="w-full bg-indigo-600 text-white text-center py-2 px-4 rounded-md hover:bg-indigo-700 transition-colors duration-200 block">
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
                <div class="mx-auto h-24 w-24 text-gray-400">
                    <i class="fas fa-map-marked-alt text-6xl"></i>
                </div>
                <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('regions.messages.no_regions_found') }}</h3>
                <p class="mt-2 text-gray-500">{{ __('regions.messages.try_different_filters') }}</p>
                <div class="mt-6">
                    <a href="{{ route('regions.index') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                        {{ __('regions.actions.view_all_regions') }}
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection