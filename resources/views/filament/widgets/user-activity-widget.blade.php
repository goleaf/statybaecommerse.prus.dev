<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ $this->getHeading() }}
        </x-slot>

        <div class="space-y-6">
            {{-- User Activity Chart --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
                <div class="h-80">
                    <canvas id="user-activity-chart"></canvas>
                </div>
            </div>

            {{-- User Metrics --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Total Users</p>
                            <p class="text-2xl font-bold" id="total-users">0</p>
                        </div>
                        <div class="bg-blue-400 bg-opacity-30 rounded-full p-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Active Users</p>
                            <p class="text-2xl font-bold" id="active-users">0</p>
                        </div>
                        <div class="bg-green-400 bg-opacity-30 rounded-full p-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">New Users</p>
                            <p class="text-2xl font-bold" id="new-users">0</p>
                        </div>
                        <div class="bg-purple-400 bg-opacity-30 rounded-full p-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 text-sm font-medium">Engagement</p>
                            <p class="text-2xl font-bold" id="engagement">0%</p>
                        </div>
                        <div class="bg-orange-400 bg-opacity-30 rounded-full p-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- User Activity Summary --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">User Activity Summary</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Recent Activity</h4>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900 dark:text-white">User registration</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">2 minutes ago</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <div class="w-2 h-2 bg-blue-400 rounded-full"></div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900 dark:text-white">Profile update</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">5 minutes ago</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <div class="w-2 h-2 bg-purple-400 rounded-full"></div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900 dark:text-white">Order placed</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">10 minutes ago</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">User Demographics</h4>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Mobile Users</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">65%</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Desktop Users</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">35%</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Returning Users</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">78%</span>
                            </div>
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
            
            // Update metrics
            const totalUsers = widgetData.datasets[0]?.data?.reduce((a, b) => a + b, 0) || 0;
            const activeUsers = Math.floor(totalUsers * 0.7);
            const newUsers = Math.floor(totalUsers * 0.15);
            const engagement = 85.5;
            
            document.getElementById('total-users').textContent = totalUsers.toLocaleString();
            document.getElementById('active-users').textContent = activeUsers.toLocaleString();
            document.getElementById('new-users').textContent = newUsers.toLocaleString();
            document.getElementById('engagement').textContent = engagement + '%';
            
            // Create chart
            const ctx = document.getElementById('user-activity-chart').getContext('2d');
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
                            text: 'User Activity Trends'
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



