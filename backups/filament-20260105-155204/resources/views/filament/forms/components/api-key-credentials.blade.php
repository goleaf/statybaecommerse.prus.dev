<div
    x-data="{
        open: false,
        key: '',
        secret: '',
        copiedKey: false,
        copiedSecret: false,
        copy(value, flag) {
            if (! value) {
                return;
            }

            if (navigator?.clipboard) {
                navigator.clipboard.writeText(value).then(() => {
                    this[flag] = true;
                    setTimeout(() => this[flag] = false, 2000);
                });

                return;
            }

            const textarea = document.createElement('textarea');
            textarea.value = value;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();

            try {
                document.execCommand('copy');
                this[flag] = true;
                setTimeout(() => this[flag] = false, 2000);
            } finally {
                document.body.removeChild(textarea);
            }
        },
    }"
    x-on:api-key\:show.window="open = true; key = $event.detail.key; secret = $event.detail.secret ?? ''; copiedKey = false; copiedSecret = false;"
    class="space-y-3"
>
    @php($recordExists = ($this->record?->exists ?? false))

    <div class="flex flex-wrap gap-2">
        <x-filament::button
            type="button"
            color="primary"
            icon="heroicon-m-eye"
            wire:click="revealCredentials"
            :disabled="! $recordExists"
        >
            {{ __('api_keys.actions.reveal_key') }}
        </x-filament::button>

        <x-filament::button
            type="button"
            color="warning"
            icon="heroicon-m-arrow-path"
            wire:click="regenerateCredentials"
            :disabled="! $recordExists"
        >
            {{ __('api_keys.actions.regenerate_key') }}
        </x-filament::button>
    </div>

    <p class="text-sm text-gray-500 dark:text-gray-400">
        {{ $recordExists ? __('api_keys.messages.key_modal_hint') : __('api_keys.messages.generate_after_save') }}
    </p>

    <template x-if="open">
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/60 p-4 dark:bg-gray-950/70"
        >
            <div
                class="w-full max-w-xl rounded-xl bg-white p-6 shadow-2xl ring-1 ring-gray-950/10 dark:bg-gray-900 dark:ring-white/10"
                x-on:click.outside="open = false"
                x-transition
            >
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">
                        {{ __('api_keys.modals.reveal_key.heading') }}
                    </h3>

                    <button
                        type="button"
                        class="rounded p-1 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                        x-on:click="open = false"
                    >
                        <x-filament::icon icon="heroicon-m-x-mark" class="h-5 w-5" />
                    </button>
                </div>

                <div class="mt-4 space-y-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('api_keys.fields.key') }}
                        </h4>
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <code
                                class="break-all rounded bg-gray-100 px-2 py-1 text-sm text-gray-900 dark:bg-gray-800 dark:text-gray-100"
                                x-text="key"
                            ></code>
                            <x-filament::button
                                type="button"
                                size="sm"
                                icon="heroicon-m-clipboard"
                                x-on:click="copy(key, 'copiedKey')"
                            >
                                {{ __('api_keys.actions.copy') }}
                            </x-filament::button>
                            <span
                                class="text-xs font-medium text-success-600"
                                x-show="copiedKey"
                                x-transition
                            >
                                {{ __('api_keys.messages.copied') }}
                            </span>
                        </div>
                    </div>

                    <template x-if="secret">
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('api_keys.fields.secret') }}
                            </h4>
                            <div class="mt-2 space-y-2" x-data="{ revealed: false }">
                                <div class="flex flex-wrap items-center gap-2">
                                    <code
                                        class="break-all rounded bg-gray-100 px-2 py-1 text-sm text-gray-900 dark:bg-gray-800 dark:text-gray-100"
                                        x-text="revealed ? secret : '••••••••••••••••••'"
                                    ></code>
                                    <x-filament::button
                                        type="button"
                                        size="sm"
                                        icon="heroicon-m-eye"
                                        x-on:click="revealed = ! revealed"
                                    >
                                        <span x-show="!revealed">
                                            {{ __('api_keys.actions.reveal_secret') }}
                                        </span>
                                        <span x-show="revealed">
                                            {{ __('api_keys.actions.hide_secret') }}
                                        </span>
                                    </x-filament::button>
                                    <x-filament::button
                                        type="button"
                                        size="sm"
                                        icon="heroicon-m-clipboard"
                                        x-on:click="copy(secret, 'copiedSecret')"
                                    >
                                        {{ __('api_keys.actions.copy') }}
                                    </x-filament::button>
                                    <span
                                        class="text-xs font-medium text-success-600"
                                        x-show="copiedSecret"
                                        x-transition
                                    >
                                        {{ __('api_keys.messages.copied') }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ __('api_keys.messages.secret_warning') }}
                                </p>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="mt-6 flex justify-end">
                    <x-filament::button type="button" color="gray" x-on:click="open = false">
                        {{ __('api_keys.actions.close') }}
                    </x-filament::button>
                </div>
            </div>
        </div>
    </template>
</div>
