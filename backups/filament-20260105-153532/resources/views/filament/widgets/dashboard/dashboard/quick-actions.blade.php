<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ __('admin/dashboard.actions.heading') }}
        </x-slot>

        <x-slot name="description">
            {{ __('admin/dashboard.actions.description') }}
        </x-slot>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ __('admin/dashboard.actions.rebuild_search') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('admin/dashboard.actions.rebuild_search_help') }}
                        </p>
                    </div>
                    <x-heroicon-o-magnifying-glass-circle class="h-6 w-6 text-primary-500" />
                </div>
                <div class="mt-4">
                    {{ $this->rebuildSearchIndexAction }}
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ __('admin/dashboard.actions.clear_cache') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('admin/dashboard.actions.clear_cache_help') }}
                        </p>
                    </div>
                    <x-heroicon-o-trash class="h-6 w-6 text-warning-500" />
                </div>
                <div class="mt-4">
                    {{ $this->clearCacheAction }}
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ __('admin/dashboard.actions.run_minimal_seed') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('admin/dashboard.actions.run_minimal_seed_help') }}
                        </p>
                    </div>
                    <x-heroicon-o-bolt class="h-6 w-6 text-success-500" />
                </div>
                <div class="mt-4">
                    {{ $this->runMinimalSeedAction }}
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
