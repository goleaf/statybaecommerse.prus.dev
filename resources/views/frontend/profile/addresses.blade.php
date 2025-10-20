@extends('frontend.layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-10 space-y-6">
        <header class="space-y-2">
            <h1 class="text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Manage addresses') }}</h1>
            <p class="text-slate-600 dark:text-slate-300">{{ __('Add new delivery locations or update existing ones.') }}</p>
        </header>

        @if (session('status'))
            <div class="rounded-lg bg-green-100 text-green-800 px-4 py-3">{{ session('status') }}</div>
        @endif

        <section class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6">
            <form method="post" action="{{ route('frontend.profile.store-address') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300" for="first_name">{{ __('First name') }}</label>
                        <input id="first_name" name="first_name" required class="mt-1 w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300" for="last_name">{{ __('Last name') }}</label>
                        <input id="last_name" name="last_name" required class="mt-1 w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300" for="address_line_1">{{ __('Address line 1') }}</label>
                    <input id="address_line_1" name="address_line_1" required class="mt-1 w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300" for="address_line_2">{{ __('Address line 2') }}</label>
                    <input id="address_line_2" name="address_line_2" class="mt-1 w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300" for="city">{{ __('City') }}</label>
                        <input id="city" name="city" required class="mt-1 w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300" for="postal_code">{{ __('Postal code') }}</label>
                        <input id="postal_code" name="postal_code" required class="mt-1 w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300" for="country_code">{{ __('Country code') }}</label>
                        <input id="country_code" name="country_code" maxlength="2" required class="mt-1 w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300" for="phone">{{ __('Phone') }}</label>
                        <input id="phone" name="phone" class="mt-1 w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900">
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <label class="inline-flex items-center text-sm text-slate-600 dark:text-slate-300">
                        <input type="checkbox" name="is_default" value="1" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 mr-2">
                        {{ __('Set as default address') }}
                    </label>
                    <label class="inline-flex items-center text-sm text-slate-600 dark:text-slate-300">
                        <input type="checkbox" name="is_billing" value="1" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 mr-2">
                        {{ __('Billing address') }}
                    </label>
                    <label class="inline-flex items-center text-sm text-slate-600 dark:text-slate-300">
                        <input type="checkbox" name="is_shipping" value="1" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 mr-2">
                        {{ __('Shipping address') }}
                    </label>
                </div>
                <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700">{{ __('Save address') }}</button>
            </form>
        </section>

        <section class="space-y-4">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Saved addresses') }}</h2>
            @if ($addresses->isEmpty())
                <p class="text-slate-600 dark:text-slate-300">{{ __('No addresses added yet.') }}</p>
            @else
                <div class="space-y-3">
                    @foreach ($addresses as $address)
                        <article class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-slate-900 dark:text-slate-100">{{ $address->full_name }}</h3>
                                <p class="text-sm text-slate-600 dark:text-slate-300">{{ $address->full_address }}</p>
                            </div>
                            <div class="flex items-center gap-3 text-sm">
                                <form method="post" action="{{ route('frontend.profile.update-address', $address) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('put')
                                    <input type="hidden" name="first_name" value="{{ $address->first_name }}">
                                    <input type="hidden" name="last_name" value="{{ $address->last_name }}">
                                    <input type="hidden" name="address_line_1" value="{{ $address->address_line_1 }}">
                                    <input type="hidden" name="address_line_2" value="{{ $address->address_line_2 }}">
                                    <input type="hidden" name="city" value="{{ $address->city }}">
                                    <input type="hidden" name="postal_code" value="{{ $address->postal_code }}">
                                    <input type="hidden" name="country_code" value="{{ $address->country_code }}">
                                    <input type="hidden" name="phone" value="{{ $address->phone }}">
                                    <input type="hidden" name="type" value="{{ $address->type?->value ?? $address->type ?? 'shipping' }}">
                                    <input type="hidden" name="is_default" value="1">
                                    <input type="hidden" name="is_billing" value="{{ $address->is_billing ? 1 : 0 }}">
                                    <input type="hidden" name="is_shipping" value="{{ $address->is_shipping ? 1 : 0 }}">
                                    <button type="submit" class="text-primary-600 hover:text-primary-700">{{ __('Mark as default') }}</button>
                                </form>
                                <form method="post" action="{{ route('frontend.profile.delete-address', $address) }}">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="text-red-600 hover:text-red-700">{{ __('Remove') }}</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
