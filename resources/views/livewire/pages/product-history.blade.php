@section('meta')
    <x-meta
        :title="__('frontend.products.history_title', ['product' => $product->trans('name') ?? $product->name])"
        :description="__('frontend.products.history_description', ['product' => $product->trans('name') ?? $product->name])"
        :canonical="url()->current()" />
@endsection

<div class="bg-white" wire:loading.attr="aria-busy" aria-busy="false">
    <div class="pb-16 pt-10 sm:pb-24">
        <x-container class="mt-8 max-w-4xl">
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
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-clock class="h-6 w-6 text-gray-400" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        {{ __('frontend.products.total_changes') }}
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ $totalChanges }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-currency-euro class="h-6 w-6 text-green-400" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        {{ __('frontend.products.price_changes') }}
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ $priceChanges }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-cube class="h-6 w-6 text-blue-400" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        {{ __('frontend.products.stock_updates') }}
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ $stockUpdates }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-calendar class="h-6 w-6 text-purple-400" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        {{ __('frontend.products.last_change') }}
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ $lastChange ? $lastChange->created_at->diffForHumans() : __('frontend.products.never') }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filters and Controls --}}
            <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4">
                <div class="flex flex-col space-y-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                    <div class="flex flex-col space-y-2 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4">
                        {{-- Action Filter --}}
                        <div>
                            <label for="actionFilter" class="block text-sm font-medium text-gray-700">
                                {{ __('frontend.products.filter_by_action') }}:
                            </label>
                            <select wire:model.live="actionFilter" 
                                    id="actionFilter"
                                    class="mt-1 rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('frontend.products.all_actions') }}</option>
                                <option value="created">{{ __('frontend.products.events.created') }}</option>
                                <option value="updated">{{ __('frontend.products.events.updated') }}</option>
                                <option value="price_changed">{{ __('frontend.products.events.price_changed') }}</option>
                                <option value="stock_updated">{{ __('frontend.products.events.stock_updated') }}</option>
                                <option value="status_changed">{{ __('frontend.products.events.status_changed') }}</option>
                            </select>
                        </div>

                        {{-- Date Range Filter --}}
                        <div>
                            <label for="dateFilter" class="block text-sm font-medium text-gray-700">
                                {{ __('frontend.products.filter_by_date') }}:
                            </label>
                            <select wire:model.live="dateFilter" 
                                    id="dateFilter"
                                    class="mt-1 rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('frontend.products.all_time') }}</option>
                                <option value="7">{{ __('frontend.products.last_7_days') }}</option>
                                <option value="30">{{ __('frontend.products.last_30_days') }}</option>
                                <option value="90">{{ __('frontend.products.last_90_days') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4">
                        {{-- Per Page --}}
                    <div class="flex items-center space-x-2">
                        <label for="perPage" class="text-sm font-medium text-gray-700">
                            {{ __('frontend.products.per_page') }}:
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

                        {{-- Export Button --}}
                        <a href="/api/products/{{ $product->id }}/history/export" 
                           class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
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
                                            <div>
                                                <span class="flex h-8 w-8 items-center justify-center rounded-full 
                                                    @if($entry->action === 'created') bg-green-100 text-green-600
                                                    @elseif($entry->action === 'updated') bg-blue-100 text-blue-600
                                                    @elseif($entry->action === 'deleted') bg-red-100 text-red-600
                                                    @elseif($entry->action === 'price_changed') bg-yellow-100 text-yellow-600
                                                    @elseif($entry->action === 'stock_updated') bg-indigo-100 text-indigo-600
                                                    @elseif($entry->action === 'status_changed') bg-purple-100 text-purple-600
                                                    @else bg-gray-100 text-gray-600 @endif">
                                                    @if($entry->action === 'created')
                                                        <x-heroicon-s-plus class="h-4 w-4" />
                                                    @elseif($entry->action === 'updated')
                                                        <x-heroicon-s-pencil class="h-4 w-4" />
                                                    @elseif($entry->action === 'deleted')
                                                        <x-heroicon-s-trash class="h-4 w-4" />
                                                    @elseif($entry->action === 'price_changed')
                                                        <x-heroicon-s-currency-euro class="h-4 w-4" />
                                                    @elseif($entry->action === 'stock_updated')
                                                        <x-heroicon-s-cube class="h-4 w-4" />
                                                    @elseif($entry->action === 'status_changed')
                                                        <x-heroicon-s-flag class="h-4 w-4" />
                                                    @else
                                                        <x-heroicon-s-information-circle class="h-4 w-4" />
                                                    @endif
                                                </span>
                                            </div>
                                            
                                            {{-- Content --}}
                                            <div class="min-w-0 flex-1">
                                                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm hover:shadow-md transition-shadow">
                                                    <div class="flex items-start justify-between">
                                                        <div class="flex-1">
                                                            <div class="flex items-center space-x-2">
                                                                <h3 class="text-sm font-medium text-gray-900">
                                                                    {{ __('frontend.products.events.' . $entry->action) }}
                                                                </h3>
                                                                @if($entry->field_name)
                                                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-800">
                                                                        {{ __('frontend.products.fields.' . $entry->field_name, [], $entry->field_name) }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            
                                                            @if($entry->description)
                                                                <p class="mt-1 text-sm text-gray-600">
                                                                    {{ $entry->description }}
                                                                </p>
                                                            @endif
                                                            
                                                            <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500">
                                                                <div class="flex items-center">
                                                                    <x-heroicon-s-clock class="mr-1 h-4 w-4" />
                                                                    {{ $entry->created_at->format('d.m.Y H:i') }}
                                                                    <span class="ml-1">({{ $entry->created_at->diffForHumans() }})</span>
                                                                </div>
                                                                
                                                                @if($entry->user)
                                                                    <div class="flex items-center">
                                                                        <x-heroicon-s-user class="mr-1 h-4 w-4" />
                                                                        {{ $entry->user->name }}
                                                                </div>
                                                            @endif
                                                            </div>
                                                        </div>

                                                        {{-- Impact Badge --}}
                                                        @if($entry->isSignificantChange())
                                                            <div class="ml-4">
                                                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium
                                                                    @if($entry->getChangeImpact() === 'high') bg-red-100 text-red-800
                                                                    @elseif($entry->getChangeImpact() === 'medium') bg-yellow-100 text-yellow-800
                                                                    @else bg-green-100 text-green-800 @endif">
                                                                    {{ __('frontend.products.impact.' . $entry->getChangeImpact()) }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    {{-- Change Details --}}
                                                    @if($entry->old_value || $entry->new_value)
                                                        <div class="mt-4 border-t border-gray-100 pt-4">
                                                            <h4 class="text-sm font-medium text-gray-900 mb-3">
                                                                {{ __('frontend.products.change_details') }}:
                                                            </h4>
                                                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                                                @if($entry->old_value)
                                                                    <div>
                                                                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                                                                            {{ __('frontend.products.old_value') }}
                                                                        </label>
                                                                        <div class="mt-1 text-sm text-red-600 bg-red-50 p-2 rounded border-l-4 border-red-400">
                                                                            {{ is_array($entry->old_value) ? json_encode($entry->old_value, JSON_UNESCAPED_UNICODE) : $entry->old_value }}
                                                                            </div>
                                                                                    </div>
                                                                                @endif
                                                                
                                                                @if($entry->new_value)
                                                                    <div>
                                                                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                                                                            {{ __('frontend.products.new_value') }}
                                                                        </label>
                                                                        <div class="mt-1 text-sm text-green-600 bg-green-50 p-2 rounded border-l-4 border-green-400">
                                                                            {{ is_array($entry->new_value) ? json_encode($entry->new_value, JSON_UNESCAPED_UNICODE) : $entry->new_value }}
                                                                        </div>
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    @endif

                                                    {{-- Metadata --}}
                                                    @if($entry->metadata && count($entry->metadata) > 0)
                                                        <div class="mt-4 border-t border-gray-100 pt-4">
                                                            <details class="group">
                                                                <summary class="flex cursor-pointer items-center text-sm font-medium text-gray-700 hover:text-gray-900">
                                                                    <x-heroicon-s-chevron-right class="mr-1 h-4 w-4 transform transition-transform group-open:rotate-90" />
                                                                    {{ __('frontend.products.additional_info') }}
                                                                </summary>
                                                                <div class="mt-2 text-sm text-gray-600">
                                                                    @foreach($entry->metadata as $key => $value)
                                                                        <div class="flex justify-between py-1">
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

