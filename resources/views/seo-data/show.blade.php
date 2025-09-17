@extends('layouts.app')

@section('title', $seoData->title ?: __('seo_data.seo_details'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('seo-data.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        {{ __('seo_data.title') }}
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">
                            {{ __('seo_data.seo_details') }}
                        </span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('seo_data.basic_information') }}
                </h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('seo_data.title') }}
                        </label>
                        <p class="text-gray-900 dark:text-white">
                            {{ $seoData->title ?: __('seo_data.no_title') }}
                        </p>
                        @if($seoData->title)
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                {{ __('seo_data.title_length') }}: {{ $seoData->title_length }} {{ __('seo_data.characters') }}
                                @if($seoData->isTitleOptimal)
                                    <span class="text-green-600 dark:text-green-400 ml-2">✓ {{ __('seo_data.optimal') }}</span>
                                @else
                                    <span class="text-yellow-600 dark:text-yellow-400 ml-2">⚠ {{ __('seo_data.needs_optimization') }}</span>
                                @endif
                            </p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('seo_data.description') }}
                        </label>
                        <p class="text-gray-900 dark:text-white">
                            {{ $seoData->description ?: __('seo_data.no_description') }}
                        </p>
                        @if($seoData->description)
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                {{ __('seo_data.description_length') }}: {{ $seoData->description_length }} {{ __('seo_data.characters') }}
                                @if($seoData->isDescriptionOptimal)
                                    <span class="text-green-600 dark:text-green-400 ml-2">✓ {{ __('seo_data.optimal') }}</span>
                                @else
                                    <span class="text-yellow-600 dark:text-yellow-400 ml-2">⚠ {{ __('seo_data.needs_optimization') }}</span>
                                @endif
                            </p>
                        @endif
                    </div>

                    @if($seoData->keywords)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('seo_data.keywords') }}
                            </label>
                            <div class="flex flex-wrap gap-2">
                                @foreach(explode(',', $seoData->keywords) as $keyword)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ trim($keyword) }}
                                    </span>
                                @endforeach
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                {{ __('seo_data.keywords_count') }}: {{ $seoData->keywords_count }}
                            </p>
                        </div>
                    @endif

                    @if($seoData->canonical_url)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('seo_data.canonical_url') }}
                            </label>
                            <a href="{{ $seoData->canonical_url }}" 
                               target="_blank" 
                               class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 break-all">
                                {{ $seoData->canonical_url }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Meta Tags -->
            @if($seoData->meta_tags && count($seoData->meta_tags) > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('seo_data.meta_tags') }}
                    </h2>
                    
                    <div class="space-y-3">
                        @foreach($seoData->meta_tags as $name => $content)
                            <div class="border-l-4 border-blue-500 pl-4">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $name }}</p>
                                <p class="text-gray-900 dark:text-white">{{ $content }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Structured Data -->
            @if($seoData->structured_data)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('seo_data.structured_data') }}
                    </h2>
                    
                    <pre class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg overflow-x-auto text-sm"><code>{{ json_encode($seoData->structured_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- SEO Score -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('seo_data.seo_score') }}
                </h3>
                
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-24 h-24 rounded-full text-3xl font-bold text-white mb-4 {{ $seoData->seo_score_color }}">
                        {{ $seoData->seo_score }}
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('seo_data.out_of_100') }}
                    </p>
                </div>
            </div>

            <!-- Related Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('seo_data.related_information') }}
                </h3>
                
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('seo_data.locale') }}
                        </label>
                        <p class="text-gray-900 dark:text-white">
                            {{ $seoData->locale_name }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('seo_data.type') }}
                        </label>
                        <p class="text-gray-900 dark:text-white">
                            {{ $seoData->seoable_type_name }}
                        </p>
                    </div>

                    @if($seoData->seoable)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('seo_data.related_object') }}
                            </label>
                            <p class="text-gray-900 dark:text-white">
                                {{ $seoData->seoable_name }}
                            </p>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('seo_data.robots') }}
                        </label>
                        <p class="text-gray-900 dark:text-white">
                            {{ $seoData->robots }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('seo_data.created_at') }}
                        </label>
                        <p class="text-gray-900 dark:text-white">
                            {{ $seoData->created_at->format('Y-m-d H:i:s') }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('seo_data.updated_at') }}
                        </label>
                        <p class="text-gray-900 dark:text-white">
                            {{ $seoData->updated_at->format('Y-m-d H:i:s') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('seo_data.actions') }}
                </h3>
                
                <div class="space-y-2">
                    <a href="{{ route('seo-data.index') }}" 
                       class="w-full bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-md transition duration-200 text-center block">
                        {{ __('seo_data.back_to_list') }}
                    </a>
                    
                    @if($seoData->canonical_url)
                        <a href="{{ $seoData->canonical_url }}" 
                           target="_blank" 
                           class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200 text-center block">
                            {{ __('seo_data.view_page') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

