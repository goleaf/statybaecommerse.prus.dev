@extends('layouts.app')

@section('title', $city->translated_name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-8">
        <ol class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
            <li><a href="{{ route('home') }}" class="hover:text-blue-600">{{ __('nav_home') }}</a></li>
            <li><span class="mx-2">/</span></li>
            <li><a href="{{ route('cities.index') }}" class="hover:text-blue-600">{{ __('cities.plural_model_label') }}</a></li>
            <li><span class="mx-2">/</span></li>
            <li class="text-gray-900 dark:text-white">{{ $city->translated_name }}</li>
        </ol>
    </nav>

    <!-- City Header -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
        <div class="flex items-start justify-between">
            <div>
                <div class="flex items-center space-x-3 mb-2">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $city->translated_name }}
                    </h1>
                    @if($city->is_capital)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            {{ __('cities.is_capital') }}
                        </span>
                    @endif
                </div>
                <p class="text-gray-600 dark:text-gray-400">{{ $city->code }}</p>
                @if($city->translated_description)
                    <p class="mt-2 text-gray-700 dark:text-gray-300">{{ $city->translated_description }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Basic Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('cities.basic_information') }}
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('cities.name') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $city->translated_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('cities.code') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $city->code }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('cities.level') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            @switch($city->level)
                                @case(0) {{ __('cities.level_city') }} @break
                                @case(1) {{ __('cities.level_district') }} @break
                                @case(2) {{ __('cities.level_neighborhood') }} @break
                                @case(3) {{ __('cities.level_suburb') }} @break
                                @default {{ __('cities.level_city') }}
                            @endswitch
                        </dd>
                    </div>
                    @if($city->type)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('cities.type') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $city->type }}</dd>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Location Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('cities.location') }}
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($city->country)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('cities.country') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                <a href="{{ route('countries.show', $city->country) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $city->country->translated_name }}
                                </a>
                            </dd>
                        </div>
                    @endif
                    @if($city->region)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('cities.region') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                <a href="{{ route('regions.show', $city->region) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $city->region->translated_name }}
                                </a>
                            </dd>
                        </div>
                    @endif
                    @if($city->zone)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('cities.zone') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                <a href="{{ route('zones.show', $city->zone) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $city->zone->translated_name }}
                                </a>
                            </dd>
                        </div>
                    @endif
                    @if($city->parent)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('cities.parent_city') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                <a href="{{ route('cities.show', $city->parent) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $city->parent->translated_name }}
                                </a>
                            </dd>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Geographic Data -->
            @if($city->latitude || $city->longitude || $city->population || $city->area)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('cities.geographic_data') }}
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($city->latitude && $city->longitude)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('cities.coordinates') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ number_format($city->latitude, 6) }}, {{ number_format($city->longitude, 6) }}
                                </dd>
                            </div>
                        @endif
                        @if($city->population)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('cities.population') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ number_format($city->population) }}</dd>
                            </div>
                        @endif
                        @if($city->area)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('cities.area') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ number_format($city->area, 2) }} km²</dd>
                            </div>
                        @endif
                        @if($city->density)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('cities.density') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ number_format($city->density, 2) }} /km²</dd>
                            </div>
                        @endif
                        @if($city->elevation)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('cities.elevation') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ number_format($city->elevation, 2) }} m</dd>
                            </div>
                        @endif
                        @if($city->timezone)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('cities.timezone') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $city->timezone }}</dd>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Related Cities -->
            @if($city->children->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('cities.related_cities') }}
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($city->children as $child)
                            <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                <h3 class="font-medium text-gray-900 dark:text-white">
                                    <a href="{{ route('cities.show', $child) }}" class="hover:text-blue-600">
                                        {{ $child->translated_name }}
                                    </a>
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $child->code }}</p>
                                @if($child->population)
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        {{ number_format($child->population) }} {{ __('cities.population') }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Stats -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('cities.quick_stats') }}
                </h3>
                <div class="space-y-3">
                    @if($city->population)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('cities.population') }}</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($city->population) }}</span>
                        </div>
                    @endif
                    @if($city->area)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('cities.area') }}</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($city->area, 2) }} km²</span>
                        </div>
                    @endif
                    @if($city->density)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('cities.density') }}</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($city->density, 2) }} /km²</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Additional Info -->
            @if($city->currency_code || $city->language_code || $city->phone_code)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('cities.additional_info') }}
                    </h3>
                    <div class="space-y-3">
                        @if($city->currency_code)
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('cities.currency_code') }}</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $city->currency_code }}</span>
                            </div>
                        @endif
                        @if($city->language_code)
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('cities.language_code') }}</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $city->language_code }}</span>
                            </div>
                        @endif
                        @if($city->phone_code)
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('cities.phone_code') }}</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $city->phone_code }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
