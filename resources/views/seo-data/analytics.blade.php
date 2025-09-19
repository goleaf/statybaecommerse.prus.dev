@extends('components.layouts.base')

@section('title', __('seo_data.analytics'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
            {{ __('seo_data.analytics') }}
        </h1>
        <p class="text-gray-600 dark:text-gray-400">
            {{ __('seo_data.analytics_description') }}
        </p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        {{ __('seo_data.total_seo_data') }}
                    </p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ number_format($stats['total']) }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        {{ __('seo_data.complete_seo') }}
                    </p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ number_format($stats['complete_seo']) }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        {{ __('seo_data.needs_optimization') }}
                    </p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ number_format($stats['needs_optimization']) }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        {{ __('seo_data.avg_seo_score') }}
                    </p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ number_format($stats['avg_score'], 1) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- By Locale -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                {{ __('seo_data.by_locale') }}
            </h3>
            
            <div class="space-y-4">
                @foreach($stats['by_locale'] as $locale => $count)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($locale === 'lt') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 @endif">
                                {{ $locale === 'lt' ? __('seo_data.lithuanian') : __('seo_data.english') }}
                            </span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full w-var" data-width="{{ $stats['total'] > 0 ? ($count / $stats['total']) * 100 : 0 }}"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white w-12 text-right">
                                {{ number_format($count) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- By Type -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                {{ __('seo_data.by_type') }}
            </h3>
            
            <div class="space-y-4">
                @foreach($stats['by_type'] as $type => $count)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                @switch($type)
                                    @case('App\Models\Product')
                                        {{ __('seo_data.products') }}
                                        @break
                                    @case('App\Models\Category')
                                        {{ __('seo_data.categories') }}
                                        @break
                                    @case('App\Models\Brand')
                                        {{ __('seo_data.brands') }}
                                        @break
                                    @default
                                        {{ $type }}
                                @endswitch
                            </span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full w-var" data-width="{{ $stats['total'] > 0 ? ($count / $stats['total']) * 100 : 0 }}"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white w-12 text-right">
                                {{ number_format($count) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            {{ __('seo_data.quick_actions') }}
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('seo-data.index') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-md transition duration-200 text-center">
                {{ __('seo_data.view_all_seo_data') }}
            </a>
            
            <a href="{{ route('seo-data.by-type', 'App\Models\Product') }}" 
               class="bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-md transition duration-200 text-center">
                {{ __('seo_data.view_product_seo') }}
            </a>
            
            <a href="{{ route('seo-data.by-type', 'App\Models\Category') }}" 
               class="bg-purple-600 hover:bg-purple-700 text-white font-medium py-3 px-4 rounded-md transition duration-200 text-center">
                {{ __('seo_data.view_category_seo') }}
            </a>
        </div>
    </div>
</div>
@endsection
