<div class="space-y-6">
    <header class="border-b border-gray-200 pb-5">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('frontend.account.navigation.addresses') }}</h1>
        <p class="mt-2 text-sm text-gray-600">{{ __('frontend.account.navigation.addresses_description') }}</p>
    </header>

    @php
        $fieldBaseClasses = 'mt-1 block h-11 w-full rounded-lg border bg-white px-3 text-sm text-gray-900 placeholder:text-gray-400 transition focus:outline-none focus:ring-2';
        $fieldDefaultClasses = 'border-gray-300 focus:border-primary-500 focus:ring-primary-500/25';
        $fieldErrorClasses = 'border-red-500 focus:border-red-500 focus:ring-red-500/25';
    @endphp

    <form wire:submit="saveAddress" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
        <h2 class="text-base font-semibold text-gray-900">
            {{ $editing_address_id ? __('frontend.account.addresses.edit_title') : __('frontend.account.addresses.add_new') }}
        </h2>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="first_name" class="block text-sm font-medium text-gray-700">{{ __('frontend.account.addresses.fields.first_name') }}</label>
                <input
                    id="first_name"
                    type="text"
                    wire:model.live="first_name"
                    @class([
                        $fieldBaseClasses,
                        $fieldDefaultClasses => ! $errors->has('first_name'),
                        $fieldErrorClasses => $errors->has('first_name'),
                    ])
                >
                @error('first_name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="last_name" class="block text-sm font-medium text-gray-700">{{ __('frontend.account.addresses.fields.last_name') }}</label>
                <input
                    id="last_name"
                    type="text"
                    wire:model.live="last_name"
                    @class([
                        $fieldBaseClasses,
                        $fieldDefaultClasses => ! $errors->has('last_name'),
                        $fieldErrorClasses => $errors->has('last_name'),
                    ])
                >
                @error('last_name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="address_line_1" class="block text-sm font-medium text-gray-700">{{ __('frontend.account.addresses.fields.address_line_1') }}</label>
                <input
                    id="address_line_1"
                    type="text"
                    wire:model.live="address_line_1"
                    @class([
                        $fieldBaseClasses,
                        $fieldDefaultClasses => ! $errors->has('address_line_1'),
                        $fieldErrorClasses => $errors->has('address_line_1'),
                    ])
                >
                @error('address_line_1')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="address_line_2" class="block text-sm font-medium text-gray-700">{{ __('frontend.account.addresses.fields.address_line_2') }}</label>
                <input
                    id="address_line_2"
                    type="text"
                    wire:model.live="address_line_2"
                    @class([
                        $fieldBaseClasses,
                        $fieldDefaultClasses => ! $errors->has('address_line_2'),
                        $fieldErrorClasses => $errors->has('address_line_2'),
                    ])
                >
                @error('address_line_2')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div class="sm:col-span-2">
                <label for="city" class="block text-sm font-medium text-gray-700">{{ __('frontend.account.addresses.fields.city') }}</label>
                <input
                    id="city"
                    type="text"
                    wire:model.live="city"
                    @class([
                        $fieldBaseClasses,
                        $fieldDefaultClasses => ! $errors->has('city'),
                        $fieldErrorClasses => $errors->has('city'),
                    ])
                >
                @error('city')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="postal_code" class="block text-sm font-medium text-gray-700">{{ __('frontend.account.addresses.fields.postal_code') }}</label>
                <input
                    id="postal_code"
                    type="text"
                    wire:model.live="postal_code"
                    @class([
                        $fieldBaseClasses,
                        $fieldDefaultClasses => ! $errors->has('postal_code'),
                        $fieldErrorClasses => $errors->has('postal_code'),
                    ])
                >
                @error('postal_code')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="country_code" class="block text-sm font-medium text-gray-700">{{ __('frontend.account.addresses.fields.country_code') }}</label>
                <select
                    id="country_code"
                    wire:model.live="country_code"
                    @class([
                        $fieldBaseClasses,
                        $fieldDefaultClasses => ! $errors->has('country_code'),
                        $fieldErrorClasses => $errors->has('country_code'),
                    ])
                >
                    <option value=""></option>
                    @foreach ($countries as $countryCode => $countryName)
                        <option value="{{ $countryCode }}">{{ $countryName }}</option>
                    @endforeach
                </select>
                @error('country_code')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">{{ __('frontend.account.addresses.fields.phone') }}</label>
                <input
                    id="phone"
                    type="text"
                    wire:model.live="phone"
                    @class([
                        $fieldBaseClasses,
                        $fieldDefaultClasses => ! $errors->has('phone'),
                        $fieldErrorClasses => $errors->has('phone'),
                    ])
                >
                @error('phone')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <span class="block text-sm font-medium text-gray-700">{{ __('frontend.account.addresses.type.label') }}</span>
                <div class="mt-2 flex items-center gap-6">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" value="shipping" wire:model.live="type" class="h-4 w-4 border-gray-300 text-primary-600 focus:ring-primary-500">
                        {{ __('frontend.account.addresses.type.shipping') }}
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" value="billing" wire:model.live="type" class="h-4 w-4 border-gray-300 text-primary-600 focus:ring-primary-500">
                        {{ __('frontend.account.addresses.type.billing') }}
                    </label>
                </div>
                @error('type')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" wire:model.live="set_as_default" class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                {{ __('frontend.account.addresses.set_as_default') }}
            </label>

            <div class="flex items-center gap-2">
                @if ($editing_address_id)
                    <button
                        type="button"
                        wire:click="cancelEdit"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                    >
                        {{ __('frontend.account.addresses.cancel_edit') }}
                    </button>
                @endif

                <button
                    type="submit"
                    class="inline-flex items-center rounded-md border border-transparent bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                >
                    {{ $editing_address_id ? __('frontend.account.addresses.update_address') : __('frontend.account.addresses.save_address') }}
                </button>
            </div>
        </div>
    </form>

    @if ($addresses->isNotEmpty())
        <div class="space-y-4">
            @foreach ($addresses as $address)
                @php
                    $countryCode = \Illuminate\Support\Str::upper((string) ($address->country_code ?? ''));
                    $countryName = $countries[$countryCode]
                        ?? ($address->country?->translated_name ?? null)
                        ?? ($address->country?->name ?? null)
                        ?? $countryCode;
                    $recipientName = trim((string) ($address->full_name ?? ''));
                    if ($recipientName === '') {
                        $recipientName = trim((string) (($address->first_name ?? '') . ' ' . ($address->last_name ?? '')));
                    }
                    $recipientName = $recipientName !== '' ? $recipientName : __('ui.unknown');
                @endphp
                <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="grid gap-0 lg:grid-cols-[minmax(0,1fr)_240px]">
                        <div class="p-5 sm:p-6">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $recipientName }}</h3>
                                <span class="inline-flex items-center rounded-full border border-gray-300 bg-white px-2.5 py-1 text-xs font-medium text-gray-700">
                                    {{ $address->type === 'billing' ? __('frontend.account.addresses.type.billing') : __('frontend.account.addresses.type.shipping') }}
                                </span>
                                @if ($address->is_default)
                                    <span class="inline-flex items-center rounded-full border border-primary-200 bg-primary-50 px-2.5 py-1 text-xs font-medium text-primary-700">
                                        {{ __('frontend.account.addresses.default_badge') }}
                                    </span>
                                @endif
                            </div>

                            <dl class="mt-5 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2 xl:grid-cols-3">
                                <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5">
                                    <dt class="text-xs text-gray-500">{{ __('frontend.account.addresses.fields.address_line_1') }}</dt>
                                    <dd class="mt-1 font-medium text-gray-900">{{ $address->address_line_1 }}</dd>
                                </div>

                                @if ($address->address_line_2)
                                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5">
                                        <dt class="text-xs text-gray-500">{{ __('frontend.account.addresses.fields.address_line_2') }}</dt>
                                        <dd class="mt-1 font-medium text-gray-900">{{ $address->address_line_2 }}</dd>
                                    </div>
                                @endif

                                <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5">
                                    <dt class="text-xs text-gray-500">{{ __('frontend.account.addresses.fields.city') }}</dt>
                                    <dd class="mt-1 font-medium text-gray-900">{{ $address->city }}</dd>
                                </div>

                                <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5">
                                    <dt class="text-xs text-gray-500">{{ __('frontend.account.addresses.fields.postal_code') }}</dt>
                                    <dd class="mt-1 font-medium text-gray-900">{{ $address->postal_code }}</dd>
                                </div>

                                <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5">
                                    <dt class="text-xs text-gray-500">{{ __('frontend.account.addresses.fields.country_code') }}</dt>
                                    <dd class="mt-1 font-medium text-gray-900">{{ $countryName }}</dd>
                                </div>

                                @if ($address->phone)
                                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5">
                                        <dt class="text-xs text-gray-500">{{ __('frontend.account.addresses.fields.phone') }}</dt>
                                        <dd class="mt-1 font-medium text-gray-900">{{ $address->phone }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>

                        <aside class="border-t border-gray-200 bg-gray-50 p-5 sm:p-6 lg:border-l lg:border-t-0">
                            <div class="grid gap-2 sm:grid-cols-3 lg:grid-cols-1">
                                <a
                                    href="{{ route('account.addresses.edit', ['address' => $address->id]) }}"
                                    class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700"
                                >
                                    {{ __('frontend.account.addresses.edit') }}
                                </a>

                                <button
                                    type="button"
                                    wire:click="setDefaultAddress({{ $address->id }})"
                                    @disabled($address->is_default)
                                    class="inline-flex h-10 items-center justify-center rounded-lg border border-primary-200 bg-primary-50 px-4 text-sm font-medium text-primary-700 disabled:cursor-not-allowed disabled:opacity-60"
                                >
                                    {{ __('frontend.account.addresses.set_default') }}
                                </button>

                                <button
                                    type="button"
                                    wire:click="removeAddress({{ $address->id }})"
                                    wire:confirm="{{ __('frontend.account.addresses.confirm_delete') }}"
                                    class="inline-flex h-10 items-center justify-center rounded-lg border border-red-200 bg-red-50 px-4 text-sm font-medium text-red-700"
                                >
                                    {{ __('frontend.account.addresses.remove') }}
                                </button>
                            </div>
                        </aside>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 py-12 text-center">
            <h3 class="text-sm font-medium text-gray-900">{{ __('frontend.account.addresses.empty_title') }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ __('frontend.account.addresses.empty_description') }}</p>
        </div>
    @endif
</div>
