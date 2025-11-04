<div>
    <x-slot name="title">
        {{ $title }}
    </x-slot>

    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
                <label for="first_name" class="block text-sm font-medium text-gray-700">
                    {{ __('First name') }} <span class="text-red-500">*</span>
                </label>
                <input 
                    wire:model="first_name" 
                    id="first_name" 
                    name="first_name" 
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                @error('first_name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <label for="last_name" class="block text-sm font-medium text-gray-700">
                    {{ __('Last name') }} <span class="text-red-500">*</span>
                </label>
                <input 
                    wire:model="last_name" 
                    id="last_name" 
                    name="last_name" 
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                @error('last_name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="space-y-2">
            <label for="street_address" class="block text-sm font-medium text-gray-700">
                {{ __('Street Address') }} <span class="text-red-500">*</span>
            </label>
            <input
                wire:model="street_address"
                id="street_address"
                placeholder="{{ __('Enter street address') }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                name="street_address"
                type="text"
            />
            @error('street_address')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-2">
            <label for="street_address_plus" class="block text-sm font-medium text-gray-700">
                {{ __('Apartment, suite, etc.') }}
            </label>
            <input
                wire:model="street_address_plus"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                id="street_address_plus"
                name="street_address_plus"
                type="text"
                placeholder="{{ __('Apartment, suite, unit, etc.') }}"
            />
            @error('street_address_plus')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
                <label for="city" class="block text-sm font-medium text-gray-700">
                    {{ __('City') }} <span class="text-red-500">*</span>
                </label>
                <input 
                    wire:model="city" 
                    id="city" 
                    name="city" 
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                @error('city')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <label for="postal_code" class="block text-sm font-medium text-gray-700">
                    {{ __('Postal / Zip code') }} <span class="text-red-500">*</span>
                </label>
                <input 
                    wire:model="postal_code" 
                    id="postal_code" 
                    name="postal_code" 
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                @error('postal_code')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="space-y-2">
            <label for="country_code" class="block text-sm font-medium text-gray-700">
                {{ __('Country') }} <span class="text-red-500">*</span>
            </label>
            <select 
                wire:model="country_code" 
                id="country_code" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
                <option value="">{{ __('Select a country') }}</option>
                @foreach ($countries as $key => $country)
                    <option value="{{ $key }}">{{ $country }}</option>
                @endforeach
            </select>
            @error('country_code')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-2">
            <label for="phone_number" class="block text-sm font-medium text-gray-700">
                {{ __('Phone Number') }}
            </label>
            <input 
                wire:model="phone_number" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                id="phone_number" 
                name="phone_number" 
                type="text"
                placeholder="{{ __('Enter phone number') }}"
            />
            @error('phone_number')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <label class="block text-sm font-medium text-gray-700">
                    {{ __('Address type') }} <span class="text-red-500">*</span>
                </label>
            </div>

            <div class="space-y-3">
                <div class="flex items-center">
                    <input 
                        id="type-billing" 
                        name="type" 
                        type="radio" 
                        value="billing" 
                        wire:model="type"
                        class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                    />
                    <label for="type-billing" class="ml-2 text-sm font-medium text-gray-700">
                        {{ __('Billing address') }}
                    </label>
                </div>

                <div class="flex items-center">
                    <input 
                        id="type-shipping" 
                        name="type" 
                        type="radio" 
                        value="shipping" 
                        wire:model="type"
                        class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                    />
                    <label for="type-shipping" class="ml-2 text-sm font-medium text-gray-700">
                        {{ __('Shipping address') }}
                    </label>
                </div>
            </div>
            @error('type')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <x-slot name="footer">
        <div class="flex justify-end space-x-3">
            <button
                type="button"
                wire:click="closeModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
                {{ __('Cancel') }}
            </button>
            <button
                type="button"
                wire:click="save"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span wire:loading.remove wire:target="save">{{ __('Save') }}</span>
                <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
            </button>
        </div>
    </x-slot>
</div>
