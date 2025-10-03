@section('meta')
    <x-meta
        :title="__('frontend.products.history_title', ['product' => $product->trans('name') ?? $product->name])"
        :description="__('frontend.products.history_description', ['product' => $product->trans('name') ?? $product->name])"
        :canonical="url()->current()" />
@endsection

<div class="bg-white" wire:loading.attr="aria-busy" aria-busy="false">
    <div class="pb-16 pt-10 sm:pb-24">
        <x-container class="mt-8 max-w-4xl">
            @php
                $stats = [
                    [
                        'icon' => 'heroicon-o-clock',
                        'icon_color' => 'text-indigo-500',
                        'label' => __('frontend.products.total_changes'),
                        'value' => number_format($totalChanges),
                    ],
                    [
                        'icon' => 'heroicon-o-currency-euro',
                        'icon_color' => 'text-emerald-500',
                        'label' => __('frontend.products.price_changes'),
                        'value' => number_format($priceChanges),
                    ],
                    [
                        'icon' => 'heroicon-o-cube',
                        'icon_color' => 'text-sky-500',
                        'label' => __('frontend.products.stock_updates'),
                        'value' => number_format($stockUpdates),
                    ],
                    [
                        'icon' => 'heroicon-o-calendar',
                        'icon_color' => 'text-violet-500',
                        'label' => __('frontend.products.last_change'),
                        'value' => $lastChange ? $lastChange->created_at->diffForHumans() : __('frontend.products.never'),
                    ],
                ];

                $actionStyles = [
                    'created' => ['icon' => 'heroicon-s-plus', 'classes' => 'bg-green-100 text-green-600'],
                    'updated' => ['icon' => 'heroicon-s-pencil', 'classes' => 'bg-blue-100 text-blue-600'],
                    'deleted' => ['icon' => 'heroicon-s-trash', 'classes' => 'bg-red-100 text-red-600'],
                    'price_changed' => ['icon' => 'heroicon-s-currency-euro', 'classes' => 'bg-yellow-100 text-yellow-600'],
                    'stock_updated' => ['icon' => 'heroicon-s-cube', 'classes' => 'bg-indigo-100 text-indigo-600'],
                    'status_changed' => ['icon' => 'heroicon-s-flag', 'classes' => 'bg-purple-100 text-purple-600'],
                    'default' => ['icon' => 'heroicon-s-information-circle', 'classes' => 'bg-gray-100 text-gray-600'],
                ];
            @endphp

            {{-- Breadcrumbs --}}
            <x-breadcrumbs :items="[
                [
                    'label' => __('frontend.navigation.products'),
                    'url' => route('localized.products.index', ['locale' => app()->getLocale()]),
                ],
                [
                    'label' => $product->trans('name') ?? $product->name,
                    'url' => route('localized.products.show', [
                        'locale' => app()->getLocale(),
                        'product' => $product->trans('slug') ?? $product->slug,
                    ]),
                ],
                ['label' => __('frontend.products.history')],
            ]" aria-label="{{ __('frontend.navigation.breadcrumbs') }}" />

            {{-- Header --}}
            <div class="mb-8">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                    {{ __('frontend.products.history_title', ['product' => $product->trans('name') ?? $product->name]) }}
                </h1>
                <p class="mt-2 text-lg text-gray-600">
                    {{ __('frontend.products.history_description', ['product' => $product->trans('name') ?? $product->name]) }}
                </p>
            </div>

            {{-- Product Info Card --}}
            <div class="mb-8 rounded-lg border border-gray-200 bg-gray-50 p-6">
                <div class="flex items-center space-x-4">
                    @if($product->getMainImage())
                        <img src="{{ $product->getMainImage() }}" 
                             alt="{{ $product->trans('name') ?? $product->name }}"
                             class="h-16 w-16 rounded-lg object-cover">
                    @endif
                    <div>
                        <h2 class="text-lg font-medium text-gray-900">
                            {{ $product->trans('name') ?? $product->name }}
                        </h2>
                        <p class="text-sm text-gray-600">
                            {{ __('frontend.products.sku') }}: {{ $product->sku }}
                        </p>
                        @if($product->brand)
                            <p class="text-sm text-gray-600">
                                {{ __('frontend.products.brand') }}: {{ $product->brand->trans('name') ?? $product->brand->name }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Statistics Cards --}}
            <div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($stats as $stat)
                    <div class="rounded-xl border border-gray-200 bg-white shadow-sm transition-shadow hover:shadow-md">
                        <div class="p-5">
                            <div class="flex items-center space-x-4">
                                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-50">
                                    <x-dynamic-component :component="$stat['icon']" class="h-6 w-6 {{ $stat['icon_color'] }}" />
                                </span>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ $stat['label'] }}</p>
                                    <p class="text-xl font-semibold text-gray-900">{{ $stat['value'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Filters and Controls --}}
            <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex flex-1 flex-wrap gap-4">
                        <div class="min-w-[12rem]">
                            <label for="actionFilter" class="block text-sm font-medium text-gray-700">
                                {{ __('frontend.products.filter_by_action') }}
                            </label>
                            <select wire:model.live="actionFilter"
                                    id="actionFilter"
                                    class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('frontend.products.all_actions') }}</option>
                                <option value="created">{{ __('frontend.products.events.created') }}</option>
                                <option value="updated">{{ __('frontend.products.events.updated') }}</option>
                                <option value="price_changed">{{ __('frontend.products.events.price_changed') }}</option>
                                <option value="stock_updated">{{ __('frontend.products.events.stock_updated') }}</option>
                                <option value="status_changed">{{ __('frontend.products.events.status_changed') }}</option>
                            </select>
                        </div>

                        <div class="min-w-[12rem]">
                            <label for="dateFilter" class="block text-sm font-medium text-gray-700">
                                {{ __('frontend.products.filter_by_date') }}
                            </label>
                            <select wire:model.live="dateFilter"
                                    id="dateFilter"
                                    class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('frontend.products.all_time') }}</option>
                                <option value="7">{{ __('frontend.products.last_7_days') }}</option>
                                <option value="30">{{ __('frontend.products.last_30_days') }}</option>
                                <option value="90">{{ __('frontend.products.last_90_days') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <label for="perPage" class="text-sm font-medium text-gray-700">
                                {{ __('frontend.products.per_page') }}
                            </label>
                            <select wire:model.live="perPage"
                                    id="perPage"
                                    class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>

                        <a href="/api/products/{{ $product->id }}/history/export"
                           class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <x-heroicon-o-arrow-down-tray class="mr-2 h-4 w-4" />
                            {{ __('frontend.products.export_history') }}
                        </a>
                    </div>
                </div>
            </div>

            {{-- History Timeline --}}
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">
                        {{ __('frontend.products.change_history') }}
                        @if($history->count() > 0)
                            <span class="text-sm font-normal text-gray-500">
                                ({{ $history->total() }} {{ __('frontend.products.total_entries') }})
                            </span>
                        @endif
                    </h2>
                </div>

                @if($history->count() > 0)
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @foreach($history as $index => $entry)
                                <li>
                                    <div class="relative pb-8">
                                        @if(!$loop->last)
                                            <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" 
                                                  aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            {{-- Icon --}}
                                            @php
                                                $style = $actionStyles[$entry->action] ?? $actionStyles['default'];
                                            @endphp
                                            <div>
                                                <span class="flex h-8 w-8 items-center justify-center rounded-full {{ $style['classes'] }}">
                                                    <x-dynamic-component :component="$style['icon']" class="h-4 w-4" />
                                                </span>
                                            </div>
                                            
                                            {{-- Content --}}
                                            <div class="min-w-0 flex-1">
                                                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition-shadow hover:shadow-md">
                                                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                                        <div class="flex-1">
                                                            <div class="flex flex-wrap items-center gap-2">
                                                                <h3 class="text-sm font-semibold text-gray-900">
                                                                    {{ __('frontend.products.events.' . $entry->action) }}
                                                                </h3>
                                                                @if($entry->field_name)
                                                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-800">
                                                                        {{ __('frontend.products.fields.' . $entry->field_name, [], $entry->field_name) }}
                                                                    </span>
                                                                @endif
                                                            </div>

                                                            @if($entry->description)
                                                                <p class="mt-2 text-sm text-gray-600">
                                                                    {{ $entry->description }}
                                                                </p>
                                                            @endif

                                                            <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-gray-500">
                                                                <div class="flex items-center">
                                                                    <x-heroicon-s-clock class="mr-1.5 h-4 w-4" />
                                                                    <span>{{ $entry->created_at->format('d.m.Y H:i') }}</span>
                                                                    <span class="ml-1.5 text-gray-400">({{ $entry->created_at->diffForHumans() }})</span>
                                                                </div>

                                                                @if($entry->user)
                                                                    <div class="flex items-center">
                                                                        <x-heroicon-s-user class="mr-1.5 h-4 w-4" />
                                                                        <span>{{ $entry->user->name }}</span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        @if($entry->isSignificantChange())
                                                            @php
                                                                $impact = $entry->getChangeImpact();
                                                                $impactClasses = 'bg-green-100 text-green-800';
                                                                if ($impact === 'high') {
                                                                    $impactClasses = 'bg-red-100 text-red-800';
                                                                } elseif ($impact === 'medium') {
                                                                    $impactClasses = 'bg-yellow-100 text-yellow-800';
                                                                }
                                                            @endphp
                                                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium {{ $impactClasses }}">
                                                                {{ __('frontend.products.impact.' . $impact) }}
                                                            </span>
                                                        @endif
                                                    </div>

                                                    @if($entry->old_value || $entry->new_value)
                                                        <div class="mt-4 border-t border-gray-100 pt-4">
                                                            <h4 class="mb-3 text-sm font-semibold text-gray-900">
                                                                {{ __('frontend.products.change_details') }}
                                                            </h4>
                                                            <div class="grid gap-4 sm:grid-cols-2">
                                                                @if($entry->old_value)
                                                                    <div>
                                                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                                            {{ __('frontend.products.old_value') }}
                                                                        </p>
                                                                        <div class="mt-2 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                                                                            {{ is_array($entry->old_value) ? json_encode($entry->old_value, JSON_UNESCAPED_UNICODE) : $entry->old_value }}
                                                                        </div>
                                                                    </div>
                                                                @endif

                                                                @if($entry->new_value)
                                                                    <div>
                                                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                                            {{ __('frontend.products.new_value') }}
                                                                        </p>
                                                                        <div class="mt-2 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-700">
                                                                            {{ is_array($entry->new_value) ? json_encode($entry->new_value, JSON_UNESCAPED_UNICODE) : $entry->new_value }}
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif

                                                    @if($entry->metadata && count($entry->metadata) > 0)
                                                        <div class="mt-4 border-t border-gray-100 pt-4">
                                                            <details class="group">
                                                                <summary class="flex cursor-pointer items-center text-sm font-medium text-gray-700 transition-colors hover:text-gray-900">
                                                                    <x-heroicon-s-chevron-right class="mr-1.5 h-4 w-4 transition-transform group-open:rotate-90" />
                                                                    {{ __('frontend.products.additional_info') }}
                                                                </summary>
                                                                <div class="mt-2 space-y-2 text-sm text-gray-600">
                                                                    @foreach($entry->metadata as $key => $value)
                                                                        <div class="flex justify-between gap-3">
                                                                            <span class="font-medium">{{ __('frontend.products.metadata.' . $key, [], $key) }}:</span>
                                                                            <span>{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value }}</span>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </details>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-8">
                        {{ $history->links() }}
                    </div>
                @else
                    {{-- Empty State --}}
                    <div class="text-center py-12">
                        <x-heroicon-o-clock class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900">
                            {{ __('frontend.products.no_history') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ __('frontend.products.no_history_description') }}
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('localized.products.show', [
                                'locale' => app()->getLocale(),
                                'product' => $product->trans('slug') ?? $product->slug,
                            ]) }}" 
                               class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <x-heroicon-s-arrow-left class="mr-2 h-4 w-4" />
                                {{ __('frontend.buttons.back_to_product') }}
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Back to Product Button --}}
            @if($history->count() > 0)
                <div class="mt-8 flex justify-center">
                    <a href="{{ route('localized.products.show', [
                        'locale' => app()->getLocale(),
                        'product' => $product->trans('slug') ?? $product->slug,
                    ]) }}" 
                       class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <x-heroicon-s-arrow-left class="mr-2 h-4 w-4" />
                        {{ __('frontend.buttons.back_to_product') }}
                    </a>
                </div>
            @endif
        </x-container>
    </div>
</div>

