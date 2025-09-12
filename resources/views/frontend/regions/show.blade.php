@extends('frontend.layouts.app')

@section('title', $region->translated_name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-8">
        <ol class="flex items-center space-x-2 text-sm text-gray-500">
            <li><a href="{{ route('frontend.regions.index') }}" class="hover:text-gray-700">{{ __('regions.all_regions') }}</a></li>
            @foreach($region->breadcrumb as $breadcrumbRegion)
                <li class="flex items-center">
                    <svg class="w-4 h-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    @if($breadcrumbRegion->id === $region->id)
                        <span class="text-gray-900 font-medium">{{ $breadcrumbRegion->translated_name }}</span>
                    @else
                        <a href="{{ route('frontend.regions.show', $breadcrumbRegion) }}" class="hover:text-gray-700">
                            {{ $breadcrumbRegion->translated_name }}
                        </a>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>

    <!-- Region Header -->
    <div class="bg-white rounded-lg shadow-md p-8 mb-8">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $region->translated_name }}</h1>
                <div class="flex items-center space-x-4 mb-4">
                    <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">
                        {{ $region->code }}
                    </span>
                    <span class="bg-gray-100 text-gray-800 text-sm font-medium px-3 py-1 rounded-full">
                        {{ __('regions.level') }}: {{ $region->level }}
                    </span>
                    @if($region->is_default)
                        <span class="bg-green-100 text-green-800 text-sm font-medium px-3 py-1 rounded-full">
                            {{ __('regions.default_region') }}
                        </span>
                    @endif
                </div>
                
                @if($region->translated_description)
                    <p class="text-gray-600 text-lg">{{ $region->translated_description }}</p>
                @endif
            </div>
            
            <div class="text-right">
                <div class="text-sm text-gray-500 mb-2">{{ __('regions.created_at') }}</div>
                <div class="text-gray-900">{{ $region->created_at->format('d.m.Y') }}</div>
            </div>
        </div>
    </div>

    <!-- Region Information -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Basic Information -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ __('regions.basic_information') }}</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('regions.name') }}</label>
                        <p class="text-gray-900">{{ $region->translated_name }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('regions.code') }}</label>
                        <p class="text-gray-900">{{ $region->code }}</p>
                    </div>
                    
                    @if($region->country)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('regions.country') }}</label>
                            <p class="text-gray-900">{{ $region->country->name }}</p>
                        </div>
                    @endif
                    
                    @if($region->zone)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('regions.zone') }}</label>
                            <p class="text-gray-900">{{ $region->zone->name }}</p>
                        </div>
                    @endif
                    
                    @if($region->parent)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('regions.parent_region') }}</label>
                            <a href="{{ route('frontend.regions.show', $region->parent) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $region->parent->translated_name }}
                            </a>
                        </div>
                    @endif
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('regions.level') }}</label>
                        <p class="text-gray-900">{{ $region->level }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistics -->
        <div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ __('regions.statistics') }}</h2>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">{{ __('regions.cities_count') }}</span>
                        <span class="font-semibold text-gray-900">{{ $region->cities()->count() }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">{{ __('regions.addresses_count') }}</span>
                        <span class="font-semibold text-gray-900">{{ $region->addresses()->count() }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">{{ __('regions.users_count') }}</span>
                        <span class="font-semibold text-gray-900">{{ $region->users()->count() }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">{{ __('regions.orders_count') }}</span>
                        <span class="font-semibold text-gray-900">{{ $region->orders()->count() }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">{{ __('regions.customers_count') }}</span>
                        <span class="font-semibold text-gray-900">{{ $region->customers()->count() }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">{{ __('regions.warehouses_count') }}</span>
                        <span class="font-semibold text-gray-900">{{ $region->warehouses()->count() }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">{{ __('regions.stores_count') }}</span>
                        <span class="font-semibold text-gray-900">{{ $region->stores()->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sub Regions -->
    @if($region->children->count() > 0)
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ __('regions.sub_regions') }}</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($region->children as $child)
                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors duration-200">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-medium text-gray-900">{{ $child->translated_name }}</h3>
                            <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2 py-1 rounded">
                                {{ $child->code }}
                            </span>
                        </div>
                        
                        <div class="text-sm text-gray-500 mb-3">
                            {{ __('regions.level') }}: {{ $child->level }}
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">
                                {{ $child->cities()->count() }} {{ __('regions.cities') }}
                            </span>
                            <a href="{{ route('frontend.regions.show', $child) }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                {{ __('regions.view_details') }}
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Cities -->
    @if($region->cities->count() > 0)
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ __('regions.cities') }}</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($region->cities as $city)
                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors duration-200">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-medium text-gray-900">{{ $city->name }}</h3>
                            <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2 py-1 rounded">
                                {{ $city->code }}
                            </span>
                        </div>
                        
                        @if($city->description)
                            <p class="text-sm text-gray-500 mb-3">{{ Str::limit($city->description, 80) }}</p>
                        @endif
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">
                                {{ $city->addresses()->count() }} {{ __('regions.addresses') }}
                            </span>
                            <a href="{{ route('frontend.cities.show', $city) }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                {{ __('regions.view_details') }}
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Actions -->
    <div class="flex justify-center space-x-4">
        <a href="{{ route('frontend.regions.index') }}" 
           class="bg-gray-600 text-white px-6 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
            {{ __('regions.back_to_regions') }}
        </a>
        
        @if($region->parent)
            <a href="{{ route('frontend.regions.show', $region->parent) }}" 
               class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                {{ __('regions.back_to_parent') }}
            </a>
        @endif
    </div>
</div>
@endsection

