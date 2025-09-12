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

            {{-- History Timeline --}}
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">
                        {{ __('frontend.products.change_history') }}
                    </h2>
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
                </div>

                @if($history->count() > 0)
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @foreach($history as $index => $activity)
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
                                                    @if($activity->event === 'created') bg-green-100 text-green-600
                                                    @elseif($activity->event === 'updated') bg-blue-100 text-blue-600
                                                    @elseif($activity->event === 'deleted') bg-red-100 text-red-600
                                                    @else bg-gray-100 text-gray-600 @endif">
                                                    @if($activity->event === 'created')
                                                        <x-heroicon-s-plus class="h-4 w-4" />
                                                    @elseif($activity->event === 'updated')
                                                        <x-heroicon-s-pencil class="h-4 w-4" />
                                                    @elseif($activity->event === 'deleted')
                                                        <x-heroicon-s-trash class="h-4 w-4" />
                                                    @else
                                                        <x-heroicon-s-information-circle class="h-4 w-4" />
                                                    @endif
                                                </span>
                                            </div>
                                            
                                            {{-- Content --}}
                                            <div class="min-w-0 flex-1">
                                                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex-1">
                                                            <div class="flex items-center space-x-2">
                                                                <h3 class="text-sm font-medium text-gray-900">
                                                                    {{ __('frontend.products.events.' . $activity->event) }}
                                                                </h3>
                                                                @if($activity->description)
                                                                    <span class="text-sm text-gray-500">
                                                                        - {{ $activity->description }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            
                                                            <div class="mt-1 text-sm text-gray-600">
                                                                {{ $activity->created_at->format('d.m.Y H:i') }}
                                                            </div>
                                                            
                                                            @if($activity->causer)
                                                                <div class="mt-1 text-sm text-gray-500">
                                                                    {{ __('frontend.products.changed_by') }}: 
                                                                    {{ $activity->causer->name ?? $activity->causer->email }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    {{-- Changes Details --}}
                                                    @if($activity->properties && $activity->event === 'updated')
                                                        <div class="mt-4 border-t border-gray-100 pt-4">
                                                            <h4 class="text-sm font-medium text-gray-900 mb-2">
                                                                {{ __('frontend.products.changes_made') }}:
                                                            </h4>
                                                            <div class="space-y-2">
                                                                @foreach($activity->properties->get('attributes', []) as $key => $newValue)
                                                                    @php
                                                                        $oldValue = $activity->properties->get('old', [])[$key] ?? null;
                                                                        $translatedKey = __('frontend.products.fields.' . $key, [], $key);
                                                                    @endphp
                                                                    @if($oldValue !== $newValue && !in_array($key, ['updated_at']))
                                                                        <div class="flex items-start space-x-4 text-sm">
                                                                            <div class="w-32 flex-shrink-0 font-medium text-gray-700">
                                                                                {{ $translatedKey }}:
                                                                            </div>
                                                                            <div class="flex-1">
                                                                                @if($oldValue)
                                                                                    <div class="text-red-600 line-through">
                                                                                        {{ is_array($oldValue) ? json_encode($oldValue) : $oldValue }}
                                                                                    </div>
                                                                                @endif
                                                                                @if($newValue)
                                                                                    <div class="text-green-600 font-medium">
                                                                                        {{ is_array($newValue) ? json_encode($newValue) : $newValue }}
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                            </div>
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

