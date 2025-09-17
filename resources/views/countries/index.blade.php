@extends('layouts.app')

@section('title', __('countries.title'))
@section('description', __('countries.subtitle'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
            {{ __('countries.title') }}
        </h1>
        <p class="text-gray-600 dark:text-gray-400">
            {{ __('countries.subtitle') }}
        </p>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
        <form method="GET" action="{{ route('countries.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('countries.filters.search') }}
                    </label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="{{ __('countries.filters.search_placeholder') }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                </div>

                <!-- Region Filter -->
                <div>
                    <label for="region" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('countries.filters.by_region') }}
                    </label>
                    <select id="region" 
                            name="region" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">{{ __('countries.filters.all_regions') }}</option>
                        @foreach($regions as $region)
                            <option value="{{ $region }}" {{ request('region') === $region ? 'selected' : '' }}>
                                {{ $region }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Currency Filter -->
                <div>
                    <label for="currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('countries.filters.by_currency') }}
                    </label>
                    <select id="currency" 
                            name="currency" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">{{ __('countries.filters.all_currencies') }}</option>
                        @foreach($currencies as $currency)
                            <option value="{{ $currency }}" {{ request('currency') === $currency ? 'selected' : '' }}>
                                {{ $currency }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- EU Member Filter -->
                <div>
                    <label for="eu_member" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('countries.filters.eu_members') }}
                    </label>
                    <select id="eu_member" 
                            name="eu_member" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">{{ __('countries.filters.all') }}</option>
                        <option value="1" {{ request('eu_member') === '1' ? 'selected' : '' }}>
                            {{ __('countries.filters.eu_members_only') }}
                        </option>
                        <option value="0" {{ request('eu_member') === '0' ? 'selected' : '' }}>
                            {{ __('countries.filters.non_eu_only') }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="flex justify-between items-center">
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                    {{ __('countries.filters.apply_filters') }}
                </button>
                
                <a href="{{ route('countries.index') }}" 
                   class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                    {{ __('countries.filters.clear_filters') }}
                </a>
            </div>
        </form>
    </div>

    <!-- Countries Grid -->
    @if($countries->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($countries as $country)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 overflow-hidden">
                    <!-- Flag -->
                    <div class="h-32 bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                        @if($country->getFlagUrl())
                            <img src="{{ $country->getFlagUrl() }}" 
                                 alt="{{ $country->translated_name }}" 
                                 class="h-20 w-auto object-contain">
                        @else
                            <div class="text-gray-400 dark:text-gray-500 text-4xl">
                                <i class="fas fa-flag"></i>
                            </div>
                        @endif
                    </div>

                    <!-- Content -->
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            {{ $country->translated_name }}
                        </h3>
                        
                        <div class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                            <div class="flex justify-between">
                                <span>{{ __('countries.fields.code') }}:</span>
                                <span class="font-medium">{{ $country->cca2 }}</span>
                            </div>
                            
                            @if($country->region)
                                <div class="flex justify-between">
                                    <span>{{ __('countries.fields.region') }}:</span>
                                    <span class="font-medium">{{ $country->region }}</span>
                                </div>
                            @endif
                            
                            @if($country->currency_code)
                                <div class="flex justify-between">
                                    <span>{{ __('countries.fields.currency') }}:</span>
                                    <span class="font-medium">{{ $country->currency_code }}</span>
                                </div>
                            @endif
                            
                            @if($country->is_eu_member)
                                <div class="flex items-center justify-between">
                                    <span>{{ __('countries.fields.is_eu_member') }}:</span>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        <i class="fas fa-check mr-1"></i>
                                        {{ __('countries.fields.yes') }}
                                    </span>
                                </div>
                            @endif
                            
                            @if($country->vat_rate)
                                <div class="flex justify-between">
                                    <span>{{ __('countries.fields.vat_rate') }}:</span>
                                    <span class="font-medium">{{ number_format($country->vat_rate, 2) }}%</span>
                                </div>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="mt-4 flex space-x-2">
                            <a href="{{ route('countries.show', $country) }}" 
                               class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-3 rounded-md text-sm font-medium transition duration-200">
                                {{ __('countries.actions.view_details') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $countries->appends(request()->query())->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <div class="text-gray-400 dark:text-gray-500 text-6xl mb-4">
                <i class="fas fa-globe"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                {{ __('countries.messages.no_countries_found') }}
            </h3>
            <p class="text-gray-600 dark:text-gray-400">
                {{ __('countries.messages.try_different_filters') }}
            </p>
        </div>
    @endif
</div>
@endsection
