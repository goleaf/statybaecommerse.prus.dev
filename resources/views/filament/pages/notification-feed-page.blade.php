<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                {{ __('admin.notification_feed_page.title') }}
            </h2>
            <p class="text-gray-600 dark:text-gray-400">
                {{ __('admin.notification_feed_page.description') }}
            </p>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                {{ __('admin.notification_feed_page.component_title') }}
            </h3>
            <div class="flex justify-center">
                @livewire('live-notification-feed')
            </div>
        </div>
    </div>
</x-filament-panels::page>
