@php(/** @var \App\Filament\Pages\SearchExplorer $this */ null)

<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::card>
            <form wire:submit.prevent="performSearch" class="space-y-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('Explore search results') }}</h2>
                    <p class="text-sm text-gray-600">{{ __('Run the same ranked queries exposed by the public API and inspect the aggregated buckets.') }}</p>
                </div>

                <div class="grid gap-4 md:grid-cols-6">
                    <div class="md:col-span-4">
                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="text"
                                wire:model.defer="query"
                                autocomplete="off"
                                placeholder="{{ __('Search for products, categories, or brands…') }}"
                            />
                        </x-filament::input.wrapper>
                    </div>
                    <div>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="number"
                                min="1"
                                max="{{ \App\Data\SearchQueryData::MAX_PER_PAGE }}"
                                wire:model.defer="perPage"
                                placeholder="{{ __('Per page') }}"
                            />
                        </x-filament::input.wrapper>
                    </div>
                    <div class="flex items-end">
                        <x-filament::button type="submit" color="primary" class="w-full">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                            <span>{{ __('Search') }}</span>
                        </x-filament::button>
                    </div>
                </div>
            </form>
        </x-filament::card>

        <x-filament::card>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Latest response') }}</h3>
                    <p class="text-sm text-gray-600">
                        @if (($meta['query'] ?? '') !== '')
                            {{ __('Showing :returned of :total ranked results for ":query".', ['returned' => $meta['returned'] ?? 0, 'total' => $meta['total_results'] ?? 0, 'query' => $meta['query']]) }}
                        @else
                            {{ __('Enter a query above to preview ranked search data.') }}
                        @endif
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-3 text-sm text-gray-600">
                    <div>
                        <span class="font-semibold text-gray-900">{{ __('Duration') }}</span>
                        <div>{{ ($meta['took_ms'] ?? 0) }} ms</div>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-900">{{ __('Cached') }}</span>
                        <div>{{ ($meta['cached'] ?? false) ? __('Yes') : __('No') }}</div>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-900">{{ __('Per page cap') }}</span>
                        <div>{{ \App\Data\SearchQueryData::MAX_PER_PAGE }}</div>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-900">{{ __('Page') }}</span>
                        <div>{{ $meta['page'] ?? 1 }}</div>
                    </div>
                </div>
            </div>

            <dl class="mt-4 grid gap-4 sm:grid-cols-3">
                <div class="rounded-lg bg-gray-50 p-4">
                    <dt class="text-sm font-medium text-gray-500">{{ __('Products') }}</dt>
                    <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $buckets['product'] ?? 0 }}</dd>
                </div>
                <div class="rounded-lg bg-gray-50 p-4">
                    <dt class="text-sm font-medium text-gray-500">{{ __('Categories') }}</dt>
                    <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $buckets['category'] ?? 0 }}</dd>
                </div>
                <div class="rounded-lg bg-gray-50 p-4">
                    <dt class="text-sm font-medium text-gray-500">{{ __('Brands') }}</dt>
                    <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $buckets['brand'] ?? 0 }}</dd>
                </div>
            </dl>

            <div class="mt-6 space-y-4">
                @forelse ($results as $result)
                    <div class="rounded-lg border border-gray-200 p-4 shadow-sm">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-sm uppercase tracking-wide text-gray-500">{{ ucfirst($result['type'] ?? 'result') }}</p>
                                <h4 class="text-lg font-semibold text-gray-900">{{ $result['title'] ?? '—' }}</h4>
                                @if (! empty($result['subtitle']))
                                    <p class="text-sm text-gray-600">{{ $result['subtitle'] }}</p>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">{{ __('Ranking score') }}</p>
                                <p class="text-2xl font-semibold text-primary-600">{{ number_format($result['ranking_score'] ?? 0, 2) }}</p>
                            </div>
                        </div>
                        @if (! empty($result['description']))
                            <p class="mt-3 text-sm text-gray-600">{{ \Illuminate\Support\Str::limit($result['description'], 200) }}</p>
                        @endif
                        <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-gray-500">
                            <span>{{ __('Relevance') }}: {{ $result['relevance_score'] ?? 0 }}</span>
                            @if(isset($result['url']))
                                <a href="{{ $result['url'] }}" target="_blank" rel="noreferrer" class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-700">
                                    <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" />
                                    <span>{{ __('View record') }}</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">{{ __('No results yet. Submit a query to populate this panel.') }}</p>
                @endforelse
            </div>
        </x-filament::card>
    </div>
</x-filament-panels::page>
