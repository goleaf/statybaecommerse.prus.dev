<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ __('messages.translations) }}
        </x-slot>

        <x-slot name="description">
            {{ __('messages.translations) }}
        </x-slot>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Create New Slider -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ __('messages.translations) }}
                    </h3>
                    <x-heroicon-o-plus class="h-5 w-5 text-blue-500" />
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    {{ __('messages.translations) }}
                </p>
                {{ $this->createSliderAction }}
            </div>

            <!-- Toggle All Sliders -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ __('messages.translations) }}
                    </h3>
                    <x-heroicon-o-power class="h-5 w-5 text-yellow-500" />
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    {{ __('messages.translations) }}
                </p>
                {{ $this->toggleAllSlidersAction }}
            </div>

            <!-- Reorder Sliders -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ __('messages.translations) }}
                    </h3>
                    <x-heroicon-o-arrows-up-down class="h-5 w-5 text-green-500" />
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    {{ __('messages.translations) }}
                </p>
                {{ $this->reorderSlidersAction }}
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
