<div>
    <div class="py-10">
        <x-container>
            <h1 class="text-2xl font-semibold mb-6">{{ __('Your cart') }}</h1>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-4">
                    @if ($items->isEmpty())
                        <div class="text-gray-600">
                            <p class="text-gray-500">{{ __('Your cart is empty.') }}</p>
                            <div class="mt-4">
                                <x-link :href="localized_route('products.index')" class="text-primary-600">
                                    {{ __('Continue shopping') }}
                                </x-link>
                            </div>
                        </div>
                    @else
                        <div class="divide-y divide-gray-100 border border-gray-100 rounded-md bg-white">
                            @foreach ($items as $item)
                                <div class="flex items-center justify-between p-4">
                                    <div class="flex items-center gap-4 min-w-0">
                                        @php($model = $item->associatedModel)
                                        @if (method_exists($model, 'getFirstMediaUrl'))
                                            @php($thumb = $model->getFirstMediaUrl(config('media.storage.thumbnail_collection')) ?: ($model->getFirstMediaUrl(config('media.storage.collection_name'), 'small') ?: ($model->getFirstMediaUrl(config('media.storage.collection_name'), 'medium') ?: $model->getFirstMediaUrl(config('media.storage.collection_name')))))
                                            @if ($thumb)
                                                <img src="{{ $thumb }}" alt="{{ $item->name }}" class="h-16 w-16 object-cover rounded" />
                                            @endif
                                        @endif
                                        <div class="truncate">
                                            <p class="font-medium truncate">{{ $item->name }}</p>
                                            <div class="mt-1 flex items-center gap-2 text-sm text-gray-500">
                                                <button type="button" wire:click="decrementItem({{ (int) $item->id }})" class="px-2 py-1 ring-1 ring-gray-200 hover:bg-gray-50">−</button>
                                                <input type="number" min="0" step="1" value="{{ (int) $item->quantity }}"
                                                       class="w-16 text-center ring-1 ring-gray-200"
                                                       wire:change="updateItemQuantity({{ (int) $item->id }}, $event.target.value)"
                                                       inputmode="numeric" />
                                                <button type="button" wire:click="incrementItem({{ (int) $item->id }})" class="px-2 py-1 ring-1 ring-gray-200 hover:bg-gray-50">+</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-medium">
                                            {{ \Illuminate\Support\Number::currency((float) $item->price * (int) $item->quantity, current_currency(), app()->getLocale()) }}
                                        </p>
                                        <button wire:click="removeItem({{ (int) $item->id }})" 
                                                wire:confirm="{{ __('translations.confirm_remove_cart_item') }}"
                                                class="text-sm text-red-600 hover:underline">{{ __('Remove') }}</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="space-y-6">
                    <div class="border border-gray-100 rounded-md p-4 bg-white">
                        @if ((bool) (config('app-features.features.discount') ?? true))
                            <livewire:components.coupon-form />
                        @endif
                        <div class="mt-6">
                            <livewire:components.cart-total />
                        </div>
                    </div>

                    <div>
                        <a href="{{ localized_route('checkout.index') }}"
                           class="inline-flex items-center justify-center rounded-md bg-primary-600 px-4 py-3 text-white w-full disabled:opacity-50 {{ $items->isEmpty() ? 'pointer-events-none opacity-50' : '' }}"
                           @if($items->isEmpty()) aria-disabled="true" @endif>
                            {{ __('Proceed to checkout') }}
                        </a>
                    </div>
                </div>
            </div>
        </x-container>
    </div>
    
</div>
