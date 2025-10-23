@extends('components.layouts.base')

@section('title', __('Profile'))

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-5xl mx-auto space-y-8">
            @if(session('success'))
                <div class="rounded-md bg-green-50 p-4 border border-green-200 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="rounded-md bg-red-50 p-4 border border-red-200 text-red-800">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ __('My profile') }}
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        {{ __('Manage your personal information and saved addresses.') }}
                    </p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('frontend.profile.edit') }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        {{ __('Edit profile') }}
                    </a>
                    <a href="{{ route('frontend.profile.addresses') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-800 text-sm font-medium rounded-md shadow hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        {{ __('Manage addresses') }}
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
                    <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('Account details') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('These details are used for your storefront profile and communication.') }}
                        </p>
                    </div>
                    <dl class="px-6 py-5 space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Full name') }}</dt>
                            <dd class="mt-1 text-base text-gray-900 dark:text-white">
                                {{ trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->name }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Email address') }}</dt>
                            <dd class="mt-1 text-base text-gray-900 dark:text-white">
                                {{ $user->email }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Phone number') }}</dt>
                            <dd class="mt-1 text-base text-gray-900 dark:text-white">
                                {{ $user->phone ?? $user->phone_number ?? __('Not provided') }}
                            </dd>
                        </div>
                        @if($customer?->address)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Default billing address') }}</dt>
                                <dd class="mt-1 text-base text-gray-900 dark:text-white">
                                    {{ $customer->address }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
                    <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('Saved addresses') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Quick overview of the addresses you can use during checkout.') }}
                        </p>
                    </div>
                    <div class="px-6 py-5 space-y-4">
                        @forelse($addresses as $address)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-md p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-base font-semibold text-gray-900 dark:text-white">
                                            {{ $address->full_name ?? ($address->first_name . ' ' . $address->last_name) }}
                                        </p>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                            {{ $address->address_line_1 }}
                                            @if($address->address_line_2)
                                                <span>, {{ $address->address_line_2 }}</span>
                                            @endif
                                            <br>
                                            {{ $address->postal_code }} {{ $address->city }}
                                            @if($address->country_code)
                                                <span class="uppercase">({{ $address->country_code }})</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400 space-y-1 text-right">
                                        @if($address->is_default)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-medium">
                                                {{ __('Default') }}
                                            </span>
                                        @endif
                                        @if($address->is_billing)
                                            <span class="block">{{ __('Billing') }}</span>
                                        @endif
                                        @if($address->is_shipping)
                                            <span class="block">{{ __('Shipping') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                {{ __('You have not saved any addresses yet.') }}
                            </p>
                        @endforelse
                        <div>
                            <a href="{{ route('frontend.profile.addresses') }}"
                               class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-500">
                                {{ __('Add or update your addresses') }}
                                <svg class="h-4 w-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('translations.profile_data_controls_title') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('translations.profile_data_controls_description') }}
                    </p>
                </div>
                <div class="px-6 py-5 space-y-8">
                    <div class="space-y-3">
                        <h3 class="text-base font-medium text-gray-900 dark:text-white">
                            {{ __('translations.profile_export_title') }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            {{ __('translations.profile_export_description') }}
                        </p>
                        <form method="POST" action="{{ route('frontend.profile.data.export') }}">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                {{ __('translations.profile_export_cta') }}
                            </button>
                        </form>
                    </div>

                    <div class="space-y-3">
                        <h3 class="text-base font-medium text-gray-900 dark:text-white">
                            {{ __('translations.profile_delete_title') }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            {{ __('translations.profile_delete_description') }}
                        </p>
                        <form method="POST" action="{{ route('frontend.profile.data.destroy') }}" class="space-y-4">
                            @csrf
                            @method('DELETE')
                            <div>
                                <label for="delete-password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('translations.profile_delete_password_label') }}
                                </label>
                                <input type="password"
                                       name="password"
                                       id="delete-password"
                                       required
                                       autocomplete="current-password"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white" />
                            </div>
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="confirm-deletion" name="confirm_deletion" type="checkbox" value="1" required
                                           class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </div>
                                <label for="confirm-deletion" class="ml-3 text-sm text-gray-600 dark:text-gray-300">
                                    {{ __('translations.profile_delete_checkbox') }}
                                </label>
                            </div>
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md shadow hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                {{ __('translations.profile_delete_cta') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
