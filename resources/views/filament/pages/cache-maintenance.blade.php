@php(/** @var \App\Filament\Pages\CacheMaintenance $this */ null)

<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::card>
            <div class="space-y-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Targeted Cache Controls</h2>
                    <p class="text-sm text-gray-600">
                        Use the form below to run focused cache operations before falling back to broader flushes, in line with the CachePolicy guidance.
                    </p>
                </div>
                {{ $this->form }}
                <div class="flex flex-wrap gap-3">
                    <x-filament::button color="warning" wire:click="callAction('forgetCacheKey')">
                        <x-heroicon-o-trash class="w-4 h-4" />
                        <span>{{ __('Forget Cache Key') }}</span>
                    </x-filament::button>
                    <x-filament::button color="danger" wire:click="callAction('flushCacheTags')">
                        <x-heroicon-o-tag class="w-4 h-4" />
                        <span>{{ __('Flush Cache Tags') }}</span>
                    </x-filament::button>
                </div>
            </div>
        </x-filament::card>

        @if (! empty($this->cachePerformanceSummary))
            <x-filament::card>
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Recent cache metrics</h3>
                        <p class="text-sm text-gray-600">
                            Snapshot generated from the component performance service to help validate cache impact after maintenance.
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="text-sm uppercase tracking-wide text-gray-500">Performance score</span>
                        <div class="text-3xl font-bold text-primary-600">
                            {{ $this->cachePerformanceSummary['performance_score'] ?? '—' }}
                        </div>
                    </div>
                </div>
                <dl class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-lg bg-gray-50 p-4">
                        <dt class="text-sm font-medium text-gray-500">Tracked components</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900">
                            {{ $this->cachePerformanceSummary['total_components'] ?? 0 }}
                        </dd>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-4">
                        <dt class="text-sm font-medium text-gray-500">Total renders analysed</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900">
                            {{ number_format($this->cachePerformanceSummary['total_renders'] ?? 0) }}
                        </dd>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-4">
                        <dt class="text-sm font-medium text-gray-500">Average render time (ms)</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900">
                            {{ $this->cachePerformanceSummary['avg_render_time'] ?? '—' }}
                        </dd>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-4">
                        <dt class="text-sm font-medium text-gray-500">Slowest component</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900">
                            {{ $this->cachePerformanceSummary['slowest_component'] ?? __('N/A') }}
                        </dd>
                        <p class="text-xs text-gray-500">
                            {{ __('Avg:') }} {{ $this->cachePerformanceSummary['slowest_time'] ?? '—' }} ms
                        </p>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-4">
                        <dt class="text-sm font-medium text-gray-500">Most used component</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900">
                            {{ $this->cachePerformanceSummary['most_used_component'] ?? __('N/A') }}
                        </dd>
                        <p class="text-xs text-gray-500">
                            {{ __('Renders:') }} {{ number_format($this->cachePerformanceSummary['most_used_count'] ?? 0) }}
                        </p>
                    </div>
                </dl>
            </x-filament::card>
        @endif

        @if (! empty($this->cachePolicyLinks))
            <x-filament::card>
                <h3 class="text-lg font-semibold text-gray-900">CachePolicy quick links</h3>
                <p class="mt-1 text-sm text-gray-600">
                    Review the policy before clearing shared caches to ensure mission-critical data remains warm for storefront users.
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
