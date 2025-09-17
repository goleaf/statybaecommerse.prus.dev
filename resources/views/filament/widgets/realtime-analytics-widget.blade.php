<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ $this->getHeading() }}
        </x-slot>

        <div class="space-y-6">
            {{-- Real-time Chart --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
                <div class="h-80">
                    <canvas id="realtime-analytics-chart"></canvas>
                </div>
            </div>

            {{-- Real-time Metrics --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Online Users</p>
                            <p class="text-2xl font-bold" id="online-users">0</p>
                            <p class="text-green-100 text-sm" id="user-change">+5 in last minute</p>
                        </div>
                        <div class="bg-green-400 bg-opacity-30 rounded-full p-2">
                            <div class="w-3 h-3 bg-green-300 rounded-full animate-pulse"></div>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Page Views</p>
                            <p class="text-2xl font-bold" id="page-views">0</p>
                            <p class="text-blue-100 text-sm" id="views-change">+12 in last minute</p>
                        </div>
                        <div class="bg-blue-400 bg-opacity-30 rounded-full p-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">Active Sessions</p>
                            <p class="text-2xl font-bold" id="active-sessions">0</p>
                            <p class="text-purple-100 text-sm" id="sessions-change">+3 in last minute</p>
                        </div>
                        <div class="bg-purple-400 bg-opacity-30 rounded-full p-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 text-sm font-medium">Response Time</p>
                            <p class="text-2xl font-bold" id="response-time">0ms</p>
                            <p class="text-orange-100 text-sm" id="response-change">-5ms from last check</p>
                        </div>
                        <div class="bg-orange-400 bg-opacity-30 rounded-full p-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Live Activity Feed --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Live Activity Feed</h3>
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Live</span>
                    </div>
                </div>
                <div class="space-y-3 max-h-64 overflow-y-auto">
                    <div class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">New user registered</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">2 seconds ago</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Order placed</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">5 seconds ago</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Page viewed</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">8 seconds ago</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get widget data from Livewire
            const widgetData = @json($this->getData());
            
            // Update metrics with real-time values
            let onlineUsers = 0;
            let pageViews = 0;
            let activeSessions = 0;
            let responseTime = 0;
            
            // Simulate real-time updates
            function updateMetrics() {
                onlineUsers = Math.floor(Math.random() * 50) + 20;
                pageViews = Math.floor(Math.random() * 100) + 50;
                activeSessions = Math.floor(Math.random() * 30) + 15;
                responseTime = Math.floor(Math.random() * 50) + 100;
                
                document.getElementById('online-users').textContent = onlineUsers;
                document.getElementById('page-views').textContent = pageViews;
                document.getElementById('active-sessions').textContent = activeSessions;
                document.getElementById('response-time').textContent = responseTime + 'ms';
            }
            
            // Update metrics every 5 seconds
            updateMetrics();
            setInterval(updateMetrics, 5000);
            
            // Create chart
            const ctx = document.getElementById('realtime-analytics-chart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: widgetData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 0
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Real-time Analytics'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-filament-widgets::widget>



