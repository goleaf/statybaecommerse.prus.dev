@extends('components.layouts.base')

@section('title', $setting->getTranslatedName() . ' - ' . __('frontend.system_settings.title'))
@section('description', $setting->getTranslatedDescription())

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-8">
            <ol class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                <li>
                    <a href="{{ route('frontend.system-settings.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                        {{ __('frontend.system_settings.title') }}
                    </a>
                </li>
                <li>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </li>
                <li>
                    <a href="{{ route('frontend.system-settings.category', $setting->category->slug) }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                        {{ $setting->category->getTranslatedName() }}
                    </a>
                </li>
                <li>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </li>
                <li class="text-gray-900 dark:text-white">
                    {{ $setting->getTranslatedName() }}
                </li>
            </ol>
        </nav>

        <!-- Setting Details -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                            {{ $setting->getTranslatedName() }}
                        </h1>
                        <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                            <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full dark:bg-gray-700 dark:text-gray-200">
                                {{ $setting->key }}
                            </span>
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full dark:bg-blue-900 dark:text-blue-200">
                                {{ ucfirst($setting->group) }}
                            </span>
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full dark:bg-green-900 dark:text-green-200">
                                {{ ucfirst($setting->type) }}
                            </span>
                        </div>
                    </div>
                    
                    @if($setting->category)
                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                            <i class="{{ $setting->category->getIconClass() }} mr-2"></i>
                            {{ $setting->category->getTranslatedName() }}
                        </div>
                    @endif
                </div>

                @if($setting->getTranslatedDescription())
                    <p class="text-lg text-gray-600 dark:text-gray-300">
                        {{ $setting->getTranslatedDescription() }}
                    </p>
                @endif
            </div>

            <!-- Value Section -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('frontend.system_settings.current_value') }}
                </h2>
                
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                    @switch($setting->type)
                        @case('boolean')
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $setting->value ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                    {{ $setting->value ? __('frontend.system_settings.enabled') : __('frontend.system_settings.disabled') }}
                                </span>
                            </div>
                            @break
                            
                        @case('array')
                        @case('json')
                            <pre class="text-sm text-gray-800 dark:text-gray-200 bg-white dark:bg-gray-800 p-4 rounded border overflow-x-auto"><code>{{ json_encode($setting->value, JSON_PRETTY_PRINT) }}</code></pre>
                            @break
                            
                        @case('color')
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 rounded-lg border-2 border-gray-300 dark:border-gray-600" 
                                     style="background-color: {{ $setting->value }}"></div>
                                <span class="text-lg font-mono text-gray-800 dark:text-gray-200">{{ $setting->value }}</span>
                            </div>
                            @break
                            
                        @case('file')
                        @case('image')
                            @if($setting->value)
                                <div class="flex items-center space-x-3">
                                    @if($setting->type === 'image')
                                        <img src="{{ $setting->value }}" alt="{{ $setting->getTranslatedName() }}" 
                                             class="w-16 h-16 object-cover rounded-lg">
                                    @else
                                        <div class="w-16 h-16 bg-gray-200 dark:bg-gray-600 rounded-lg flex items-center justify-center">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.29-1.009-5.824-2.709M15 6.291A7.962 7.962 0 0012 5c-2.34 0-4.29 1.009-5.824 2.709"></path>
                                            </svg>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ basename($setting->value) }}</p>
                                        <a href="{{ $setting->value }}" target="_blank" 
                                           class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm">
                                            {{ __('frontend.system_settings.download_file') }}
                                        </a>
                                    </div>
                                </div>
                            @else
                                <p class="text-gray-500 dark:text-gray-400">{{ __('frontend.system_settings.no_file') }}</p>
                            @endif
                            @break
                            
                        @default
                            <p class="text-lg text-gray-800 dark:text-gray-200 font-mono">{{ $setting->value }}</p>
                    @endswitch
                </div>
            </div>

            <!-- Help Text -->
            @if($setting->getTranslatedHelpText())
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('frontend.system_settings.help') }}
                    </h2>
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                        <p class="text-blue-800 dark:text-blue-200">
                            {{ $setting->getTranslatedHelpText() }}
                        </p>
                    </div>
                </div>
            @endif

            <!-- Technical Details -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('frontend.system_settings.technical_details') }}
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">
                            {{ __('frontend.system_settings.data_type') }}
                        </h3>
                        <p class="text-gray-900 dark:text-white">{{ ucfirst($setting->type) }}</p>
                    </div>
                    
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">
                            {{ __('frontend.system_settings.group') }}
                        </h3>
                        <p class="text-gray-900 dark:text-white">{{ ucfirst($setting->group) }}</p>
                    </div>
                    
                    @if($setting->default_value)
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">
                                {{ __('frontend.system_settings.default_value') }}
                            </h3>
                            <p class="text-gray-900 dark:text-white font-mono">{{ $setting->default_value }}</p>
                        </div>
                    @endif
                    
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">
                            {{ __('frontend.system_settings.last_updated') }}
                        </h3>
                        <p class="text-gray-900 dark:text-white">{{ $setting->updated_at->format('Y-m-d H:i:s') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Settings -->
        @if($relatedSettings->isNotEmpty())
            <div class="mt-12">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                    {{ __('frontend.system_settings.related_settings') }}
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($relatedSettings as $relatedSetting)
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                {{ $relatedSetting->getTranslatedName() }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                                {{ $relatedSetting->key }}
                            </p>
                            @if($relatedSetting->getTranslatedDescription())
                                <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">
                                    {{ Str::limit($relatedSetting->getTranslatedDescription(), 100) }}
                                </p>
                            @endif
                            <a href="{{ route('frontend.system-settings.show', $relatedSetting->key) }}" 
                               class="inline-flex items-center text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                {{ __('frontend.system_settings.view_setting') }}
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection