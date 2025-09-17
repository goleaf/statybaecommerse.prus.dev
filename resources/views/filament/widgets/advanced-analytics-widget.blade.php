<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ $this->getHeading() }}
        </x-slot>

        <div class="space-y-6">
            {{-- Chart Container --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
                <div class="h-80">
                    <canvas id="advanced-analytics-chart"></canvas>
                </div>
            </div>

            {{-- Analytics Summary --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-indigo-100 text-sm font-medium">Page Views</p>
                            <p class="text-2xl font-bold" id="page-views">0</p>
                        </div>
                        <div class="bg-indigo-400 bg-opacity-30 rounded-full p-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-pink-500 to-pink-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-pink-100 text-sm font-medium">Unique Visitors</p>
                            <p class="text-2xl font-bold" id="unique-visitors">0</p>
                        </div>
                        <div class="bg-pink-400 bg-opacity-30 rounded-full p-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-teal-500 to-teal-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-teal-100 text-sm font-medium">Bounce Rate</p>
                            <p class="text-2xl font-bold" id="bounce-rate">0%</p>
                        </div>
                        <div class="bg-teal-400 bg-opacity-30 rounded-full p-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-yellow-100 text-sm font-medium">Avg. Session</p>
                            <p class="text-2xl font-bold" id="avg-session">0m</p>
                        </div>
                        <div class="bg-yellow-400 bg-opacity-30 rounded-full p-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
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
                const uniqueVisitors = Math.floor(totalViews * 0.7); // Estimate
                const bounceRate = 45.2; // Example
                const avgSession = 3.5; // Example

                document.getElementById('page-views').textContent = totalViews.toLocaleString();
                document.getElementById('unique-visitors').textContent = uniqueVisitors.toLocaleString();
                document.getElementById('bounce-rate').textContent = bounceRate + '%';
                document.getElementById('avg-session').textContent = avgSession + 'm';

                // Create chart
                const ctx = document.getElementById('advanced-analytics-chart').getContext('2d');
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
                                text: 'Advanced Analytics Overview'
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



