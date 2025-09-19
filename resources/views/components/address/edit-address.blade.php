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
                    {{ $address->country_code }}
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
            <button
                type="button"
                wire:click="removeAddress({{ $address->id }})"
                wire:confirm="{{ __('Do you really want to delete this address?') }}"
                class="inline-flex items-center px-2 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                </svg>
                <span class="sr-only">{{ __('Delete') }}</span>
            </button>
            <button
                type="button"
                wire:click="$dispatch('openModal', { component: 'modals.account.address-form', arguments: { addressId: {{ $address->id }} }})"
                class="inline-flex items-center px-2 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
                <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span class="sr-only">{{ __('Edit') }}</span>
            </button>
        </div>
    </div>
</div>
