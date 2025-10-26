{{--
    Delivery step radio list.
    Lazily fetches shipping options while providing manual refresh controls.
--}}
<div wire:init="recalculate" class="space-y-6">
    {{-- Lock interactions while shipping recalculations run to prevent duplicate calls. --}}
    <div
        class="space-y-4"
        wire:loading.class="opacity-50 pointer-events-none"
        wire:target="recalculate,save"
    >
        @forelse (($options ?? []) as $option)
            <label
                wire:key="shipping-option-{{ $option['id'] }}"
                @class([
                    'flex w-full cursor-pointer items-start gap-4 rounded-lg border p-4 shadow-sm transition-colors',
                    'border-primary-200 bg-primary-50 ring-2 ring-primary-200' => $currentSelected === $option['id'],
                    'border-gray-200 hover:border-primary-200' => $currentSelected !== $option['id'],
                ])
            >
                {{-- Radio control uses Livewire live binding so totals update immediately. --}}
                <input
                    type="radio"
                    wire:model.live="currentSelected"
                    value="{{ $option['id'] }}"
                    class="mt-1 size-4 shrink-0 cursor-pointer border-gray-300 text-primary-500 focus:ring-primary-600"
                >

                <div class="flex w-full items-start justify-between gap-6">
                    <div class="flex flex-col gap-1 text-sm">
                        <span class="font-heading text-base font-medium text-gray-900">
                            {{ $option['name'] }}
                        </span>
                        @if (! empty($option['description']))
                            <span class="text-gray-500">
                                {{ $option['description'] }}
                            </span>
                        @endif
                        @if (! empty($option['estimated_delivery']))
                            <span class="text-xs text-gray-400">
                                {{ $option['estimated_delivery'] }}
                            </span>
                        @endif
                    </div>

                    <span class="text-sm font-semibold text-gray-900">
                        {{ $option['formatted_price'] ?? money($option['price'], $option['currency'] ?? current_currency()) }}
                    </span>
                </div>
            </label>
        @empty
            {{-- Skeleton placeholders keep layout stable before options hydrate. --}}
            <div class="space-y-3">
                <div class="h-6 w-48 animate-pulse rounded bg-gray-200"></div>
                <div class="h-6 w-56 animate-pulse rounded bg-gray-200"></div>
            </div>
        @endforelse
    </div>

    @error('currentSelected')
        <p class="text-sm text-red-600">{{ __($message) }}</p>
    @enderror

    {{-- Action row exposing manual recalculation and checkout continuation. --}}
    <div class="mt-6 flex flex-wrap gap-3">
        <x-buttons.secondary
            type="button"
            wire:click="recalculate"
            wire:loading.attr="disabled"
            wire:target="recalculate,save"
        >
            {{ __('Recalculate') }}
        </x-buttons.secondary>

        <x-buttons.primary
            type="button"
            wire:click="save"
            wire:loading.attr="disabled"
            wire:target="recalculate,save"
        >
            {{ __('Continue') }}
        </x-buttons.primary>
    </div>
</div>
