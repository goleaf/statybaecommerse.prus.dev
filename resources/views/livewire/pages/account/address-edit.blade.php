<div class="space-y-6">
    <header class="border-b border-gray-200 pb-5">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('frontend.account.addresses.edit_title') }}</h1>
        <p class="mt-2 text-sm text-gray-600">{{ __('frontend.account.navigation.addresses_description') }}</p>
    </header>

    @php
        $fieldBaseClasses = 'mt-1 block h-11 w-full rounded-lg border bg-white px-3 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2';
        $fieldDefaultClasses = 'border-gray-300 focus:border-primary-500 focus:ring-primary-500/25';
        $fieldErrorClasses = 'border-red-500 focus:border-red-500 focus:ring-red-500/25';
    @endphp

    <form wire:submit="updateAddress" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="first_name" class="block text-sm font-medium text-gray-700">{{ __('frontend.account.addresses.fields.first_name') }}</label>
                <input id="first_name" type="text" wire:model.live="first_name" @class([$fieldBaseClasses, $fieldDefaultClasses => ! $errors->has('first_name'), $fieldErrorClasses => $errors->has('first_name')])>
                @error('first_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="last_name" class="block text-sm font-medium text-gray-700">{{ __('frontend.account.addresses.fields.last_name') }}</label>
                <input id="last_name" type="text" wire:model.live="last_name" @class([$fieldBaseClasses, $fieldDefaultClasses => ! $errors->has('last_name'), $fieldErrorClasses => $errors->has('last_name')])>
                @error('last_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="address_line_1" class="block text-sm font-medium text-gray-700">{{ __('frontend.account.addresses.fields.address_line_1') }}</label>
                <input id="address_line_1" type="text" wire:model.live="address_line_1" @class([$fieldBaseClasses, $fieldDefaultClasses => ! $errors->has('address_line_1'), $fieldErrorClasses => $errors->has('address_line_1')])>
                @error('address_line_1') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="address_line_2" class="block text-sm font-medium text-gray-700">{{ __('frontend.account.addresses.fields.address_line_2') }}</label>
                <input id="address_line_2" type="text" wire:model.live="address_line_2" @class([$fieldBaseClasses, $fieldDefaultClasses => ! $errors->has('address_line_2'), $fieldErrorClasses => $errors->has('address_line_2')])>
                @error('address_line_2') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div class="sm:col-span-2">
                <label for="city" class="block text-sm font-medium text-gray-700">{{ __('frontend.account.addresses.fields.city') }}</label>
                <input id="city" type="text" wire:model.live="city" @class([$fieldBaseClasses, $fieldDefaultClasses => ! $errors->has('city'), $fieldErrorClasses => $errors->has('city')])>
                @error('city') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="postal_code" class="block text-sm font-medium text-gray-700">{{ __('frontend.account.addresses.fields.postal_code') }}</label>
                <input id="postal_code" type="text" wire:model.live="postal_code" @class([$fieldBaseClasses, $fieldDefaultClasses => ! $errors->has('postal_code'), $fieldErrorClasses => $errors->has('postal_code')])>
                @error('postal_code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="country_code" class="block text-sm font-medium text-gray-700">{{ __('frontend.account.addresses.fields.country_code') }}</label>
                <select id="country_code" wire:model.live="country_code" @class([$fieldBaseClasses, $fieldDefaultClasses => ! $errors->has('country_code'), $fieldErrorClasses => $errors->has('country_code')])>
                    <option value=""></option>
                    @foreach ($countries as $countryCode => $countryName)
                        <option value="{{ $countryCode }}">{{ $countryName }}</option>
                    @endforeach
                </select>
                @error('country_code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">{{ __('frontend.account.addresses.fields.phone') }}</label>
                <input id="phone" type="text" wire:model.live="phone" @class([$fieldBaseClasses, $fieldDefaultClasses => ! $errors->has('phone'), $fieldErrorClasses => $errors->has('phone')])>
                @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
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
                @error('type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" wire:model.live="set_as_default" class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                {{ __('frontend.account.addresses.set_as_default') }}
            </label>

            <div class="flex items-center gap-2">
                <a href="{{ route('account.addresses') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700">
                    {{ __('frontend.account.addresses.cancel_edit') }}
                </a>

                <button type="submit" class="inline-flex items-center rounded-md border border-transparent bg-primary-600 px-4 py-2 text-sm font-medium text-white">
                    {{ __('frontend.account.addresses.update_address') }}
                </button>
            </div>
        </div>
    </form>
</div>
