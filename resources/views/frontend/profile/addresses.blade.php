<x-layouts.base title="{{ __('Manage addresses') }}">
    <div class="max-w-5xl mx-auto px-4 py-10 space-y-8">
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ __('Addresses') }}</h1>

        <section class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-semibold mb-4">{{ __('Add a new address') }}</h2>
            <form method="POST" action="{{ route('frontend.profile.store-address') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="type">{{ __('Type') }}</label>
                    <select id="type" name="type" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                        @foreach ($types as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div></div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="first_name">{{ __('First name') }}</label>
                    <input id="first_name" name="first_name" value="{{ old('first_name') }}" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                    @error('first_name')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="last_name">{{ __('Last name') }}</label>
                    <input id="last_name" name="last_name" value="{{ old('last_name') }}" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                    @error('last_name')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="address_line_1">{{ __('Address line 1') }}</label>
                    <input id="address_line_1" name="address_line_1" value="{{ old('address_line_1') }}" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                    @error('address_line_1')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="address_line_2">{{ __('Address line 2') }}</label>
                    <input id="address_line_2" name="address_line_2" value="{{ old('address_line_2') }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="city">{{ __('City') }}</label>
                    <input id="city" name="city" value="{{ old('city') }}" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                    @error('city')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="postal_code">{{ __('Postal code') }}</label>
                    <input id="postal_code" name="postal_code" value="{{ old('postal_code') }}" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                    @error('postal_code')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="country_code">{{ __('Country code') }}</label>
                    <input id="country_code" name="country_code" value="{{ old('country_code', 'LT') }}" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                    @error('country_code')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="phone">{{ __('Phone') }}</label>
                    <input id="phone" name="phone" value="{{ old('phone') }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_default" name="is_default" value="1" class="rounded border-gray-300 dark:border-white/10" @checked(old('is_default'))>
                    <label for="is_default" class="text-sm text-gray-600 dark:text-gray-300">{{ __('Set as default address') }}</label>
                </div>
                <div class="md:col-span-2 flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">{{ __('Save address') }}</button>
                </div>
            </form>
        </section>

        <section class="space-y-4">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('Saved addresses') }}</h2>
            @forelse ($addresses as $address)
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl shadow-sm p-6">
                    <form method="POST" action="{{ route('frontend.profile.update-address', $address) }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="type-{{ $address->id }}">{{ __('Type') }}</label>
                            <select id="type-{{ $address->id }}" name="type" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                                @foreach ($types as $type)
                                    <option value="{{ $type->value }}" @selected($address->type === $type->value)>{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="default-{{ $address->id }}" name="is_default" value="1" class="rounded border-gray-300 dark:border-white/10" @checked($address->is_default)>
                            <label for="default-{{ $address->id }}" class="text-sm text-gray-600 dark:text-gray-300">{{ __('Default') }}</label>
                        </div>
                        <div>
                            <label class="block text-sm font-medium" for="first-name-{{ $address->id }}">{{ __('First name') }}</label>
                            <input id="first-name-{{ $address->id }}" name="first_name" value="{{ $address->first_name }}" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                        </div>
                        <div>
                            <label class="block text-sm font-medium" for="last-name-{{ $address->id }}">{{ __('Last name') }}</label>
                            <input id="last-name-{{ $address->id }}" name="last_name" value="{{ $address->last_name }}" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium" for="line1-{{ $address->id }}">{{ __('Address line 1') }}</label>
                            <input id="line1-{{ $address->id }}" name="address_line_1" value="{{ $address->address_line_1 }}" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium" for="line2-{{ $address->id }}">{{ __('Address line 2') }}</label>
                            <input id="line2-{{ $address->id }}" name="address_line_2" value="{{ $address->address_line_2 }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                        </div>
                        <div>
                            <label class="block text-sm font-medium" for="city-{{ $address->id }}">{{ __('City') }}</label>
                            <input id="city-{{ $address->id }}" name="city" value="{{ $address->city }}" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                        </div>
                        <div>
                            <label class="block text-sm font-medium" for="postal-{{ $address->id }}">{{ __('Postal code') }}</label>
                            <input id="postal-{{ $address->id }}" name="postal_code" value="{{ $address->postal_code }}" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                        </div>
                        <div>
                            <label class="block text-sm font-medium" for="country-{{ $address->id }}">{{ __('Country code') }}</label>
                            <input id="country-{{ $address->id }}" name="country_code" value="{{ $address->country_code }}" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                        </div>
                        <div>
                            <label class="block text-sm font-medium" for="phone-{{ $address->id }}">{{ __('Phone') }}</label>
                            <input id="phone-{{ $address->id }}" name="phone" value="{{ $address->phone }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                        </div>
                        <div class="md:col-span-2 flex justify-end gap-3">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">{{ __('Update') }}</button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('frontend.profile.delete-address', $address) }}" class="mt-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 hover:text-red-700">{{ __('Delete address') }}</button>
                    </form>
                </div>
            @empty
                <p class="text-gray-500 dark:text-gray-400">{{ __('You have not saved any addresses yet.') }}</p>
            @endforelse
        </section>
    </div>
</x-layouts.base>
