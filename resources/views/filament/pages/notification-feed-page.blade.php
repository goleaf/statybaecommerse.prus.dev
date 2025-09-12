<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                {{ __('Live Notification Feed') }}
            </h2>
            <p class="text-gray-600 dark:text-gray-400">
                {{ __('This page demonstrates the live notification feed component. The notification bell in the top navigation provides real-time updates.') }}
            </p>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                {{ __('Notification Feed Component') }}
            </h3>
            <div class="flex justify-center">
                @livewire('live-notification-feed')
            </div>
        </div>
    </div>
</x-filament-panels::page>
