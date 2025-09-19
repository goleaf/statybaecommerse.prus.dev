<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-light-bulb class="h-5 w-5" />
                Slider Optimization Recommendations
            </div>
        </x-slot>

        <div class="space-y-4">
            @if (empty($recommendations))
                <div class="text-center py-8">
                    <x-heroicon-o-check-circle class="h-12 w-12 text-success-500 mx-auto mb-4" />
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">All Good!</h3>
                    <p class="text-gray-600 dark:text-gray-400">Your sliders are optimized. No recommendations at this
                        time.</p>
                </div>
            @else
                @foreach ($recommendations as $recommendation)
                    <div
                         class="border rounded-lg p-4 {{ match ($recommendation['type']) {
                             'success' => 'border-success-200 bg-success-50 dark:border-success-800 dark:bg-success-900/20',
                             'warning' => 'border-warning-200 bg-warning-50 dark:border-warning-800 dark:bg-warning-900/20',
                             'danger' => 'border-danger-200 bg-danger-50 dark:border-danger-800 dark:bg-danger-900/20',
                             'info' => 'border-info-200 bg-info-50 dark:border-info-800 dark:bg-info-900/20',
                             default => 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800',
                         } }}">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <div
                                     class="w-8 h-8 rounded-full flex items-center justify-center {{ match ($recommendation['type']) {
                                         'success' => 'bg-success-100 text-success-600 dark:bg-success-900 dark:text-success-400',
                                         'warning' => 'bg-warning-100 text-warning-600 dark:bg-warning-900 dark:text-warning-400',
                                         'danger' => 'bg-danger-100 text-danger-600 dark:bg-danger-900 dark:text-danger-400',
                                         'info' => 'bg-info-100 text-info-600 dark:bg-info-900 dark:text-info-400',
                                         default => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                                     } }}">
                                    @if ($recommendation['icon'] === 'heroicon-o-camera')
                                        <x-heroicon-o-camera class="h-4 w-4" />
                                    @elseif($recommendation['icon'] === 'heroicon-o-cursor-arrow-rays')
                                        <x-heroicon-o-cursor-arrow-rays class="h-4 w-4" />
                                    @elseif($recommendation['icon'] === 'heroicon-o-document-text')
                                        <x-heroicon-o-document-text class="h-4 w-4" />
                                    @elseif($recommendation['icon'] === 'heroicon-o-eye-slash')
                                        <x-heroicon-o-eye-slash class="h-4 w-4" />
                                    @elseif($recommendation['icon'] === 'heroicon-o-paint-brush')
                                        <x-heroicon-o-paint-brush class="h-4 w-4" />
                                    @elseif($recommendation['icon'] === 'heroicon-o-clock')
                                        <x-heroicon-o-clock class="h-4 w-4" />
                                    @elseif($recommendation['icon'] === 'heroicon-o-check-circle')
                                        <x-heroicon-o-check-circle class="h-4 w-4" />
                                    @else
                                        <x-heroicon-o-information-circle class="h-4 w-4" />
                                    @endif
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ $recommendation['title'] }}
                                    </h4>
                                    <span
                                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ match ($recommendation['type']) {
                                              'success' => 'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200',
                                              'warning' => 'bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-200',
                                              'danger' => 'bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-200',
                                              'info' => 'bg-info-100 text-info-800 dark:bg-info-900 dark:text-info-200',
                                              default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
                                          } }}">
                                        {{ $recommendation['count'] }}
                                        slider{{ $recommendation['count'] !== 1 ? 's' : '' }}
                                    </span>
                                </div>

                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $recommendation['description'] }}
                                </p>

                                <div class="mt-2">
                                    <span
                                          class="text-xs font-medium {{ match ($recommendation['type']) {
                                              'success' => 'text-success-700 dark:text-success-300',
                                              'warning' => 'text-warning-700 dark:text-warning-300',
                                              'danger' => 'text-danger-700 dark:text-danger-300',
                                              'info' => 'text-info-700 dark:text-info-300',
                                              default => 'text-gray-700 dark:text-gray-300',
                                          } }}">
                                        💡 {{ $recommendation['action'] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        @if (!empty($recommendations))
            <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
                    <span>Total sliders analyzed: {{ $totalSliders }}</span>
                    <span>Active sliders: {{ $activeSliders }}</span>
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
