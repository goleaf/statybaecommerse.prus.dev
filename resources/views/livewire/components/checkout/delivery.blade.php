{{--
    Delivery step: renders resolved shipping options with loading states and discount badges.
--}}
<div class="space-y-6">
    {{-- Skeleton loader surfaced while Livewire fetches updated options --}}
    <div
        wire:loading.flex
        wire:target="resolveOptions"
        class="space-y-3"
        role="status"
        aria-live="polite"
    >
        @foreach(range(1, 3) as $placeholder)
            <div class="flex items-center justify-between gap-4 rounded border border-primary-100 bg-primary-50/80 p-4">
                <div class="flex items-start gap-3">
                    <span class="mt-1 size-4 rounded-full border border-primary-200 bg-white"></span>
                    <span class="space-y-2">
                        <span class="block h-3 w-32 animate-pulse rounded bg-primary-100"></span>
                        <span class="block h-3 w-48 animate-pulse rounded bg-primary-100"></span>
                    </span>
                </div>
                <span class="block h-3 w-16 animate-pulse rounded bg-primary-100"></span>
            </div>
        @endforeach

        <p class="text-xs text-center text-primary-700">
            {{ __('Recalculating delivery options…') }}
        </p>
    </div>

    {{-- No data state once resolver completes --}}
    @if(! $isResolving && count($options) === 0)
        <div class="flex items-center gap-3 rounded border border-amber-200 bg-amber-50 p-4" role="alert">
            <x-untitledui-truck class="size-5 text-amber-600" stroke-width="1.5" aria-hidden="true" />
            <p class="text-sm text-amber-800">
                {{ __('No delivery option available for your address. Please confirm your details or contact support.') }}
            </p>
        </div>
    @endif

    @if(count($options) > 0)
        <form wire:submit="save" class="flex-1 space-y-3" aria-busy="{{ $isResolving ? 'true' : 'false' }}">
            @if($isResolving)
                <div
                    class="flex items-center gap-3 rounded border border-primary-200 bg-primary-50 p-3 text-sm text-primary-700"
                    role="status"
                    aria-live="polite"
                >
                    <svg class="size-4 animate-spin text-primary-500" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-90" d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4" stroke-linecap="round" />
                    </svg>
                    <span>{{ __('Recalculating delivery options…') }}</span>
                </div>
            @endif

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
                <fieldset aria-label="{{ __('Delivery method') }}" aria-busy="{{ $isResolving ? 'true' : 'false' }}">
                    <div
                        class="-space-y-px bg-white"
                        @class([
                            'pointer-events-none opacity-60' => $isResolving,
                        ])
                    >
                        @foreach($options as $option)
                            <label
                                wire:key="delivery-option-{{ $option['id'] }}"
                                aria-label="{{ $option['name'] }}"
                                aria-description="{{ $option['description'] }}"
                                @class([
                                    'group relative flex items-start justify-between cursor-pointer border p-4 focus:outline-none',
                                    'data-[checked]:z-10 data-[checked]:border-green-200 data-[checked]:bg-green-50 z-10 border-primary-200 bg-primary-50' => (int) $currentSelected === (int) $option['id'],
                                    'border-gray-200' => (int) $currentSelected !== (int) $option['id'],
                                ])
                            >
                                <span class="flex flex-1">
                                    <input
                                        type="radio"
                                        wire:model.live="currentSelected"
                                        wire:click="selectOption({{ $option['id'] }})"
                                        name="shipping"
                                        value="{{ $option['id'] }}"
                                        @disabled($isResolving)
                                        class="mt-0.5 size-4 shrink-0 cursor-pointer border-gray-300 text-primary-500 focus:ring-primary-600 active:ring-2 active:ring-offset-2"
                                    >
                                    <span class="flex flex-col ml-3">
                                    <span
                                        @class([
                                            'block text-sm font-heading',
                                            'text-primary-950 font-medium' => (int) $currentSelected === (int) $option['id'],
                                            'text-gray-600' => (int) $currentSelected !== (int) $option['id'],
                                        ])
                                    >{{ $option['name'] }}</span>
                                        @if(! empty($option['description']))
                                            <span
                                                class="block text-sm text-gray-500"
                                            >{{ $option['description'] }}</span>
                                        @endif
                                        @if(! empty($option['estimated_delivery']))
                                            <span class="mt-1 text-xs text-gray-400">
                                                {{ $option['estimated_delivery'] }}
                                            </span>
                                        @endif
                                        @if(! empty($option['badges']) && is_array($option['badges']))
                                            <span class="mt-2 flex flex-wrap gap-2">
                                                @foreach($option['badges'] as $badge)
                                                    @if(is_array($badge) && ! empty($badge['label']))
                                                        <span
                                                            @class([
                                                                'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                                                                'bg-green-100 text-green-800' => ($badge['type'] ?? null) === 'free',
                                                                'bg-primary-100 text-primary-800' => ($badge['type'] ?? null) !== 'free',
                                                            ])
                                                        >
                                                            {{ $badge['label'] }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </span>
                                        @endif
                                    </span>
                                </span>
                                <span class="flex flex-col items-end text-right">
                                    @if(($option['original_price'] ?? null) !== null && $option['original_price'] > $option['price'])
                                        <span class="text-xs text-gray-400 line-through">
                                            {{ app_money_format($option['original_price'], $option['currency_code'] ?? current_currency()) }}
                                        </span>
                                    @endif
                                    <span class="text-sm font-semibold text-primary-950" aria-live="polite">
                                        {{ $option['formatted_price'] ?? \Illuminate\Support\Number::currency($option['price'], $option['currency_code'], app()->getLocale()) }}
                                    </span>
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
                        wire:loading.attr="disabled"
                        wire:loading.attr="aria-disabled"
                        wire:target="save,resolveOptions"
                        :disabled="$isResolving"
                    />
                </div>
            </div>
        </form>
    @endif
</div>
