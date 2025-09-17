@extends('layouts.app')

@section('title', $category->getTranslatedName() . ' - ' . __('admin.system_settings.frontend.title'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-8">
            <ol class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
                <li>
                    <a href="{{ route('frontend.system-settings.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                        {{ __('admin.system_settings.frontend.title') }}
                    </a>
                </li>
                <li class="flex items-center">
                    <x-heroicon-o-chevron-right class="w-4 h-4 mx-2" />
                    {{ $category->getTranslatedName() }}
                </li>
            </ol>
        </nav>

        <!-- Category Header -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-8">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-folder class="w-8 h-8 text-white" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-white">
                            {{ $category->getTranslatedName() }}
                        </h1>
                        <p class="text-blue-100 mt-1">
                            {{ $settings->count() }} {{ __('admin.system_settings.settings') }}
                        </p>
                    </div>
                </div>
                @if($category->getTranslatedDescription())
                    <div class="mt-4">
                        <p class="text-blue-100 leading-relaxed">
                            {{ $category->getTranslatedDescription() }}
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Settings Grid -->
        @if($settings->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($settings as $setting)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition duration-200 p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                        <x-heroicon-o-cog-6-tooth class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ $setting->getTranslatedName() }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 font-mono">
                                        {{ $setting->key }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                    {{ __('admin.system_settings.' . $setting->group) }}
                                </span>
                            </div>
                        </div>

                        <div class="mb-4">
                            @if($setting->getTranslatedDescription())
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                    {{ Str::limit($setting->getTranslatedDescription(), 100) }}
                                </p>
                            @endif
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('admin.system_settings.value') }}:
                                </span>
                                <span class="text-sm text-gray-900 dark:text-white font-mono">
                                    {{ $setting->getFormattedValue() }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    {{ __('admin.system_settings.' . $setting->type) }}
                                </span>
                                @if($setting->is_required)
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        {{ __('admin.system_settings.required') }}
                                    </span>
                                @endif
                            </div>
                            <a href="{{ route('frontend.system-settings.show', $setting) }}" 
                               class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
                                {{ __('admin.system_settings.frontend.view_setting') }}
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <div class="w-24 h-24 mx-auto mb-4 text-gray-400">
                    <x-heroicon-o-folder-open class="w-full h-full" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                    {{ __('admin.system_settings.frontend.no_settings_found') }}
                </h3>
                <p class="text-gray-600 dark:text-gray-400">
                    {{ __('admin.system_settings.frontend.loading_settings') }}
                </p>
            </div>
        @endif

        <!-- Back Button -->
        <div class="mt-8 text-center">
            <a href="{{ route('frontend.system-settings.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-md transition duration-200">
                <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                {{ __('admin.system_settings.frontend.back_to_settings') }}
            </a>
        </div>
    </div>
</div>
@endsection

