@section('meta')
    <x-meta
        :title="__('frontend.products.history_title', ['product' => $product->trans('name') ?? $product->name])"
        :description="__('frontend.products.history_description', ['product' => $product->trans('name') ?? $product->name])"
        :canonical="url()->current()" />
@endsection

<div class="bg-slate-50" wire:loading.attr="aria-busy" aria-busy="false">
    <div class="relative isolate overflow-hidden bg-gradient-to-b from-white via-slate-50 to-slate-100 pb-16 pt-10 sm:pb-24">
        <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-64 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-indigo-100 via-white/0 to-white/0 opacity-60"></div>

        <x-container class="mt-8 max-w-5xl space-y-10">
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
                    'created' => ['icon' => 'heroicon-s-plus', 'classes' => 'bg-emerald-100 text-emerald-600 ring-4 ring-white'],
                    'updated' => ['icon' => 'heroicon-s-pencil', 'classes' => 'bg-sky-100 text-sky-600 ring-4 ring-white'],
                    'deleted' => ['icon' => 'heroicon-s-trash', 'classes' => 'bg-rose-100 text-rose-600 ring-4 ring-white'],
                    'price_changed' => ['icon' => 'heroicon-s-currency-euro', 'classes' => 'bg-amber-100 text-amber-600 ring-4 ring-white'],
                    'stock_updated' => ['icon' => 'heroicon-s-cube', 'classes' => 'bg-indigo-100 text-indigo-600 ring-4 ring-white'],
                    'status_changed' => ['icon' => 'heroicon-s-flag', 'classes' => 'bg-purple-100 text-purple-600 ring-4 ring-white'],
                    'default' => ['icon' => 'heroicon-s-information-circle', 'classes' => 'bg-slate-100 text-slate-600 ring-4 ring-white'],
                ];

                $actionOptions = [
                    '' => __('frontend.products.all_actions'),
                    'created' => __('frontend.products.events.created'),
                    'updated' => __('frontend.products.events.updated'),
                    'price_changed' => __('frontend.products.events.price_changed'),
                    'stock_updated' => __('frontend.products.events.stock_updated'),
                    'status_changed' => __('frontend.products.events.status_changed'),
                ];

                $dateOptions = [
                    '' => __('frontend.products.all_time'),
                    '7' => __('frontend.products.last_7_days'),
                    '30' => __('frontend.products.last_30_days'),
                    '90' => __('frontend.products.last_90_days'),
                ];

                $perPageOptions = [10, 20, 50, 100];
                $hasHistory = $history->count() > 0;
            @endphp

            <x-breadcrumbs :items="[
                [
                    'label' => __('messages.frontend),
                    'url' => route('localized.products.index', ['locale' => app()->getLocale()]),
                ],
                [
                    'label' => $product->trans('name') ?? $product->name,
                    'url' => route('localized.products.show', [
                        'locale' => app()->getLocale(),
                        'product' => $product->trans('slug') ?? $product->slug,
                    ]),
                ],
                ['label' => __('messages.frontend)],
            ]" aria-label="{{ __('messages.frontend) }}" />

            <section class="rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-sm backdrop-blur">
                <div class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
                    <div class="flex flex-1 items-start gap-4">
                        @if($product->getMainImage())
                            <img src="{{ $product->getMainImage() }}"
                                 alt="{{ $product->trans('name') ?? $product->name }}"
                                 class="h-20 w-20 flex-none rounded-2xl object-cover shadow-sm" />
                        @else
                            <span class="flex h-20 w-20 flex-none items-center justify-center rounded-2xl bg-slate-100 text-slate-400 shadow-inner">
                                <x-heroicon-o-cube class="h-8 w-8" />
                            </span>
                        @endif

                        <div class="space-y-4">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-wide text-indigo-500">
                                    {{ __('messages.frontend) }}
                                </p>
                                <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                                    {{ __('frontend.products.history_title', ['product' => $product->trans('name') ?? $product->name]) }}
                                </h1>
                                <p class="mt-2 max-w-2xl text-base text-slate-600">
                                    {{ __('frontend.products.history_description', ['product' => $product->trans('name') ?? $product->name]) }}
                                </p>
                            </div>

                            <dl class="flex flex-wrap gap-x-6 gap-y-3 text-sm text-slate-600">
                                <div class="flex items-center gap-2">
                                    <dt class="font-medium text-slate-500">{{ __('messages.frontend) }}:</dt>
                                    <dd>{{ $product->sku }}</dd>
                                </div>

                                @if($product->brand)
                                    <div class="flex items-center gap-2">
                                        <dt class="font-medium text-slate-500">{{ __('messages.frontend) }}:</dt>
                                        <dd>{{ $product->brand->trans('name') ?? $product->brand->name }}</dd>
                                    </div>
                                @endif

                                @if($lastChange)
                                    <div class="flex items-center gap-2">
                                        <dt class="font-medium text-slate-500">{{ __('frontend.products.last_change') }}:</dt>
                                        <dd>{{ $lastChange->created_at->diffForHumans() }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <a href="{{ route('localized.products.show', [
                        'locale' => app()->getLocale(),
                        'product' => $product->trans('slug') ?? $product->slug,
                    ]) }}"
                       class="inline-flex items-center justify-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <x-heroicon-s-arrow-left class="h-4 w-4" />
                        {{ __('frontend.buttons.back_to_product') }}
                    </a>
                </div>
            </section>

            <section aria-labelledby="history-stats" class="space-y-6">
                <h2 id="history-stats" class="sr-only">{{ __('messages.frontend) }}</h2>

                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($stats as $stat)
                        <div class="rounded-2xl border border-slate-200 bg-white/80 p-5 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-lg">
                            <div class="flex items-center gap-4">
                                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-50">
                                    <x-dynamic-component :component="$stat['icon']" class="h-6 w-6 {{ $stat['icon_color'] }}" />
                                </span>
                                <div class="space-y-1">
                                    <p class="text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
                                    <p class="text-2xl font-semibold text-slate-900">{{ $stat['value'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section aria-labelledby="history-filters" class="rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-sm">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="grid flex-1 gap-4 sm:grid-cols-2 lg:max-w-3xl">
                        <div>
                            <label for="actionFilter" class="block text-sm font-medium text-slate-700">
                                {{ __('frontend.products.filter_by_action') }}
                            </label>
                            <select wire:model.live="actionFilter"
                                    id="actionFilter"
                                    class="mt-1 block w-full rounded-xl border-slate-200 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach($actionOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="dateFilter" class="block text-sm font-medium text-slate-700">
                                {{ __('frontend.products.filter_by_date') }}
                            </label>
                            <select wire:model.live="dateFilter"
                                    id="dateFilter"
                                    class="mt-1 block w-full rounded-xl border-slate-200 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach($dateOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-col items-stretch gap-4 sm:flex-row sm:items-center">
                        <label for="perPage" class="flex items-center gap-3 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm">
                            <span>{{ __('frontend.products.per_page') }}</span>
                            <select wire:model.live="perPage"
                                    id="perPage"
                                    class="border-0 bg-transparent p-0 text-sm font-semibold text-slate-900 focus:ring-0">
                                @foreach($perPageOptions as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        </label>

                        <a href="/api/products/{{ $product->id }}/history/export"
                           class="inline-flex items-center justify-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
                            {{ __('frontend.products.export_history') }}
                        </a>
                    </div>
                </div>
            </section>

            <section aria-labelledby="history-timeline" class="space-y-6">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <h2 id="history-timeline" class="text-2xl font-semibold text-slate-900">
                        {{ __('frontend.products.change_history') }}
                        @if($hasHistory)
                            <span class="ml-2 text-sm font-normal text-slate-500">
                                ({{ $history->total() }} {{ __('frontend.products.total_entries') }})
                            </span>
                        @endif
                    </h2>
                </div>

                @if($hasHistory)
                    <div class="flow-root">
                        <ul role="list" class="-mb-10">
                            @foreach($history as $entry)
                                @php
                                    $style = $actionStyles[$entry->action] ?? $actionStyles['default'];
                                @endphp

                                <li>
                                    <div class="relative pb-12 pl-12 sm:pl-14">
                                        @if(!$loop->last)
                                            <span class="absolute left-4 top-5 -ml-px h-full w-px bg-slate-200 sm:left-5" aria-hidden="true"></span>
                                        @endif

                                        <span class="absolute left-0 top-0 flex h-9 w-9 items-center justify-center rounded-full {{ $style['classes'] }}">
                                            <x-dynamic-component :component="$style['icon']" class="h-4 w-4" />
                                        </span>

                                        <article class="rounded-2xl border border-slate-200 bg-white/90 p-5 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-lg">
                                            <header class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                                <div class="flex flex-1 flex-wrap items-center gap-2">
                                                    <h3 class="text-base font-semibold text-slate-900">
                                                        {{ __('frontend.products.events.' . $entry->action) }}
                                                    </h3>

                                                    @if($entry->field_name)
                                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700">
                                                            {{ __('frontend.products.fields.' . $entry->field_name, [], $entry->field_name) }}
                                                        </span>
                                                    @endif

                                                    @if($entry->isSignificantChange())
                                                        @php
                                                            $impact = $entry->getChangeImpact();
                                                            $impactClasses = 'bg-emerald-100 text-emerald-800';

                                                            if ($impact === 'high') {
                                                                $impactClasses = 'bg-rose-100 text-rose-800';
                                                            } elseif ($impact === 'medium') {
                                                                $impactClasses = 'bg-amber-100 text-amber-800';
                                                            }
                                                        @endphp

                                                        <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold {{ $impactClasses }}">
                                                            {{ __('frontend.products.impact.' . $impact) }}
                                                        </span>
                                                    @endif
                                                </div>

                                                <dl class="flex flex-wrap items-center gap-4 text-sm text-slate-500">
                                                    <div class="flex items-center gap-2">
                                                        <x-heroicon-s-clock class="h-4 w-4" />
                                                        <span>{{ $entry->created_at->format('d.m.Y H:i') }}</span>
                                                        <span class="text-slate-400">({{ $entry->created_at->diffForHumans() }})</span>
                                                    </div>

                                                    @if($entry->user)
                                                        <div class="flex items-center gap-2">
                                                            <x-heroicon-s-user class="h-4 w-4" />
                                                            <span>{{ $entry->user->name }}</span>
                                                        </div>
                                                    @endif
                                                </dl>
                                            </header>

                                            @if($entry->description)
                                                <p class="mt-3 text-sm text-slate-600">
                                                    {{ $entry->description }}
                                                </p>
                                            @endif

                                            @if($entry->old_value || $entry->new_value)
                                                <div class="mt-5 space-y-4 rounded-2xl border border-slate-100 bg-slate-50/60 p-4">
                                                    <h4 class="text-sm font-semibold text-slate-900">
                                                        {{ __('frontend.products.change_details') }}
                                                    </h4>
                                                    <div class="grid gap-4 sm:grid-cols-2">
                                                        @if($entry->old_value)
                                                            <div>
                                                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                                    {{ __('frontend.products.old_value') }}
                                                                </p>
                                                                <div class="mt-2 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                                                                    <pre class="whitespace-pre-wrap break-words font-mono text-xs">{{ is_array($entry->old_value) ? json_encode($entry->old_value, JSON_UNESCAPED_UNICODE) : $entry->old_value }}</pre>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        @if($entry->new_value)
                                                            <div>
                                                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                                    {{ __('frontend.products.new_value') }}
                                                                </p>
                                                                <div class="mt-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                                                                    <pre class="whitespace-pre-wrap break-words font-mono text-xs">{{ is_array($entry->new_value) ? json_encode($entry->new_value, JSON_UNESCAPED_UNICODE) : $entry->new_value }}</pre>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif

                                            @if($entry->metadata && count($entry->metadata) > 0)
                                                <div class="mt-5 border-t border-slate-100 pt-4">
                                                    <details class="group">
                                                        <summary class="flex cursor-pointer items-center text-sm font-semibold text-slate-700 transition-colors hover:text-slate-900">
                                                            <x-heroicon-s-chevron-right class="mr-2 h-4 w-4 transition-transform group-open:rotate-90" />
                                                            {{ __('frontend.products.additional_info') }}
                                                        </summary>
                                                        <div class="mt-3 space-y-2 text-sm text-slate-600">
                                                            @foreach($entry->metadata as $key => $value)
                                                                <div class="flex justify-between gap-4 rounded-xl border border-slate-100 bg-white/60 px-3 py-2">
                                                                    <span class="font-medium text-slate-700">{{ __('frontend.products.metadata.' . $key, [], $key) }}</span>
                                                                    <span class="text-right">{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </details>
                                                </div>
                                            @endif
                                        </article>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="mt-10 flex flex-col items-center gap-6">
                        {{ $history->links() }}

                        <a href="{{ route('localized.products.show', [
                            'locale' => app()->getLocale(),
                            'product' => $product->trans('slug') ?? $product->slug,
                        ]) }}"
                           class="inline-flex items-center justify-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <x-heroicon-s-arrow-left class="h-4 w-4" />
                            {{ __('frontend.buttons.back_to_product') }}
                        </a>
                    </div>
                @else
                    <div class="rounded-3xl border border-dashed border-slate-300 bg-white/80 p-12 text-center shadow-inner">
                        <x-heroicon-o-clock class="mx-auto h-12 w-12 text-slate-400" />
                        <h3 class="mt-4 text-lg font-semibold text-slate-900">
                            {{ __('frontend.products.no_history') }}
                        </h3>
                        <p class="mt-2 text-sm text-slate-600">
                            {{ __('frontend.products.no_history_description') }}
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('localized.products.show', [
                                'locale' => app()->getLocale(),
                                'product' => $product->trans('slug') ?? $product->slug,
                            ]) }}"
                               class="inline-flex items-center justify-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <x-heroicon-s-arrow-left class="h-4 w-4" />
                                {{ __('frontend.buttons.back_to_product') }}
                            </a>
                        </div>
                    </div>
                @endif
            </section>
        </x-container>
    </div>
</div>

