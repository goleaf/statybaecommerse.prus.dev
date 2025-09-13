@extends('layouts.app')

@section('title', __('frontend.system_settings.title'))
@section('description', __('frontend.system_settings.description'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                {{ __('frontend.system_settings.title') }}
            </h1>
            <p class="text-lg text-gray-600 dark:text-gray-300">
                {{ __('frontend.system_settings.description') }}
            </p>
        </div>

        <!-- Search -->
        <div class="mb-8">
            <form action="{{ route('frontend.system-settings.search') }}" method="GET" class="max-w-md">
                <div class="relative">
                    <input type="text" 
                           name="q" 
                           value="{{ request('q') }}"
                           placeholder="{{ __('frontend.system_settings.search_placeholder') }}"
                           class="w-full px-4 py-2 pl-10 pr-4 text-gray-700 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-800 dark:text-white dark:border-gray-600">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </form>
        </div>

        <!-- Filters -->
        <div class="mb-8 flex flex-wrap gap-4">
            <div class="flex items-center space-x-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('frontend.system_settings.filter_by_category') }}:
                </label>
                <select onchange="window.location.href = this.value" class="px-3 py-1 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white dark:border-gray-600">
                    <option value="{{ route('frontend.system-settings.index') }}">
                        {{ __('frontend.system_settings.all_categories') }}
                    </option>
                    @foreach($categories as $category)
                        <option value="{{ route('frontend.system-settings.category', $category->slug) }}" 
                                {{ request('category') === $category->slug ? 'selected' : '' }}>
                            {{ $category->getTranslatedName() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('frontend.system_settings.filter_by_group') }}:
                </label>
                <select onchange="window.location.href = this.value" class="px-3 py-1 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white dark:border-gray-600">
                    <option value="{{ route('frontend.system-settings.index') }}">
                        {{ __('frontend.system_settings.all_groups') }}
                    </option>
                    @foreach(['general', 'ecommerce', 'email', 'payment', 'shipping', 'seo', 'security', 'api', 'appearance', 'notifications'] as $group)
                        <option value="{{ route('frontend.system-settings.group', $group) }}" 
                                {{ request('group') === $group ? 'selected' : '' }}>
                            {{ ucfirst($group) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Categories Grid -->
        @if($categories->isNotEmpty())
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                {{ __('frontend.system_settings.categories') }}
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($categories as $category)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                    <i class="{{ $category->getIconClass() }} text-blue-600 dark:text-blue-400"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ $category->getTranslatedName() }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $category->settings->count() }} {{ __('frontend.system_settings.settings') }}
                                </p>
                            </div>
                        </div>
                        @if($category->getTranslatedDescription())
                            <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">
                                {{ $category->getTranslatedDescription() }}
                            </p>
                        @endif
                        <a href="{{ route('frontend.system-settings.category', $category->slug) }}" 
                           class="inline-flex items-center text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            {{ __('frontend.system_settings.view_category') }}
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Settings List -->
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                {{ __('frontend.system_settings.all_settings') }}
            </h2>
            
            @if($settings->isNotEmpty())
                <div class="space-y-4">
                    @foreach($settings as $setting)
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                            {{ $setting->getTranslatedName() }}
                                        </h3>
                                        <span class="ml-2 px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full dark:bg-gray-700 dark:text-gray-200">
                                            {{ $setting->key }}
                                        </span>
                                    </div>
                                    
                                    @if($setting->getTranslatedDescription())
                                        <p class="text-gray-600 dark:text-gray-300 mb-3">
                                            {{ $setting->getTranslatedDescription() }}
                                        </p>
                                    @endif

                                    <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                                        @if($setting->category)
                                            <span class="flex items-center">
                                                <i class="{{ $setting->category->getIconClass() }} mr-1"></i>
                                                {{ $setting->category->getTranslatedName() }}
                                            </span>
                                        @endif
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full dark:bg-blue-900 dark:text-blue-200">
                                            {{ ucfirst($setting->group) }}
                                        </span>
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full dark:bg-green-900 dark:text-green-200">
                                            {{ ucfirst($setting->type) }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="ml-4">
                                    <a href="{{ route('frontend.system-settings.show', $setting->key) }}" 
                                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                        {{ __('frontend.system_settings.view_setting') }}
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $settings->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <div class="text-gray-400 dark:text-gray-500 mb-4">
                        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.29-1.009-5.824-2.709M15 6.291A7.962 7.962 0 0012 5c-2.34 0-4.29 1.009-5.824 2.709"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                        {{ __('frontend.system_settings.no_settings_found') }}
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400">
                        {{ __('frontend.system_settings.no_settings_description') }}
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection