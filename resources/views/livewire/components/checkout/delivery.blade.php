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
            @error('currentSelected')
            <div class="p-4 border-l-4 border-red-400 bg-red-50">
                <div class="flex">
                    <div class="shrink-0">
                        <svg class="size-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">
                            {{ __($message) }}
                        </p>
                    </div>
                </div>
            </div>
            @enderror

            <div class="max-w-lg mx-auto lg:max-w-none">
                <fieldset aria-label="{{ __('Delivery method') }}">
                    <div class="-space-y-px bg-white">
                        @foreach($options as $option)
                            @php
                                // Ensure array access even when legacy collections slip through.
                                $optionId = (int) data_get($option, 'id');
                                $optionName = (string) data_get($option, 'name', '');
                                $optionDescription = (string) data_get($option, 'description', '');
                                $optionPrice = (float) data_get($option, 'price', 0);
                                $formattedPrice = (string) data_get($option, 'formatted_price', \Illuminate\Support\Number::currency($optionPrice, current_currency(), app()->getLocale()));
                            @endphp
                            <label
                                aria-label="{{ $optionName }}"
                                aria-description="{{ $optionDescription }}"
                                @class([
                                    'group relative flex items-start justify-between cursor-pointer border p-4 focus:outline-none',
                                    'data-[checked]:z-10 data-[checked]:border-green-200 data-[checked]:bg-green-50 z-10 border-primary-200 bg-primary-50' => $currentSelected === $optionId,
                                    'border-gray-200' => $currentSelected !== $optionId,
                                ])
                            >
                                <span class="flex flex-1">
                                    <input
                                        type="radio"
                                        wire:model.live.debounce="currentSelected"
                                        name="shipping"
                                        value="{{ $optionId }}"
                                        class="mt-0.5 size-4 shrink-0 cursor-pointer border-gray-300 text-primary-500 focus:ring-primary-600 active:ring-2 active:ring-offset-2"
                                    >
                                    <span class="flex flex-col ml-3">
                                        <span
                                            @class([
                                                'block text-sm font-heading',
                                                'text-primary-950 font-medium' => $currentSelected === $optionId,
                                                'text-gray-600' => $currentSelected !== $optionId,
                                            ])
                                        >{{ $optionName }}</span>
                                        <span
                                            @class([
                                                'block text-sm',
                                                'text-primary-700' => $currentSelected === $optionId,
                                                'text-gray-500' => $currentSelected !== $optionId,
                                            ])
                                        >{{ $optionDescription }}</span>
                                    </span>
                                </span>
                                <span class="text-sm font-medium text-primary-950">
                                    {{ $formattedPrice }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div class="pt-6 mt-10 border-t border-gray-200 sm:flex sm:items-center sm:justify-end">
                    <x-buttons.submit
                        :title="__('Go to checkout')"
                        class="w-full px-8 py-2 text-sm sm:w-auto"
                        wire:loading.attr="data-loading"
                        wire:target="save,handleShippingAddressSelected"
                        :disabled="$isResolvingOptions"
                    />
                </div>
            </div>
        </form>
    @endif
</div>
