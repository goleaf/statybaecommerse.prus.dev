<div class="space-y-4">
    <div>
        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ __('api_keys.fields.key') }}
        </h4>
        <div class="mt-2 flex flex-wrap items-center gap-2" x-data="{ copied: false, copy(value) { navigator.clipboard?.writeText(value).then(() => { this.copied = true; setTimeout(() => this.copied = false, 2000); }); } }">
            <code class="break-all rounded bg-gray-100 px-2 py-1 text-sm text-gray-900 dark:bg-gray-800 dark:text-gray-100">{{ $key }}</code>
            <x-filament::button type="button" size="sm" icon="heroicon-m-clipboard" x-on:click="copy(@js($key))">
                {{ __('api_keys.actions.copy') }}
            </x-filament::button>
            <span class="text-xs font-medium text-success-600" x-show="copied" x-transition>
                {{ __('api_keys.messages.copied') }}
            </span>
        </div>
    </div>

    @if ($secret)
        <div>
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ __('api_keys.fields.secret') }}
            </h4>
            <div class="mt-2 space-y-2" x-data="{ revealed: false, copied: false, toggle() { this.revealed = ! this.revealed; }, copy(value) { navigator.clipboard?.writeText(value).then(() => { this.copied = true; setTimeout(() => this.copied = false, 2000); }); } }">
                <div class="flex flex-wrap items-center gap-2">
                    <code class="break-all rounded bg-gray-100 px-2 py-1 text-sm text-gray-900 dark:bg-gray-800 dark:text-gray-100" x-text="revealed ? @js($secret) : '••••••••••••••••••'"></code>
                    <x-filament::button type="button" size="sm" icon="heroicon-m-eye" x-on:click="toggle()">
                        <span x-show="! revealed">{{ __('api_keys.actions.reveal_secret') }}</span>
                        <span x-show="revealed">{{ __('api_keys.actions.hide_secret') }}</span>
                    </x-filament::button>
                    <x-filament::button type="button" size="sm" icon="heroicon-m-clipboard" x-on:click="copy(@js($secret))">
                        {{ __('api_keys.actions.copy') }}
                    </x-filament::button>
                    <span class="text-xs font-medium text-success-600" x-show="copied" x-transition>
                        {{ __('api_keys.messages.copied') }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ __('api_keys.messages.secret_warning') }}
                </p>
            </div>
        </div>
    @endif
</div>
