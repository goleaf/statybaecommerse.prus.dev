<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ $this->getHeading() }}
        </x-slot>

        <div class="space-y-6">
            {{-- Chart Container --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
                <div class="h-80">
                    <canvas id="campaign-performance-chart"></canvas>
                </div>
            </div>

            {{-- Performance Metrics --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Total Views</p>
                            <p class="text-2xl font-semibold text-blue-900 dark:text-blue-100" id="total-views">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-green-600 dark:text-green-400">Total Clicks</p>
                            <p class="text-2xl font-semibold text-green-900 dark:text-green-100" id="total-clicks">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-purple-600 dark:text-purple-400" fill="none"
                                 stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-purple-600 dark:text-purple-400">CTR</p>
                            <p class="text-2xl font-semibold text-purple-900 dark:text-purple-100" id="ctr">0%</p>
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
                const totalViews = widgetData.datasets[0]?.data?.reduce((a, b) => a + b, 0) || 0;
                const totalClicks = widgetData.datasets[1]?.data?.reduce((a, b) => a + b, 0) || 0;
                const ctr = totalViews > 0 ? ((totalClicks / totalViews) * 100).toFixed(2) : 0;

                document.getElementById('total-views').textContent = totalViews.toLocaleString();
                document.getElementById('total-clicks').textContent = totalClicks.toLocaleString();
                document.getElementById('ctr').textContent = ctr + '%';

                // Create chart
                const ctx = document.getElementById('campaign-performance-chart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
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
                                text: 'Campaign Performance Overview'
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



