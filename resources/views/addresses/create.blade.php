@extends('components.layouts.base')

@section('title', __('translations.add_new_address'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center mb-8">
            <a href="{{ route('frontend.addresses.index') }}" 
               class="text-blue-600 hover:text-blue-800 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('translations.add_new_address') }}</h1>
        </div>

        <form action="{{ route('frontend.addresses.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Address Type -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('translations.address_type') }}</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach(\App\Enums\AddressType::cases() as $type)
                        <label class="relative">
                            <input type="radio" name="type" value="{{ $type->value }}" 
                                   class="sr-only peer" 
                                   {{ old('type', 'shipping') === $type->value ? 'checked' : '' }}>
                            <div class="p-4 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($type->value === 'shipping')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                        @elseif($type->value === 'billing')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        @elseif($type->value === 'home')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                        @elseif($type->value === 'work')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        @endif
                                    </svg>
                                    <span class="text-sm font-medium">{{ $type->label() }}</span>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('type')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Personal Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('translations.personal_information') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700">{{ __('translations.first_name') }}</label>
                        <input type="text" name="first_name" id="first_name" 
                               value="{{ old('first_name') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('first_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700">{{ __('translations.last_name') }}</label>
                        <input type="text" name="last_name" id="last_name" 
                               value="{{ old('last_name') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('last_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Company Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('translations.company_information') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="company_name" class="block text-sm font-medium text-gray-700">{{ __('translations.company_name') }}</label>
                        <input type="text" name="company_name" id="company_name" 
                               value="{{ old('company_name') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('company_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="company_vat" class="block text-sm font-medium text-gray-700">{{ __('translations.company_vat') }}</label>
                        <input type="text" name="company_vat" id="company_vat" 
                               value="{{ old('company_vat') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('company_vat')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Address Details -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('translations.address_details') }}</h2>
                <div class="space-y-4">
                    <div>
                        <label for="address_line_1" class="block text-sm font-medium text-gray-700">{{ __('translations.address_line_1') }} *</label>
                        <input type="text" name="address_line_1" id="address_line_1" 
                               value="{{ old('address_line_1') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('address_line_1')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="address_line_2" class="block text-sm font-medium text-gray-700">{{ __('translations.address_line_2') }}</label>
                        <input type="text" name="address_line_2" id="address_line_2" 
                               value="{{ old('address_line_2') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('address_line_2')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="apartment" class="block text-sm font-medium text-gray-700">{{ __('translations.apartment') }}</label>
                            <input type="text" name="apartment" id="apartment" 
                                   value="{{ old('apartment') }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @error('apartment')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="floor" class="block text-sm font-medium text-gray-700">{{ __('translations.floor') }}</label>
                            <input type="text" name="floor" id="floor" 
                                   value="{{ old('floor') }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @error('floor')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="building" class="block text-sm font-medium text-gray-700">{{ __('translations.building') }}</label>
                            <input type="text" name="building" id="building" 
                                   value="{{ old('building') }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @error('building')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700">{{ __('translations.city') }} *</label>
                            <input type="text" name="city" id="city" 
                                   value="{{ old('city') }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @error('city')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="state" class="block text-sm font-medium text-gray-700">{{ __('translations.state') }}</label>
                            <input type="text" name="state" id="state" 
                                   value="{{ old('state') }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @error('state')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="postal_code" class="block text-sm font-medium text-gray-700">{{ __('translations.postal_code') }} *</label>
                            <input type="text" name="postal_code" id="postal_code" 
                                   value="{{ old('postal_code') }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @error('postal_code')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="country_code" class="block text-sm font-medium text-gray-700">{{ __('translations.country') }} *</label>
                            <select name="country_code" id="country_code" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="LT" {{ old('country_code', 'LT') === 'LT' ? 'selected' : '' }}>Lietuva</option>
                                <option value="LV" {{ old('country_code') === 'LV' ? 'selected' : '' }}>Latvija</option>
                                <option value="EE" {{ old('country_code') === 'EE' ? 'selected' : '' }}>Eesti</option>
                                <option value="PL" {{ old('country_code') === 'PL' ? 'selected' : '' }}>Polska</option>
                                <option value="DE" {{ old('country_code') === 'DE' ? 'selected' : '' }}>Deutschland</option>
                            </select>
                            @error('country_code')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('translations.contact_information') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">{{ __('translations.phone') }}</label>
                        <input type="tel" name="phone" id="phone" 
                               value="{{ old('phone') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('phone')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">{{ __('translations.email') }}</label>
                        <input type="email" name="email" id="email" 
                               value="{{ old('email') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="mt-4">
                    <label for="landmark" class="block text-sm font-medium text-gray-700">{{ __('translations.landmark') }}</label>
                    <input type="text" name="landmark" id="landmark" 
                           value="{{ old('landmark') }}"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('landmark')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Additional Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('translations.additional_information') }}</h2>
                <div class="space-y-4">
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700">{{ __('translations.notes') }}</label>
                        <textarea name="notes" id="notes" rows="3"
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="instructions" class="block text-sm font-medium text-gray-700">{{ __('translations.instructions') }}</label>
                        <textarea name="instructions" id="instructions" rows="3"
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('instructions') }}</textarea>
                        @error('instructions')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Settings -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('translations.settings') }}</h2>
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_default" id="is_default" value="1" 
                               {{ old('is_default') ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_default" class="ml-2 block text-sm text-gray-900">
                            {{ __('translations.set_as_default_address') }}
                        </label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="is_billing" id="is_billing" value="1" 
                               {{ old('is_billing') ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_billing" class="ml-2 block text-sm text-gray-900">
                            {{ __('translations.use_for_billing') }}
                        </label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="is_shipping" id="is_shipping" value="1" 
                               {{ old('is_shipping') ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_shipping" class="ml-2 block text-sm text-gray-900">
                            {{ __('translations.use_for_shipping') }}
                        </label>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('frontend.addresses.index') }}" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    {{ __('translations.cancel') }}
                </a>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('translations.save_address') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
