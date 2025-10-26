@php(/** @var \App\Filament\Pages\ObservabilityDashboard $this */ null)

<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::card>
            {{-- Open PR watch list surfaces engineering efforts awaiting merge readiness. --}}
            <div class="space-y-4">
                <div class="flex flex-col justify-between gap-2 md:flex-row md:items-center">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Open PR watch list</h2>
                        <p class="text-sm text-gray-600">Track pending pull requests slated for a follow-up merge.</p>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-700">
                        <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                        Watching
                    </span>
                </div>
                <div class="overflow-hidden rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">PR</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Summary</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($this->openPrWatchList as $pullRequest)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                        {{-- Link directly to GitHub when a URL is available for deeper review context. --}}
                                        @if (! empty($pullRequest['url']))
                                            <a
                                                href="{{ $pullRequest['url'] }}"
                                                target="_blank"
                                                rel="noreferrer"
                                                class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-700"
                                            >
                                                #{{ $pullRequest['number'] }}
                                                <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" />
                                            </a>
                                        @else
                                            #{{ $pullRequest['number'] }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        <span class="font-medium">{{ $pullRequest['title'] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $pullRequest['description'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-sm text-gray-500">No pull requests require additional monitoring right now.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </x-filament::card>

        <x-filament::card>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div class="rounded-lg bg-gray-50 p-4">
                    <dt class="text-sm font-medium text-gray-500">Active queues</dt>
                    <dd class="mt-2 text-3xl font-semibold text-gray-900">{{ count($this->queueDepth) }}</dd>
                    <p class="text-xs text-gray-500">Tracking {{ count($this->queueMetrics['queues'] ?? []) }} runtime counters.</p>
                </div>
                <div class="rounded-lg bg-gray-50 p-4">
                    <dt class="text-sm font-medium text-gray-500">Queued jobs</dt>
                    <dd class="mt-2 text-3xl font-semibold text-gray-900">
                        {{ number_format(collect($this->queueDepth)->sum('size')) }}
                    </dd>
                    <p class="text-xs text-gray-500">Live depth across all configured queues.</p>
                </div>
                <div class="rounded-lg bg-gray-50 p-4">
                    <dt class="text-sm font-medium text-gray-500">Failed jobs (table)</dt>
                    <dd class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($this->queueMetrics['failed_jobs_table'] ?? 0) }}</dd>
                    <p class="text-xs text-gray-500">Count of records stored in the <code>failed_jobs</code> table.</p>
                </div>
                <div class="rounded-lg bg-gray-50 p-4">
                    <dt class="text-sm font-medium text-gray-500">Runtime failures</dt>
                    <dd class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($this->queueMetrics['total_failed'] ?? 0) }}</dd>
                    <p class="text-xs text-gray-500">Captured from queue events since the last deployment.</p>
                </div>
                <div class="rounded-lg bg-gray-50 p-4">
                    <dt class="text-sm font-medium text-gray-500">Cache hit rate</dt>
                    <dd class="mt-2 text-3xl font-semibold text-gray-900">
                        {{ number_format(($this->cacheMetrics['hit_rate'] ?? 0) * 100, 2) }}%
                    </dd>
                    <p class="text-xs text-gray-500">{{ number_format($this->cacheMetrics['hits'] ?? 0) }} hits / {{ number_format($this->cacheMetrics['misses'] ?? 0) }} misses.</p>
                </div>
                <div class="rounded-lg bg-gray-50 p-4">
                    <dt class="text-sm font-medium text-gray-500">Telemetry sampling</dt>
                    <dd class="mt-2 text-3xl font-semibold text-gray-900">
                        {{ number_format(config('observability.tracing.sampler_ratio', 1) * 100, 1) }}%
                    </dd>
                    <p class="text-xs text-gray-500">Configured OTLP endpoint: {{ config('observability.tracing.otlp.endpoint') }}</p>
                </div>
            </div>
        </x-filament::card>

        <x-filament::card>
            <div class="space-y-4">
                <div class="flex flex-col justify-between gap-2 md:flex-row md:items-center">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Queue depth</h2>
                        <p class="text-sm text-gray-600">Live snapshot of pending jobs across connections.</p>
                    </div>
                </div>
                <div class="overflow-hidden rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Connection</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Queue</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Depth</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($this->queueDepth as $row)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $row['connection'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $row['queue'] }}</td>
                                    <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">{{ number_format($row['size']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-sm text-gray-500">No queue connections configured.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </x-filament::card>

        <x-filament::card>
            <div class="space-y-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Queue reliability</h2>
                    <p class="text-sm text-gray-600">Throughput, failures, and the most recent exception details.</p>
                </div>
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div class="overflow-hidden rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Queue</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Processed</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Failed</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse ($this->queueMetrics['queues'] ?? [] as $queue)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $queue['connection'] }}:{{ $queue['queue'] }}</td>
                                        <td class="px-4 py-3 text-right text-sm text-gray-900">{{ number_format($queue['processed']) }}</td>
                                        <td class="px-4 py-3 text-right text-sm {{ ($queue['failed'] ?? 0) > 0 ? 'text-danger-600 font-semibold' : 'text-gray-900' }}">
                                            {{ number_format($queue['failed']) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-3 text-sm text-gray-500">No runtime queue metrics recorded yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="space-y-3">
                        <div class="rounded-lg bg-gray-50 p-4">
                            <dt class="text-sm font-medium text-gray-500">Last failure</dt>
                            @if (! empty($this->queueMetrics['last_failure']))
                                <dd class="mt-2 text-sm text-gray-900">
                                    <span class="font-semibold">{{ $this->queueMetrics['last_failure']['job'] ?? 'Unknown job' }}</span>
                                    failed on <span class="font-semibold">{{ $this->queueMetrics['last_failure']['connection'] ?? 'n/a' }}:{{ $this->queueMetrics['last_failure']['queue'] ?? 'n/a' }}</span>
                                    at {{ $this->queueMetrics['last_failure']['failed_at'] ?? 'unknown time' }}.
                                </dd>
                                <p class="mt-2 text-xs text-gray-500">{{ $this->queueMetrics['last_failure']['exception'] ?? 'No exception message captured.' }}</p>
                            @else
                                <dd class="mt-2 text-sm text-gray-500">No failures recorded since boot.</dd>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::card>

        <x-filament::card>
            <div class="space-y-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Cache stores</h2>
                    <p class="text-sm text-gray-600">Hit ratios and volume per configured cache store.</p>
                </div>
                <div class="overflow-hidden rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Store</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Hits</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Misses</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Hit rate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($this->cacheMetrics['stores'] ?? [] as $store)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $store['store'] }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-900">{{ number_format($store['hits']) }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-900">{{ number_format($store['misses']) }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-900">{{ number_format($store['hit_rate'] * 100, 2) }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-sm text-gray-500">Cache instrumentation has not captured any activity yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </x-filament::card>

        <x-filament::card>
            <div class="space-y-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Prometheus preview</h2>
                    <p class="text-sm text-gray-600">Raw exposition text served at <code>/metrics</code>.</p>
                </div>
                <pre class="overflow-x-auto rounded-lg bg-gray-900 p-4 text-xs leading-relaxed text-gray-100">{{ $prometheusPreview }}</pre>
            </div>
        </x-filament::card>
    </div>
</x-filament-panels::page>
