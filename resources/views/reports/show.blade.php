@extends('layouts.app')

@section('title', $report->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('reports.index') }}" 
                   class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                    </svg>
                    {{ __('reports.title') }}
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">
                        {{ $report->name }}
                    </span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <!-- Report Header -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                            {{ $report->name }}
                        </h1>
                        
                        <div class="flex items-center space-x-2 mb-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ __("admin.reports.types.{$report->type}") }}
                            </span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                {{ __("admin.reports.categories.{$report->category}") }}
                            </span>
                        </div>

                        @if($report->description)
                            <p class="text-gray-600 dark:text-gray-400 text-lg">
                                {{ $report->description }}
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Report Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $report->view_count }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('reports.stats.views') }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $report->download_count }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('reports.stats.downloads') }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $report->created_at->format('M d') }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('reports.stats.created') }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            @if($report->last_generated_at)
                                {{ $report->last_generated_at->format('M d') }}
                            @else
                                -
                            @endif
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('reports.stats.last_generated') }}</div>
                    </div>
                </div>
            </div>

            <!-- Report Content -->
            @if($report->content)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('reports.content.title') }}
                    </h2>
                    <div class="prose dark:prose-invert max-w-none">
                        {!! $report->content !!}
                    </div>
                </div>
            @endif

            <!-- Report Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('reports.actions.title') }}
                </h2>
                
                <div class="flex flex-wrap gap-4">
                    @if($report->isGenerated())
                        <a href="{{ route('reports.download', $report) }}" 
                           class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md transition duration-200 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            {{ __('reports.actions.download') }}
                        </a>
                    @endif

                    @auth
                        <form method="POST" action="{{ route('reports.generate', $report) }}" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ __('reports.actions.generate') }}
                            </button>
                        </form>
                    @endauth

                    <button onclick="window.print()" 
                            class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-md transition duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        {{ __('reports.actions.print') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Report Info -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('reports.info.title') }}
                </h3>
                
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('reports.info.type') }}
                        </dt>
                        <dd class="text-sm text-gray-900 dark:text-white">
                            {{ __("admin.reports.types.{$report->type}") }}
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('reports.info.category') }}
                        </dt>
                        <dd class="text-sm text-gray-900 dark:text-white">
                            {{ __("admin.reports.categories.{$report->category}") }}
                        </dd>
                    </div>
                    
                    @if($report->date_range)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ __('reports.info.date_range') }}
                            </dt>
                            <dd class="text-sm text-gray-900 dark:text-white">
                                {{ __("admin.reports.date_ranges.{$report->date_range}") }}
                            </dd>
                        </div>
                    @endif
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('reports.info.created') }}
                        </dt>
                        <dd class="text-sm text-gray-900 dark:text-white">
                            {{ $report->created_at->format('F d, Y') }}
                        </dd>
                    </div>
                    
                    @if($report->last_generated_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ __('reports.info.last_generated') }}
                            </dt>
                            <dd class="text-sm text-gray-900 dark:text-white">
                                {{ $report->last_generated_at->format('F d, Y H:i') }}
                            </dd>
                        </div>
                    @endif
                    
                    @if($report->generator)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ __('reports.info.generated_by') }}
                            </dt>
                            <dd class="text-sm text-gray-900 dark:text-white">
                                {{ $report->generator->name }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>

            <!-- Related Reports -->
            @if($relatedReports->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('reports.related.title') }}
                    </h3>
                    
                    <div class="space-y-4">
                        @foreach($relatedReports as $relatedReport)
                            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 last:border-b-0 last:pb-0">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                                    <a href="{{ route('reports.show', $relatedReport) }}" 
                                       class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                        {{ $relatedReport->name }}
                                    </a>
                                </h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ __("admin.reports.types.{$relatedReport->type}") }} • 
                                    {{ $relatedReport->created_at->format('M d, Y') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


