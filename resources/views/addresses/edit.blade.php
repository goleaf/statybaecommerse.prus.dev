@extends('components.layouts.base')

@section('title', __('translations.edit_address'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center mb-8">
            <a href="{{ route('frontend.addresses.show', $address) }}" 
               class="text-blue-600 hover:text-blue-800 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('translations.edit_address') }}</h1>
        </div>

        <form action="{{ route('frontend.addresses.update', $address) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-lg shadow-lg p-6">
                <!-- Address Type -->
                <div class="mb-6">
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('translations.address_type') }} <span class="text-red-500">*</span>
                    </label>
                    <select name="type" id="type" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('type') border-red-500 @enderror"
                            required>
                        @foreach($addressTypes as $key => $label)
                            <option value="{{ $key }}" {{ old('type', $address->type) == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Personal Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('translations.first_name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="first_name" id="first_name" 
                               value="{{ old('first_name', $address->first_name) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('first_name') border-red-500 @enderror"
                               required>
                        @error('first_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('translations.last_name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="last_name" id="last_name" 
                               value="{{ old('last_name', $address->last_name) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('last_name') border-red-500 @enderror"
                               required>
                        @error('last_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Company Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('translations.company_name') }}
                        </label>
                        <input type="text" name="company_name" id="company_name" 
                               value="{{ old('company_name', $address->company_name) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('company_name') border-red-500 @enderror">
                        @error('company_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="company_vat" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('translations.company_vat') }}
                        </label>
                        <input type="text" name="company_vat" id="company_vat" 
                               value="{{ old('company_vat', $address->company_vat) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('company_vat') border-red-500 @enderror">
                        @error('company_vat')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Address Information -->
                <div class="mb-6">
                    <label for="address_line_1" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('translations.address_line_1') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="address_line_1" id="address_line_1" 
                           value="{{ old('address_line_1', $address->address_line_1) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('address_line_1') border-red-500 @enderror"
                           required>
                    @error('address_line_1')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="address_line_2" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('translations.address_line_2') }}
                    </label>
                    <input type="text" name="address_line_2" id="address_line_2" 
                           value="{{ old('address_line_2', $address->address_line_2) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('address_line_2') border-red-500 @enderror">
                    @error('address_line_2')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Additional Address Information -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label for="apartment" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('translations.apartment') }}
                        </label>
                        <input type="text" name="apartment" id="apartment" 
                               value="{{ old('apartment', $address->apartment) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('apartment') border-red-500 @enderror">
                        @error('apartment')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="floor" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('translations.floor') }}
                        </label>
                        <input type="text" name="floor" id="floor" 
                               value="{{ old('floor', $address->floor) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('floor') border-red-500 @enderror">
                        @error('floor')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="building" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('translations.building') }}
                        </label>
                        <input type="text" name="building" id="building" 
                               value="{{ old('building', $address->building) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('building') border-red-500 @enderror">
                        @error('building')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Location Information -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('translations.city') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="city" id="city" 
                               value="{{ old('city', $address->city) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('city') border-red-500 @enderror"
                               required>
                        @error('city')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('translations.postal_code') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="postal_code" id="postal_code" 
                               value="{{ old('postal_code', $address->postal_code) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('postal_code') border-red-500 @enderror"
                               required>
                        @error('postal_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="country_code" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('translations.country') }} <span class="text-red-500">*</span>
                        </label>
                        <select name="country_code" id="country_code" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('country_code') border-red-500 @enderror"
                                required>
                            @foreach($countries as $country)
                                <option value="{{ $country->code }}" {{ old('country_code', $address->country_code) == $country->code ? 'selected' : '' }}>
                                    {{ $country->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('country_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('translations.phone') }}
                        </label>
                        <input type="tel" name="phone" id="phone" 
                               value="{{ old('phone', $address->phone) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('phone') border-red-500 @enderror">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('translations.email') }}
                        </label>
                        <input type="email" name="email" id="email" 
                               value="{{ old('email', $address->email) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="landmark" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('translations.landmark') }}
                        </label>
                        <input type="text" name="landmark" id="landmark" 
                               value="{{ old('landmark', $address->landmark) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('landmark') border-red-500 @enderror">
                        @error('landmark')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="instructions" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('translations.delivery_instructions') }}
                        </label>
                        <textarea name="instructions" id="instructions" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('instructions') border-red-500 @enderror">{{ old('instructions', $address->instructions) }}</textarea>
                        @error('instructions')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Status Options -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_default" id="is_default" value="1" 
                               {{ old('is_default', $address->is_default) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_default" class="ml-2 block text-sm text-gray-700">
                            {{ __('translations.set_as_default') }}
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_billing" id="is_billing" value="1" 
                               {{ old('is_billing', $address->is_billing) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_billing" class="ml-2 block text-sm text-gray-700">
                            {{ __('translations.use_for_billing') }}
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_shipping" id="is_shipping" value="1" 
                               {{ old('is_shipping', $address->is_shipping) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_shipping" class="ml-2 block text-sm text-gray-700">
                            {{ __('translations.use_for_shipping') }}
                        </label>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200">
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('translations.update_address') }}
                    </button>

                    <a href="{{ route('frontend.addresses.show', $address) }}" 
                       class="inline-flex items-center px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        {{ __('translations.cancel') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
