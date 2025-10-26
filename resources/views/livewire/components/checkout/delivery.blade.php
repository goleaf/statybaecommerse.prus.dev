<div class="flex flex-col justify-between space-y-10">
    @include('components.checkout-steps')

    @if(count($options) === 0)
        <div class="flex items-center p-4 space-x-4 border border-gray-200">
            <x-untitledui-shopping-bag class="size-5 text-primary-800" stroke-width="1.5" aria-hidden="true" />
            <p class="text-sm text-gray-500">
                {{ __('No delivery option available for your address.') }}
            </p>
        </div>
    @else
        <form wire:submit="save" class="flex-1 space-y-3">

            <div class="max-w-lg mx-auto lg:max-w-none">
                <fieldset aria-label="{{ __('Delivery method') }}">
                    {{-- Loading placeholder keeps the layout stable while options resolve. --}}
                    <div
                        wire:loading.flex
                        wire:target="handleShippingAddressUpdated,recalculateOptions"
                        class="flex items-center gap-3 p-4 text-sm text-gray-500 border border-dashed border-primary-200"
                    >
                        <x-loading-dots aria-hidden="true" />
                        <span>{{ __('Recalculating available shipping methods...') }}</span>
                    </div>

                    <div
                        class="-space-y-px bg-white"
                        wire:loading.remove
                        wire:target="handleShippingAddressUpdated,recalculateOptions"
                    >
                        @foreach($options as $option)
                            <label
                                wire:key="shipping-{{ $option['id'] }}"
                                aria-label="{{ $option['name'] }}"
                                @class([
                                    'group relative flex items-start justify-between cursor-pointer border p-4 focus:outline-none',
                                    'data-[checked]:z-10 data-[checked]:border-green-200 data-[checked]:bg-green-50 z-10 border-primary-200 bg-primary-50' => $currentSelected === $option['id'],
                                    'border-gray-200' => $currentSelected !== $option['id'],
                                ])
                            >
                                <span class="flex flex-1">
                                    <input
                                        type="radio"
                                        wire:model.live.debounce.300ms="currentSelected"
                                        name="shipping"
                                        value="{{ $option['id'] }}"
                                        class="mt-0.5 size-4 shrink-0 cursor-pointer border-gray-300 text-primary-500 focus:ring-primary-600 active:ring-2 active:ring-offset-2"
                                    >
                                    <span class="flex flex-col ml-3">
                                        <span
                                            @class([
                                                'block text-sm font-heading',
                                                'text-primary-950 font-medium' => $currentSelected === $option['id'],
                                                'text-gray-600' => $currentSelected !== $option['id'],
                                            ])
                                        >{{ $option['name'] }}</span>
                                        <span
                                            @class([
                                                'block text-sm',
                                                'text-primary-700' => $currentSelected === $option['id'],
                                                'text-gray-500' => $currentSelected !== $option['id'],
                                            ])
                                        >{{ $option['description'] }}</span>
                                        @if(! empty($option['estimated_delivery']))
                                            <span class="text-xs text-gray-400">{{ $option['estimated_delivery'] }}</span>
                                        @endif
                                    </span>
                                </span>
                                <span class="text-sm font-medium text-primary-950">
                                    {{ $option['formatted_price'] }}
                                </span>
                            </label>
                        @endforeach
                    </div>

                    @error('currentSelected')
                        <p class="mt-2 text-sm text-red-600">{{ __($message) }}</p>
                    @enderror
                </fieldset>

                <div class="pt-6 mt-10 border-t border-gray-200 sm:flex sm:items-center sm:justify-end">
                    <x-buttons.submit
                        :title="__('Go to checkout')"
                        class="w-full px-8 py-2 text-sm sm:w-auto"
                        {{-- Disable progression while shipping rates recompute to prevent inconsistent totals. --}}
                        wire:loading.attr="disabled"
                        wire:loading.attr="data-loading"
                        wire:target="save,handleShippingAddressUpdated,recalculateOptions"
                    />
                </div>
            </div>
        </form>
    @endif
</div>
