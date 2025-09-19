<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-rectangle-stack class="h-8 w-8 text-blue-500" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('translations.total_sliders') }}</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $sliders->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-check-circle class="h-8 w-8 text-green-500" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('translations.active_sliders') }}</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ $sliders->where('is_active', true)->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-x-circle class="h-8 w-8 text-red-500" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('translations.inactive_sliders') }}</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ $sliders->where('is_active', false)->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-clock class="h-8 w-8 text-yellow-500" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('translations.recent_sliders') }}</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ $sliders->where('created_at', '>=', now()->subWeek())->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sliders Grid -->
        @if ($sliders->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($sliders as $slider)
                    <div
                         class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <!-- Slider Image -->
                        <div class="aspect-video bg-gray-100 dark:bg-gray-700 relative">
                            @if ($slider->hasImage())
                                <img src="{{ $slider->getImageUrl('slider') }}"
                                     alt="{{ $slider->title }}"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="flex items-center justify-center h-full">
                                    <x-heroicon-o-photo class="h-12 w-12 text-gray-400" />
                                </div>
                            @endif

                            <!-- Status Badge -->
                            <div class="absolute top-2 right-2">
                                @if ($slider->is_active)
                                    <span
                                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        <x-heroicon-o-check-circle class="h-3 w-3 mr-1" />
                                        {{ __('translations.active') }}
                                    </span>
                                @else
                                    <span
                                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        <x-heroicon-o-x-circle class="h-3 w-3 mr-1" />
                                        {{ __('translations.inactive') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Slider Content -->
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                {{ $slider->title }}
                            </h3>

                            @if ($slider->description)
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">
                                    {{ $slider->description }}
                                </p>
                            @endif

                            <!-- Slider Details -->
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center justify-between text-sm">
                                    <span
                                          class="text-gray-500 dark:text-gray-400">{{ __('translations.sort_order') }}:</span>
                                    <span class="font-medium">{{ $slider->sort_order }}</span>
                                </div>

                                <div class="flex items-center justify-between text-sm">
                                    <span
                                          class="text-gray-500 dark:text-gray-400">{{ __('translations.animation') }}:</span>
                                    <span class="font-medium capitalize">{{ $slider->getAnimationType() }}</span>
                                </div>

                                <div class="flex items-center justify-between text-sm">
                                    <span
                                          class="text-gray-500 dark:text-gray-400">{{ __('translations.duration') }}:</span>
                                    <span class="font-medium">{{ $slider->getDuration() }}ms</span>
                                </div>
                            </div>

                            <!-- Color Indicators -->
                            <div class="flex items-center space-x-2 mb-4">
                                <div class="flex items-center space-x-1">
                                    <div class="w-4 h-4 rounded border"
                                         style="background-color: {{ $slider->getEffectiveBackgroundColor() }}"></div>
                                    <span class="text-xs text-gray-500">{{ __('translations.background') }}</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <div class="w-4 h-4 rounded border"
                                         style="background-color: {{ $slider->getEffectiveTextColor() }}"></div>
                                    <span class="text-xs text-gray-500">{{ __('translations.text') }}</span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-between">
                                <div class="flex space-x-2">
                                    {{ $this->toggleSliderAction($slider) }}
                                    {{ $this->duplicateSliderAction($slider) }}
                                </div>

                                <div class="flex space-x-2">
                                    <a href="{{ route('filament.admin.resources.sliders.edit', $slider) }}"
                                       class="inline-flex items-center px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <x-heroicon-o-pencil class="h-3 w-3 mr-1" />
                                        {{ __('translations.edit') }}
                                    </a>

                                    {{ $this->deleteSliderAction($slider) }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <x-heroicon-o-rectangle-stack class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('translations.no_sliders') }}
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('translations.no_sliders_description') }}</p>
                <div class="mt-6">
                    {{ $this->createSliderAction() }}
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
