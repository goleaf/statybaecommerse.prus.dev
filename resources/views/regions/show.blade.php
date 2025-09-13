@extends('layouts.app')

@section('title', $region->translated_name . ' - ' . __('regions.title'))
@section('description', $region->translated_description ?: __('regions.subtitle'))

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Breadcrumb -->
    <div class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-4">
                    <li>
                        <div>
                            <a href="{{ route('regions.index') }}" class="text-gray-400 hover:text-gray-500">
                                <i class="fas fa-map-marked-alt"></i>
                                <span class="sr-only">{{ __('regions.title') }}</span>
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                            <span class="text-sm font-medium text-gray-500">{{ $region->translated_name }}</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Region Header -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header Section -->
            <div class="px-6 py-8">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between">
                    <div class="flex-1">
                        <h1 class="text-4xl font-bold text-gray-900 mb-2">{{ $region->translated_name }}</h1>
                        @if($region->translated_official_name && $region->translated_official_name !== $region->translated_name)
                            <p class="text-xl text-gray-600 mb-4">{{ $region->translated_official_name }}</p>
                        @endif
                        
                        <!-- Quick Info -->
                        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 mb-4">
                            @if($region->code)
                                <div class="flex items-center">
                                    <i class="fas fa-tag mr-2"></i>
                                    <span class="font-medium">{{ $region->code }}</span>
                                </div>
                            @endif
                            
                            @if($region->country)
                                <div class="flex items-center">
                                    <i class="fas fa-globe mr-2"></i>
                                    <span>{{ $region->country->translated_name }}</span>
                                </div>
                            @endif
                            
                            @if($region->zone)
                                <div class="flex items-center">
                                    <i class="fas fa-map-marker-alt mr-2"></i>
                                    <span>{{ $region->zone->name }}</span>
                                </div>
                            @endif
                            
                            <div class="flex items-center">
                                <i class="fas fa-layer-group mr-2"></i>
                                <span>{{ $region->getLevelName() }}</span>
                            </div>
                        </div>

                        <!-- Status Badges -->
                        <div class="flex flex-wrap gap-2">
                            @if($region->is_default)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-star mr-1"></i>
                                    {{ __('regions.fields.default') }}
                                </span>
                            @endif
                            
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $region->is_enabled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                <i class="fas {{ $region->is_enabled ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                {{ $region->is_enabled ? __('regions.fields.enabled') : __('regions.fields.disabled') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="px-6 pb-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Main Information -->
                    <div class="lg:col-span-2 space-y-8">
                        <!-- Basic Information -->
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('regions.sections.basic_information') }}</h2>
                            <div class="bg-gray-50 rounded-lg p-6">
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('regions.fields.name') }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $region->translated_name }}</dd>
                                    </div>
                                    
                                    @if($region->code)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ __('regions.fields.code') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $region->code }}</dd>
                                        </div>
                                    @endif
                                    
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('regions.fields.level') }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $region->getLevelName() }}</dd>
                                    </div>
                                    
                                    @if($region->parent)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ __('regions.fields.parent_region') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900">
                                                <a href="{{ route('regions.show', $region->parent) }}" class="text-indigo-600 hover:text-indigo-500">
                                                    {{ $region->parent->translated_name }}
                                                </a>
                                            </dd>
                                        </div>
                                    @endif
                                    
                                    @if($region->translated_description)
                                        <div class="sm:col-span-2">
                                            <dt class="text-sm font-medium text-gray-500">{{ __('regions.fields.description') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $region->translated_description }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        </div>

                        <!-- Geographic Information -->
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('regions.sections.geographic_information') }}</h2>
                            <div class="bg-gray-50 rounded-lg p-6">
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @if($region->country)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ __('regions.fields.country') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900">
                                                <a href="{{ route('countries.show', $region->country) }}" class="text-indigo-600 hover:text-indigo-500">
                                                    {{ $region->country->translated_name }}
                                                </a>
                                            </dd>
                                        </div>
                                    @endif
                                    
                                    @if($region->zone)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ __('regions.fields.zone') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $region->zone->name }}</dd>
                                        </div>
                                    @endif
                                    
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('regions.fields.depth') }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $region->depth ?? 0 }}</dd>
                                    </div>
                                    
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('regions.fields.sort_order') }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $region->sort_order ?? 0 }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Hierarchy Information -->
                        @if($region->children->count() > 0)
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('regions.sections.sub_regions') }}</h2>
                                <div class="bg-gray-50 rounded-lg p-6">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        @foreach($region->children as $child)
                                            <div class="flex items-center justify-between p-3 bg-white rounded-lg border">
                                                <div>
                                                    <a href="{{ route('regions.show', $child) }}" class="text-indigo-600 hover:text-indigo-500 font-medium">
                                                        {{ $child->translated_name }}
                                                    </a>
                                                    <p class="text-sm text-gray-500">{{ $child->getLevelName() }}</p>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $child->cities_count ?? 0 }} {{ __('regions.fields.cities') }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Related Cities -->
                        @if($region->cities->count() > 0)
                            <div class="bg-white rounded-lg shadow p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('regions.sections.major_cities') }}</h3>
                                <div class="space-y-3">
                                    @foreach($region->cities as $city)
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <a href="{{ route('cities.show', $city) }}" class="text-indigo-600 hover:text-indigo-500 font-medium">
                                                    {{ $city->translated_name }}
                                                </a>
                                                @if($city->is_capital)
                                                    <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        {{ __('cities.fields.capital') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ number_format($city->population ?? 0) }}
                                            </div>
                                        </div>
                                    @endforeach
                                    
                                    @if($region->cities_count > $region->cities->count())
                                        <div class="pt-3 border-t border-gray-200">
                                            <a href="{{ route('cities.index', ['region_id' => $region->id]) }}" class="text-indigo-600 hover:text-indigo-500 text-sm">
                                                {{ __('regions.actions.view_all_cities') }} ({{ $region->cities_count ?? 0 }})
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Related Regions -->
                        @if($relatedRegions->count() > 0)
                            <div class="bg-white rounded-lg shadow p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('regions.sections.related_regions') }}</h3>
                                <div class="space-y-3">
                                    @foreach($relatedRegions as $related)
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <a href="{{ route('regions.show', $related) }}" class="text-indigo-600 hover:text-indigo-500 font-medium">
                                                    {{ $related->translated_name }}
                                                </a>
                                                <p class="text-sm text-gray-500">{{ $related->getLevelName() }}</p>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $related->cities_count ?? 0 }} {{ __('regions.fields.cities') }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Actions -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('regions.sections.actions') }}</h3>
                            <div class="space-y-3">
                                <a href="{{ route('regions.index') }}" class="w-full bg-gray-600 text-white text-center py-2 px-4 rounded-md hover:bg-gray-700 transition-colors duration-200 block">
                                    {{ __('regions.actions.back_to_list') }}
                                </a>
                                
                                @if($region->country)
                                    <a href="{{ route('countries.show', $region->country) }}" class="w-full bg-indigo-600 text-white text-center py-2 px-4 rounded-md hover:bg-indigo-700 transition-colors duration-200 block">
                                        {{ __('regions.actions.view_country') }}
                                    </a>
                                @endif
                                
                                @if($region->cities_count > 0)
                                    <a href="{{ route('cities.index', ['region_id' => $region->id]) }}" class="w-full bg-green-600 text-white text-center py-2 px-4 rounded-md hover:bg-green-700 transition-colors duration-200 block">
                                        {{ __('regions.actions.view_cities') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection