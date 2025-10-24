@extends('layouts.minimal')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-600 rounded-full mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">Test Status Dashboard</h1>
            <p class="text-lg text-gray-600 dark:text-gray-400">Monitor your test suite execution in real-time</p>
        </div>

        @php
            $resultsPath = storage_path('app/test-results.json');
            $results = [];
            $meta = [
                'is_running' => false,
                'total' => 0,
                'completed_total' => 0,
                'current_test' => null,
                'current_index' => 0,
            ];

            if (file_exists($resultsPath)) {
                $data = json_decode(file_get_contents($resultsPath), true);
                if (is_array($data)) {
                    $meta = array_merge($meta, $data['meta'] ?? []);
                    $results = $data['tests'] ?? [];
                }
            }

            $passedCount = collect($results)->where('status', 'passed')->count();
            $failedCount = collect($results)->where('status', 'failed')->count();
            $runningCount = collect($results)->where('status', 'running')->count();
            $pendingCount = collect($results)->where('status', 'pending')->count();

            $progress = $meta['total'] > 0 ? round(($meta['completed_total'] / $meta['total']) * 100, 1) : 0;
        @endphp

        <!-- Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <!-- Total Tests -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 border-t-4 border-indigo-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Tests</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $meta['total'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Passed Tests -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 border-t-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Passed</p>
                        <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $passedCount }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Failed Tests -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 border-t-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Failed</p>
                        <p class="text-3xl font-bold text-red-600 dark:text-red-400">{{ $failedCount }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Running Tests -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 border-t-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Running</p>
                        <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ $runningCount }}</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Pending Tests -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 border-t-4 border-gray-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending</p>
                        <p class="text-3xl font-bold text-gray-600 dark:text-gray-400">{{ $pendingCount }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        @if ($meta['is_running'])
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-8">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Test Progress</h3>
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $progress }}%</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 mb-4">
                <div class="bg-indigo-600 h-4 rounded-full transition-all duration-300" style="width: {{ $progress }}%"></div>
            </div>
            @if ($meta['current_test'])
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Currently running: <span class="font-mono text-indigo-600 dark:text-indigo-400">{{ $meta['current_test'] }}</span>
                ({{ $meta['current_index'] }} / {{ $meta['total'] }})
            </p>
            @endif
        </div>
        @endif

        <!-- Test Results -->
        @if (count($results) > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Test Results</h3>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-gray-700 max-h-[600px] overflow-y-auto">
                @foreach($results as $test)
                <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3">
                                @if($test['status'] === 'passed')
                                    <span class="flex-shrink-0 w-6 h-6 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </span>
                                @elseif($test['status'] === 'failed')
                                    <span class="flex-shrink-0 w-6 h-6 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </span>
                                @elseif($test['status'] === 'running')
                                    <span class="flex-shrink-0 w-6 h-6 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                    </span>
                                @else
                                    <span class="flex-shrink-0 w-6 h-6 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </span>
                                @endif
                                <span class="font-mono text-sm text-gray-900 dark:text-white">{{ $test['id'] }}</span>
                            </div>
                            @if(isset($test['output']) && $test['output'] !== '')
                            <div class="mt-2 ml-9">
                                <details class="text-xs">
                                    <summary class="cursor-pointer text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">Show output</summary>
                                    <pre class="mt-2 p-3 bg-gray-100 dark:bg-gray-900 rounded text-gray-700 dark:text-gray-300 overflow-x-auto">{{ $test['output'] }}</pre>
                                </details>
                            </div>
                            @endif
                            @if(isset($test['error_output']) && $test['error_output'] !== '')
                            <div class="mt-2 ml-9">
                                <pre class="text-xs p-3 bg-red-50 dark:bg-red-900/20 rounded text-red-700 dark:text-red-300 overflow-x-auto">{{ $test['error_output'] }}</pre>
                            </div>
                            @endif
                        </div>
                        <div class="flex-shrink-0 ml-4 text-right">
                            @if(isset($test['last_run_duration']))
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($test['last_run_duration'], 2) }}s</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No test results available</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Run the test command to see results here</p>
            <code class="inline-block px-4 py-2 bg-gray-100 dark:bg-gray-900 rounded text-sm font-mono text-gray-700 dark:text-gray-300">php artisan project:test</code>
        </div>
        @endif

        <!-- Auto Refresh Notice -->
        @if ($meta['is_running'])
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                <svg class="inline-block w-4 h-4 mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Tests are running... Page will auto-refresh every 3 seconds
            </p>
        </div>
        <script>
            setTimeout(() => {
                window.location.reload();
            }, 3000);
        </script>
        @endif
    </div>
</div>
@endsection

