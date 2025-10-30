@extends('components.layouts.base')

@section('title', __('Manage addresses'))

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-5xl mx-auto space-y-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ __('Your addresses') }}
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        {{ __('Create, update or remove the addresses used during checkout.') }}
                    </p>
                </div>
                <a href="{{ route('frontend.profile.index') }}"
                   class="inline-flex items-center text-sm text-blue-600 hover:text-blue-500">
                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    {{ __('Back to profile') }}
                </a>
            </div>

            @if(session('success'))
                <div class="rounded-md bg-green-50 p-4 border border-green-200 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="rounded-md bg-red-50 p-4 border border-red-200 text-red-700">
                    <p class="font-semibold mb-2">{{ __('Please correct the highlighted fields below.') }}</p>
                    <ul class="list-disc pl-5 space-y-1 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="space-y-6">
                @forelse($addresses as $address)
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
                        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ $address->full_name ?? ($address->first_name . ' ' . $address->last_name) }}
                                </h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $address->address_line_1 }}@if($address->address_line_2), {{ $address->address_line_2 }}@endif,
                                    {{ $address->postal_code }} {{ $address->city }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2 text-xs font-medium text-gray-600 dark:text-gray-300">
                                @if($address->is_default)
                                    <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-800">{{ __('Default') }}</span>
                                @endif
                                @if($address->is_billing)
                                    <span class="px-2 py-1 rounded-full bg-green-100 text-green-800">{{ __('Billing') }}</span>
                                @endif
                                @if($address->is_shipping)
                                    <span class="px-2 py-1 rounded-full bg-purple-100 text-purple-800">{{ __('Shipping') }}</span>
                                @endif
                            </div>
                        </div>

                        <form method="POST" action="{{ route('frontend.profile.update-address', $address) }}" class="px-6 py-6 space-y-4">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="type-{{ $address->id }}">
                                        {{ __('Address type') }}
                                    </label>
                                    <select id="type-{{ $address->id }}"
                                            name="type"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @foreach($addressTypes as $value => $label)
                                            <option value="{{ $value }}" @selected(old('type', $address->type) == $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="is_default-{{ $address->id }}">
                                        <input id="is_default-{{ $address->id }}" type="checkbox" name="is_default" value="1" @checked(old('is_default', $address->is_default))>
                                        <span class="ml-2">{{ __('Default') }}</span>
                                    </label>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="is_billing-{{ $address->id }}">
                                        <input id="is_billing-{{ $address->id }}" type="checkbox" name="is_billing" value="1" @checked(old('is_billing', $address->is_billing))>
                                        <span class="ml-2">{{ __('Billing') }}</span>
                                    </label>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="is_shipping-{{ $address->id }}">
                                        <input id="is_shipping-{{ $address->id }}" type="checkbox" name="is_shipping" value="1" @checked(old('is_shipping', $address->is_shipping))>
                                        <span class="ml-2">{{ __('Shipping') }}</span>
                                    </label>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="first_name-{{ $address->id }}">
                                        {{ __('First name') }}
                                    </label>
                                    <input id="first_name-{{ $address->id }}"
                                           type="text"
                                           name="first_name"
                                           value="{{ old('first_name', $address->first_name) }}"
                                           required
                                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="last_name-{{ $address->id }}">
                                        {{ __('Last name') }}
                                    </label>
                                    <input id="last_name-{{ $address->id }}"
                                           type="text"
                                           name="last_name"
                                           value="{{ old('last_name', $address->last_name) }}"
                                           required
                                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="address_line_1-{{ $address->id }}">
                                        {{ __('Address line 1') }}
                                    </label>
                                    <input id="address_line_1-{{ $address->id }}"
                                           type="text"
                                           name="address_line_1"
                                           value="{{ old('address_line_1', $address->address_line_1) }}"
                                           required
                                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="address_line_2-{{ $address->id }}">
                                        {{ __('Address line 2') }}
                                    </label>
                                    <input id="address_line_2-{{ $address->id }}"
                                           type="text"
                                           name="address_line_2"
                                           value="{{ old('address_line_2', $address->address_line_2) }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="city-{{ $address->id }}">
                                        {{ __('City') }}
                                    </label>
                                    <input id="city-{{ $address->id }}"
                                           type="text"
                                           name="city"
                                           value="{{ old('city', $address->city) }}"
                                           required
                                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="postal_code-{{ $address->id }}">
                                        {{ __('Postal code') }}
                                    </label>
                                    <input id="postal_code-{{ $address->id }}"
                                           type="text"
                                           name="postal_code"
                                           value="{{ old('postal_code', $address->postal_code) }}"
                                           required
                                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="country_code-{{ $address->id }}">
                                        {{ __('Country code') }}
                                    </label>
                                    <select id="country_code-{{ $address->id }}"
                                            name="country_code"
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @foreach($countries as $country)
                                            <option value="{{ $country->cca2 }}" @selected(strtoupper(old('country_code', $address->country_code)) === $country->cca2)>
                                                {{ $country->name }} ({{ $country->cca2 }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="phone-addr-{{ $address->id }}">
                                        {{ __('Phone number') }}
                                    </label>
                                    <input id="phone-addr-{{ $address->id }}"
                                           type="text"
                                           name="phone"
                                           value="{{ old('phone', $address->phone) }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="email-addr-{{ $address->id }}">
                                        {{ __('Email address') }}
                                    </label>
                                    <input id="email-addr-{{ $address->id }}"
                                           type="email"
                                           name="email"
                                           value="{{ old('email', $address->email) }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>

                            <div class="flex justify-between items-center">
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    {{ __('Save address') }}
                                </button>
                                <button type="submit"
                                        form="delete-address-{{ $address->id }}"
                                        onclick="return confirm('{{ __('Are you sure you want to delete this address?') }}');"
                                        class="inline-flex items-center px-3 py-2 border border-red-300 dark:border-red-600 rounded-md text-sm font-medium text-red-700 dark:text-red-300 bg-white dark:bg-gray-700 hover:bg-red-50 dark:hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    {{ __('Delete') }}
                                </button>
                            </div>
                        </form>
                        <form id="delete-address-{{ $address->id }}" method="POST" action="{{ route('frontend.profile.delete-address', $address) }}" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                @empty
                    <div class="bg-white dark:bg-gray-800 border border-dashed border-gray-300 dark:border-gray-700 rounded-lg p-6 text-center text-gray-600 dark:text-gray-300">
                        {{ __('You have not added any addresses yet. Use the form below to create your first one.') }}
                    </div>
                @endforelse
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('Add a new address') }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Fill in the fields below to store another shipping or billing address.') }}
                    </p>
                </div>

                <form method="POST" action="{{ route('frontend.profile.store-address') }}" class="px-6 py-6 space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="type">
                                {{ __('Address type') }}
                            </label>
                            <select id="type"
                                    name="type"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @foreach($addressTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="is_default">
                                <input id="is_default" type="checkbox" name="is_default" value="1" @checked(old('is_default'))>
                                <span class="ml-2">{{ __('Default') }}</span>
                            </label>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="is_billing">
                                <input id="is_billing" type="checkbox" name="is_billing" value="1" @checked(old('is_billing'))>
                                <span class="ml-2">{{ __('Billing') }}</span>
                            </label>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="is_shipping">
                                <input id="is_shipping" type="checkbox" name="is_shipping" value="1" @checked(old('is_shipping'))>
                                <span class="ml-2">{{ __('Shipping') }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="first_name">
                                {{ __('First name') }}
                            </label>
                            <input id="first_name"
                                   type="text"
                                   name="first_name"
                                   value="{{ old('first_name') }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="last_name">
                                {{ __('Last name') }}
                            </label>
                            <input id="last_name"
                                   type="text"
                                   name="last_name"
                                   value="{{ old('last_name') }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="address_line_1">
                                {{ __('Address line 1') }}
                            </label>
                            <input id="address_line_1"
                                   type="text"
                                   name="address_line_1"
                                   value="{{ old('address_line_1') }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="address_line_2">
                                {{ __('Address line 2') }}
                            </label>
                            <input id="address_line_2"
                                   type="text"
                                   name="address_line_2"
                                   value="{{ old('address_line_2') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="city">
                                {{ __('City') }}
                            </label>
                            <input id="city"
                                   type="text"
                                   name="city"
                                   value="{{ old('city') }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="postal_code">
                                {{ __('Postal code') }}
                            </label>
                            <input id="postal_code"
                                   type="text"
                                   name="postal_code"
                                   value="{{ old('postal_code') }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="country_code">
                                {{ __('Country code') }}
                            </label>
                            <select id="country_code"
                                    name="country_code"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @foreach($countries as $country)
                                    <option value="{{ $country->cca2 }}" @selected(strtoupper(old('country_code')) === $country->cca2)>
                                        {{ $country->name }} ({{ $country->cca2 }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="phone_new">
                                {{ __('Phone number') }}
                            </label>
                            <input id="phone_new"
                                   type="text"
                                   name="phone"
                                   value="{{ old('phone') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="email_new">
                                {{ __('Email address') }}
                            </label>
                            <input id="email_new"
                                   type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ __('Save new address') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
