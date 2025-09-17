@props(['address'])

<div class="relative flex min-h-[250px] overflow-hidden justify-between border border-gray-200 bg-white px-5 py-6">
    @if ($address->type === 'billing')
        <div class="absolute top-2 right-2">
            <span
                  class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md gap-x-2 bg-primary-600 text-primary-100">
                <x-untitledui-tag class="size-4" stroke-width="1.5" aria-hidden="true" />
                {{ __('Billing') }}
            </span>
        </div>
    @endif

    <div class="flex flex-col justify-between flex-1">
        <div class="flex flex-col space-y-4">
            <h4 class="text-base font-medium text-left text-gray-900 font-heading">
                {{ $address->first_name }} {{ $address->last_name }}
            </h4>
            <p class="flex flex-col text-sm text-left text-gray-500">
                <span>
                    {{ $address->address_line_1 }}
                    @if ($address->address_line_2)
                        <span>, {{ $address->address_line_2 }}</span>
                    @endif
                </span>
                <span>
                    {{ $address->postal_code }}, {{ $address->city }}
                </span>
                <span>
                    {{ $address->country?->name }}
                </span>
            </p>
            <div class="space-y-2">
                @if ($address->type === 'shipping' && $address->is_default)
                    <div class="flex items-center gap-2 text-sm">
                        <x-heroicon-o-check class="text-gray-400 size-5" stroke-width="1.5" aria-hidden="true" />
                        <span class="text-gray-600">
                            {{ __('Default shipping address') }}
                        </span>
                    </div>
                @endif
                @if ($address->type === 'billing' && $address->is_default)
                    <div class="flex items-center gap-2 text-sm">
                        <x-heroicon-o-check class="text-gray-400 size-5" stroke-width="1.5" aria-hidden="true" />
                        <span class="text-gray-600">
                            {{ __('Default billing address') }}
                        </span>
                    </div>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-4">
            <x-buttons.danger
                              class="px-2"
                              wire:click="removeAddress({{ $address->id }})"
                              wire:confirm="{{ __('Do you really want to delete this address ?') }}">
                <x-untitledui-trash-03 class="size-5" stroke-width="1.5" aria-hidden="true" />
                <span class="sr-only">{{ __('Delete') }}</span>
            </x-buttons.danger>
            <x-buttons.default
                               class="px-2"
                               wire:click="$dispatch('openModal', { component: 'modals.account.address-form', arguments: { addressId: {{ $address->id }} }})">
                <x-untitledui-pencil-02
                                        class="size-5 text-gray-400 group-hover:text-gray-500"
                                        stroke-width="1.5"
                                        aria-hidden="true" />
                <span class="sr-only">{{ __('Edit') }}</span>
            </x-buttons.default>
        </div>
    </div>
</div>
