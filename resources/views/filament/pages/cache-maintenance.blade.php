@php(/** @var \App\Filament\Pages\CacheMaintenance $this */ null)

<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::card>
            <div class="space-y-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('admin.cache_maintenance.targeted_cache_controls') }}</h2>
                    <p class="text-sm text-gray-600">
                        {{ __('admin.cache_maintenance.targeted_cache_controls_description') }}
                    </p>
                </div>
                {{ $this->form }}
                <div class="flex flex-wrap gap-3">
                    <x-filament::button color="warning" wire:click="callAction('forgetCacheKey')">
                        <x-heroicon-o-trash class="w-4 h-4" />
                        <span>{{ __('admin.cache_maintenance.forget_cache_key') }}</span>
                    </x-filament::button>
                    <x-filament::button color="danger" wire:click="callAction('flushCacheTags')">
                        <x-heroicon-o-tag class="w-4 h-4" />
                        <span>{{ __('admin.cache_maintenance.flush_cache_tags') }}</span>
                    </x-filament::button>
                </div>
            </div>
        </x-filament::card>

        @if (! empty($this->cachePolicyLinks))
            <x-filament::card>
                <h3 class="text-lg font-semibold text-gray-900">{{ __('admin.cache_maintenance.cache_policy_quick_links_heading') }}</h3>
                <p class="mt-1 text-sm text-gray-600">
                    {{ __('admin.cache_maintenance.cache_policy_quick_links_description') }}
                </p>
                <ul class="mt-4 space-y-3">
                    @foreach ($this->cachePolicyLinks as $link)
                        <li class="space-y-1">
                            <a
                                href="{{ $link['url'] }}"
                                target="_blank"
                                rel="noreferrer"
                                class="inline-flex items-center gap-2 text-sm font-medium text-primary-600 hover:text-primary-700"
                            >
                                <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                                <span>{{ $link['label'] }}</span>
                            </a>
                            @if (! empty($link['description']))
                                <p class="text-xs text-gray-500">{{ $link['description'] }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </x-filament::card>
        @endif
    </div>
</x-filament-panels::page>
