<div>
    <div class="py-10">
        <x-container>
            <h1 class="text-2xl font-semibold mb-6">{{ __('Your cart') }}</h1>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-4">
                    @if ($items->isEmpty())
                        <p class="text-gray-500">{{ __('Your cart is empty.') }}</p>
                    @else
                        <div class="divide-y divide-gray-100 border border-gray-100 rounded-md">
                            @foreach ($items as $item)
                                <div class="flex items-center justify-between p-4">
                                    <div class="flex items-center gap-4">
                                        @php($model = $item->associatedModel)
                                        @if (method_exists($model, 'getFirstMediaUrl'))
                                            @php($thumb = $model->getFirstMediaUrl(config('shopper.media.storage.thumbnail_collection')) ?: ($model->getFirstMediaUrl(config('shopper.media.storage.collection_name'), 'small') ?: ($model->getFirstMediaUrl(config('shopper.media.storage.collection_name'), 'medium') ?: $model->getFirstMediaUrl(config('shopper.media.storage.collection_name')))))
                                            @if ($thumb)
                                                <img src="{{ $thumb }}" alt="{{ $item->name }}"
                                                     class="h-16 w-16 object-cover rounded" />
                                            @endif
                                        @endif
                                        <div>
                                            <p class="font-medium">{{ $item->name }}</p>
                                            <p class="text-sm text-gray-500">x{{ (int) $item->quantity }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-medium">
                                            {{ shopper_money_format(amount: (float) $item->price * (int) $item->quantity, currency: current_currency()) }}
                                        </p>
                                        <button wire:click="removeItem({{ (int) $item->id }})"
                                                class="text-sm text-red-600 hover:underline">{{ __('Remove') }}</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="space-y-6">
                    <div class="border border-gray-100 rounded-md p-4 bg-white">
                        @if ((string) config('shopper.features.discount') === \App\Support\FeatureState::Enabled->value)
                            <livewire:components.coupon-form />
                        @endif
                        <div class="mt-6">
                            <livewire:components.cart-total />
                        </div>
                    </div>

                    <div>
                        <a href="{{ route('checkout.index', ['locale' => app()->getLocale()]) }}"
                           class="inline-flex items-center justify-center rounded-md bg-primary-600 px-4 py-3 text-white w-full disabled:opacity-50 {{ $items->isEmpty() ? 'pointer-events-none' : '' }}">
                            {{ __('Proceed to checkout') }}
                        </a>
                    </div>
                </div>
            </div>
        </x-container>
    </div>

    <div>
        <x-container class="py-8">
            <h1 class="text-2xl font-semibold text-gray-900">{{ __('Your cart') }}</h1>

            @if ($items->isEmpty())
                <p class="mt-6 text-gray-500">{{ __('Your cart is empty.') }}</p>
                <div class="mt-6">
                    <x-link :href="route('home', ['locale' => app()->getLocale()])" class="text-primary-600">{{ __('Continue shopping') }}</x-link>
                </div>
            @else
                <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2">
                        <ul role="list" class="divide-y divide-gray-200">
                            @foreach ($items as $item)
                                <x-cart.item :item="$item" />
                            @endforeach
                        </ul>
                    </div>

                    <aside class="lg:col-span-1">
                        <div class="space-y-4 border border-gray-200 p-4 rounded-md">
                            <h2 class="text-lg font-medium text-gray-900">{{ __('Summary') }}</h2>
                            @if ((string) config('shopper.features.discount') === \App\Support\FeatureState::Enabled->value)
                                <livewire:components.coupon-form />
                            @endif
                            <livewire:components.cart-total />
                            <div class="pt-2">
                                <x-link :href="route('checkout.index', ['locale' => app()->getLocale()])"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-md">
                                    {{ __('Proceed to checkout') }}
                                </x-link>
                            </div>
                        </div>
                    </aside>
                </div>
            @endif
        </x-container>
    </div>
</div>
