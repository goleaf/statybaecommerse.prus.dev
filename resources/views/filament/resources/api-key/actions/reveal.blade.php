<div class="space-y-4" x-data="{ revealed: false, value: @js($plainTextKey) }">
    <p class="text-sm text-gray-600 dark:text-gray-300">
        {{ __('api_keys.modals.reveal_description') }}
    </p>

    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                {{ __('api_keys.fields.plain_text_key') }}
            </span>
            <button
                type="button"
                class="text-xs font-medium text-primary-600 transition hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300"
                x-on:click="revealed = ! revealed"
            >
                <span x-show="! revealed">{{ __('api_keys.actions.reveal') }}</span>
                <span x-show="revealed">{{ __('api_keys.actions.hide') }}</span>
            </button>
        </div>

        <div class="mt-3 flex items-center gap-2">
            <input
                :type="revealed ? 'text' : 'password'"
                x-model="value"
                readonly
                class="block w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 font-mono text-sm tracking-wide text-gray-800 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
            />
            <button
                type="button"
                class="inline-flex items-center rounded-md border border-primary-600 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-primary-600 transition hover:bg-primary-600 hover:text-white dark:border-primary-400 dark:text-primary-400 dark:hover:bg-primary-400 dark:hover:text-gray-900"
                x-on:click="navigator.clipboard.writeText(value)"
            >
                {{ __('api_keys.actions.copy') }}
            </button>
        </div>
    </div>
</div>
