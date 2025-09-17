@extends('layouts.app')

@section('title', $country->translated_name . ' - ' . __('countries.title'))
@section('description', $country->translated_description ?: __('countries.subtitle'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-6">
        <ol class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
            <li>
                <a href="{{ route('countries.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                    {{ __('countries.title') }}
                </a>
            </li>
            <li class="flex items-center">
                <i class="fas fa-chevron-right mx-2"></i>
                <span class="text-gray-900 dark:text-white">{{ $country->translated_name }}</span>
            </li>
        </ol>
    </nav>

    <!-- Country Header -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
        <div class="flex flex-col md:flex-row items-start md:items-center space-y-4 md:space-y-0 md:space-x-6">
            <!-- Flag -->
            <div class="flex-shrink-0">
                @if($country->getFlagUrl())
                    <img src="{{ $country->getFlagUrl() }}" 
                         alt="{{ $country->translated_name }}" 
                         class="h-24 w-36 object-contain border border-gray-200 dark:border-gray-600 rounded">
                @else
                    <div class="h-24 w-36 bg-gray-200 dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600 flex items-center justify-center">
                        <i class="fas fa-flag text-gray-400 dark:text-gray-500 text-3xl"></i>
                    </div>
                @endif
            </div>

            <!-- Country Info -->
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                    {{ $country->translated_name }}
                </h1>
                
                @if($country->translated_official_name && $country->translated_official_name !== $country->translated_name)
                    <p class="text-lg text-gray-600 dark:text-gray-400 mb-4">
                        {{ $country->translated_official_name }}
                    </p>
                @endif

                <!-- Quick Info -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $country->cca2 }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('countries.fields.code') }}</div>
                    </div>
                    
                    @if($country->currency_code)
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $country->currency_code }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('countries.fields.currency') }}</div>
                        </div>
                    @endif
                    
                    @if($country->phone_calling_code)
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">+{{ $country->phone_calling_code }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('countries.fields.phone_code') }}</div>
                        </div>
                    @endif
                    
                    @if($country->is_eu_member)
                        <div class="text-center">
                            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                                <i class="fas fa-flag"></i>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('countries.fields.eu_member') }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Country Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('countries.details.basic_info') }}
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('countries.fields.name') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $country->translated_name }}</p>
                    </div>
                    
                    @if($country->translated_official_name)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('countries.fields.name_official') }}
                            </label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $country->translated_official_name }}</p>
                        </div>
                    @endif
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('countries.fields.code') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $country->cca2 }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            ISO Code
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $country->cca3 }}</p>
                    </div>
                    
                    @if($country->region)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('countries.fields.region') }}
                            </label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $country->region }}</p>
                        </div>
                    @endif
                    
                    @if($country->subregion)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('countries.fields.subregion') }}
                            </label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $country->subregion }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Economic Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('countries.details.economic_info') }}
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($country->currency_code)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('countries.fields.currency') }}
                            </label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $country->currency_code }}
                                @if($country->currency_symbol)
                                    ({{ $country->currency_symbol }})
                                @endif
                            </p>
                        </div>
                    @endif
                    
                    @if($country->vat_rate)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('countries.fields.vat_rate') }}
                            </label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ number_format($country->vat_rate, 2) }}%</p>
                        </div>
                    @endif
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('countries.fields.is_eu_member') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            @if($country->is_eu_member)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    <i class="fas fa-check mr-1"></i>
                                    {{ __('countries.fields.yes') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                    <i class="fas fa-times mr-1"></i>
                                    {{ __('countries.fields.no') }}
                                </span>
                            @endif
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('countries.fields.requires_vat') }}
                        </label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            @if($country->requires_vat)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                    <i class="fas fa-check mr-1"></i>
                                    {{ __('countries.fields.yes') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                    <i class="fas fa-times mr-1"></i>
                                    {{ __('countries.fields.no') }}
                                </span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Description -->
            @if($country->translated_description)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('countries.fields.description') }}
                    </h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {{ $country->translated_description }}
                    </p>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Contact Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('countries.details.contact_info') }}
                </h3>
                
                <div class="space-y-3">
                    @if($country->phone_calling_code)
                        <div class="flex items-center">
                            <i class="fas fa-phone text-gray-400 w-5"></i>
                            <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                                +{{ $country->phone_calling_code }}
                            </span>
                        </div>
                    @endif
                    
                    @if($country->timezone)
                        <div class="flex items-center">
                            <i class="fas fa-clock text-gray-400 w-5"></i>
                            <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                                {{ $country->timezone }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Related Cities -->
            @if($country->cities->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('countries.details.major_cities') }}
                    </h3>
                    
                    <div class="space-y-2">
                        @foreach($country->cities->take(10) as $city)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ $city->translated_name }}
                                </span>
                                @if($city->is_capital)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                        {{ __('countries.fields.capital') }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                        
                        @if($country->cities->count() > 10)
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                {{ __('countries.details.and_more', ['count' => $country->cities->count() - 10]) }}
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('countries.details.actions') }}
                </h3>
                
                <div class="space-y-3">
                    <a href="{{ route('countries.index') }}" 
                       class="w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-4 rounded-md text-sm font-medium transition duration-200 block">
                        {{ __('countries.actions.back_to_list') }}
                    </a>
                    
                    @if($country->latitude && $country->longitude)
                        <a href="https://maps.google.com/?q={{ $country->latitude }},{{ $country->longitude }}" 
                           target="_blank"
                           class="w-full bg-green-600 hover:bg-green-700 text-white text-center py-2 px-4 rounded-md text-sm font-medium transition duration-200 block">
                            {{ __('countries.actions.show_on_map') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
