@extends('layouts.app')

@section('title', $region->translated_name . ' - ' . __('regions.title'))
@section('description', $region->translated_description ?: __('regions.subtitle'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-6">
        <ol class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
            <li>
                <a href="{{ route('regions.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                    {{ __('regions.title') }}
                </a>
            </li>
            <li class="flex items-center">
                <i class="fas fa-chevron-right mx-2"></i>
                <span class="text-gray-900 dark:text-white">{{ $region->translated_name }}</span>
            </li>
        </ol>
    </nav>

    <!-- Region Header -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
        <div class="flex flex-col md:flex-row items-start md:items-center space-y-4 md:space-y-0 md:space-x-6">
            <!-- Icon -->
            <div class="flex-shrink-0">
                <div class="h-24 w-24 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                    <i class="fas fa-map text-blue-600 dark:text-blue-400 text-3xl"></i>
                </div>
            </div>

            <!-- Region Info -->
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                    {{ $region->translated_name }}
                </h1>
                
                @if($region->translated_description)
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        {{ $region->translated_description }}
                    </p>
                @endif

                <!-- Quick Info -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $region->level }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('regions.fields.level') }}</div>
                    </div>
                    
                    @if($region->code)
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $region->code }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('regions.fields.code') }}</div>
                        </div>
                    @endif
                    
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $region->cities->count() }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('regions.fields.cities') }}</div>
                    </div>
                    
                    @if($region->children->count() > 0)
                        <div class="text-center">
                            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $region->children->count() }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('regions.fields.children') }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Region Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('regions.details.basic_info') }}
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('regions.fields.name') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $region->translated_name }}</p>
                    </div>
                    
                    @if($region->code)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('regions.fields.code') }}
                            </label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $region->code }}</p>
                        </div>
                    @endif
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('regions.fields.level') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ __('regions.levels.' . $region->level) }}</p>
                    </div>
                    
                    @if($region->country)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('regions.fields.country') }}
                            </label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $region->country->translated_name }}</p>
                        </div>
                    @endif
                    
                    @if($region->parent)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('regions.fields.parent') }}
                            </label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $region->parent->translated_name }}</p>
                        </div>
                    @endif
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('regions.fields.sort_order') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $region->sort_order }}</p>
                    </div>
                </div>
            </div>

            <!-- Hierarchy Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('regions.details.hierarchy_info') }}
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('regions.fields.depth') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $region->depth }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('regions.fields.is_root') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            @if($region->is_root)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    <i class="fas fa-check mr-1"></i>
                                    {{ __('regions.fields.yes') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                    <i class="fas fa-times mr-1"></i>
                                    {{ __('regions.fields.no') }}
                                </span>
                            @endif
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('regions.fields.has_children') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            @if($region->children->count() > 0)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    <i class="fas fa-check mr-1"></i>
                                    {{ __('regions.fields.yes') }} ({{ $region->children->count() }})
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                    <i class="fas fa-times mr-1"></i>
                                    {{ __('regions.fields.no') }}
                                </span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Description -->
            @if($region->translated_description)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('regions.fields.description') }}
                    </h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {{ $region->translated_description }}
                    </p>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Child Regions -->
            @if($region->children->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('regions.details.child_regions') }}
                    </h3>
                    
                    <div class="space-y-2">
                        @foreach($region->children->take(10) as $child)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ $child->translated_name }}
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $child->cities->count() }} {{ __('regions.fields.cities') }}
                                </span>
                            </div>
                        @endforeach
                        
                        @if($region->children->count() > 10)
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                {{ __('regions.details.and_more', ['count' => $region->children->count() - 10]) }}
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Related Cities -->
            @if($region->cities->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('regions.details.related_cities') }}
                    </h3>
                    
                    <div class="space-y-2">
                        @foreach($region->cities->take(10) as $city)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ $city->translated_name }}
                                </span>
                                @if($city->is_capital)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                        {{ __('regions.fields.capital') }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                        
                        @if($region->cities->count() > 10)
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                {{ __('regions.details.and_more', ['count' => $region->cities->count() - 10]) }}
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('regions.details.actions') }}
                </h3>
                
                <div class="space-y-3">
                    <a href="{{ route('regions.index') }}" 
                       class="w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-4 rounded-md text-sm font-medium transition duration-200 block">
                        {{ __('regions.actions.back_to_list') }}
                    </a>
                    
                    @if($region->country && $region->country->latitude && $region->country->longitude)
                        <a href="https://maps.google.com/?q={{ $region->country->latitude }},{{ $region->country->longitude }}" 
                           target="_blank"
                           class="w-full bg-green-600 hover:bg-green-700 text-white text-center py-2 px-4 rounded-md text-sm font-medium transition duration-200 block">
                            {{ __('regions.actions.show_on_map') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection