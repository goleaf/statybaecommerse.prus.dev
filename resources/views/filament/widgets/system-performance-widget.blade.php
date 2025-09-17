<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ $this->getHeading() }}
        </x-slot>

        <div class="space-y-6">
            {{-- System Performance Chart --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
                <div class="h-80">
                    <canvas id="system-performance-chart"></canvas>
                </div>
            </div>

            {{-- System Metrics --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Server Status</p>
                            <p class="text-2xl font-bold" id="server-status">Online</p>
                            <p class="text-green-100 text-sm" id="uptime">99.9% uptime</p>
                        </div>
                        <div class="bg-green-400 bg-opacity-30 rounded-full p-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">CPU Usage</p>
                            <p class="text-2xl font-bold" id="cpu-usage">0%</p>
                            <p class="text-blue-100 text-sm" id="cpu-temp">45°C</p>
                        </div>
                        <div class="bg-blue-400 bg-opacity-30 rounded-full p-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">Memory Usage</p>
                            <p class="text-2xl font-bold" id="memory-usage">0%</p>
                            <p class="text-purple-100 text-sm" id="memory-available">8.2 GB free</p>
                        </div>
                        <div class="bg-purple-400 bg-opacity-30 rounded-full p-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 text-sm font-medium">Disk Usage</p>
                            <p class="text-2xl font-bold" id="disk-usage">0%</p>
                            <p class="text-orange-100 text-sm" id="disk-available">250 GB free</p>
                        </div>
                        <div class="bg-orange-400 bg-opacity-30 rounded-full p-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Performance Alerts --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Performance Alerts</h3>
                <div class="space-y-3">
                    <div class="flex items-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800 dark:text-green-200">System running optimally</p>
                            <p class="text-sm text-green-600 dark:text-green-300">All services are functioning normally</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Database optimization recommended</p>
                            <p class="text-sm text-yellow-600 dark:text-yellow-300">Consider running maintenance tasks</p>
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
            
            // Update metrics with realistic values
            document.getElementById('cpu-usage').textContent = '25%';
            document.getElementById('memory-usage').textContent = '68%';
            document.getElementById('disk-usage').textContent = '45%';
            
            // Create chart
            const ctx = document.getElementById('system-performance-chart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: widgetData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'System Performance Metrics'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
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



