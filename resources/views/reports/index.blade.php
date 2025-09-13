@extends('layouts.app')

@section('title', __('reports.title'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
            {{ __('reports.title') }}
        </h1>
        <p class="text-gray-600 dark:text-gray-400">
            {{ __('reports.description') }}
        </p>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
        <form method="GET" action="{{ route('reports.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('reports.filters.search') }}
                    </label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="{{ __('reports.filters.search_placeholder') }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                </div>

                <!-- Type Filter -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('reports.filters.type') }}
                    </label>
                    <select id="type" 
                            name="type" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">{{ __('reports.filters.all_types') }}</option>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Category Filter -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('reports.filters.category') }}
                    </label>
                    <select id="category" 
                            name="category" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">{{ __('reports.filters.all_categories') }}</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Sort -->
                <div>
                    <label for="sort" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('reports.filters.sort') }}
                    </label>
                    <select id="sort" 
                            name="sort" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>
                            {{ __('reports.filters.sort_by_date') }}
                        </option>
                        <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>
                            {{ __('reports.filters.sort_by_name') }}
                        </option>
                        <option value="view_count" {{ request('sort') == 'view_count' ? 'selected' : '' }}>
                            {{ __('reports.filters.sort_by_views') }}
                        </option>
                        <option value="download_count" {{ request('sort') == 'download_count' ? 'selected' : '' }}>
                            {{ __('reports.filters.sort_by_downloads') }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="flex justify-between items-center">
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                    {{ __('reports.filters.apply') }}
                </button>
                
                <a href="{{ route('reports.index') }}" 
                   class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                    {{ __('reports.filters.clear') }}
                </a>
            </div>
        </form>
    </div>

    <!-- Reports Grid -->
    @if($reports->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            @foreach($reports as $report)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200">
                    <div class="p-6">
                        <!-- Report Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                    <a href="{{ route('reports.show', $report) }}" 
                                       class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                        {{ $report->name }}
                                    </a>
                                </h3>
                                
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ __("admin.reports.types.{$report->type}") }}
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        {{ __("admin.reports.categories.{$report->category}") }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Report Description -->
                        @if($report->description)
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-3">
                                {{ $report->description }}
                            </p>
                        @endif

                        <!-- Report Stats -->
                        <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400 mb-4">
                            <div class="flex items-center space-x-4">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    {{ $report->view_count }}
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    {{ $report->download_count }}
                                </span>
                            </div>
                            <span>{{ $report->created_at->format('M d, Y') }}</span>
                        </div>

                        <!-- Report Actions -->
                        <div class="flex items-center justify-between">
                            <a href="{{ route('reports.show', $report) }}" 
                               class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium text-sm">
                                {{ __('reports.actions.view') }}
                            </a>
                            
                            @if($report->isGenerated())
                                <a href="{{ route('reports.download', $report) }}" 
                                   class="bg-green-600 hover:bg-green-700 text-white font-medium py-1 px-3 rounded-md text-sm transition duration-200">
                                    {{ __('reports.actions.download') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="flex justify-center">
            {{ $reports->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                {{ __('reports.empty.title') }}
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ __('reports.empty.description') }}
            </p>
        </div>
    @endif
</div>
@endsection

