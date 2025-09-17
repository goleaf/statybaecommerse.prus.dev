@extends('layouts.app')

@section('title', __('seo_data.title'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
            {{ __('seo_data.title') }}
        </h1>
        <p class="text-gray-600 dark:text-gray-400">
            {{ __('seo_data.description') }}
        </p>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('seo_data.search') }}
                </label>
                <input type="text" 
                       id="search" 
                       name="search" 
                       value="{{ request('search') }}"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                       placeholder="{{ __('seo_data.search_placeholder') }}">
            </div>

            <div>
                <label for="locale" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('seo_data.locale') }}
                </label>
                <select id="locale" 
                        name="locale" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">{{ __('seo_data.all_locales') }}</option>
                    <option value="lt" {{ request('locale') === 'lt' ? 'selected' : '' }}>{{ __('seo_data.lithuanian') }}</option>
                    <option value="en" {{ request('locale') === 'en' ? 'selected' : '' }}>{{ __('seo_data.english') }}</option>
                </select>
            </div>

            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('seo_data.type') }}
                </label>
                <select id="type" 
                        name="type" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">{{ __('seo_data.all_types') }}</option>
                    <option value="App\Models\Product" {{ request('type') === 'App\Models\Product' ? 'selected' : '' }}>{{ __('seo_data.products') }}</option>
                    <option value="App\Models\Category" {{ request('type') === 'App\Models\Category' ? 'selected' : '' }}>{{ __('seo_data.categories') }}</option>
                    <option value="App\Models\Brand" {{ request('type') === 'App\Models\Brand' ? 'selected' : '' }}>{{ __('seo_data.brands') }}</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                    {{ __('seo_data.filter') }}
                </button>
            </div>
        </form>
    </div>

    <!-- SEO Data List -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($seoData as $seo)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-200">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($seo->locale === 'lt') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 @endif">
                                {{ $seo->locale_name }}
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                {{ $seo->seoable_type_name }}
                            </span>
                        </div>
                        <div class="flex items-center space-x-1">
                            @if($seo->no_index)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    {{ __('seo_data.no_index') }}
                                </span>
                            @endif
                            @if($seo->no_follow)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                    {{ __('seo_data.no_follow') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2">
                        {{ $seo->title ?: __('seo_data.no_title') }}
                    </h3>

                    @if($seo->description)
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-3">
                            {{ $seo->description }}
                        </p>
                    @endif

                    @if($seo->keywords)
                        <div class="mb-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('seo_data.keywords') }}:</p>
                            <div class="flex flex-wrap gap-1">
                                @foreach(explode(',', $seo->keywords) as $keyword)
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                        {{ trim($keyword) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                            <span>{{ __('seo_data.seo_score') }}: 
                                <span class="font-medium {{ $seo->seo_score_color }}">
                                    {{ $seo->seo_score }}/100
                                </span>
                            </span>
                        </div>
                        <a href="{{ route('seo-data.show', $seo) }}" 
                           class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
                            {{ __('seo_data.view_details') }}
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <div class="text-gray-500 dark:text-gray-400">
                    <svg class="mx-auto h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="text-lg font-medium mb-2">{{ __('seo_data.no_data_found') }}</h3>
                    <p>{{ __('seo_data.no_data_description') }}</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($seoData->hasPages())
        <div class="mt-8">
            {{ $seoData->links() }}
        </div>
    @endif
</div>
@endsection

