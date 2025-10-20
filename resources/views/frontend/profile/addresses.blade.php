@extends('components.layouts.base')

@section('title', __('My addresses'))

@section('content')
    <x-container class="py-8 space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-semibold text-gray-900">{{ __('My addresses') }}</h1>
        </div>

        <form method="post" action="{{ route('frontend.profile.store-address') }}" class="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <x-input label="{{ __('First name') }}" name="first_name" required />
                <x-input label="{{ __('Last name') }}" name="last_name" required />
            </div>
            <x-input label="{{ __('Address line 1') }}" name="address_line_1" required />
            <x-input label="{{ __('Address line 2') }}" name="address_line_2" />
            <div class="grid gap-4 sm:grid-cols-2">
                <x-input label="{{ __('City') }}" name="city" required />
                <x-input label="{{ __('Postal code') }}" name="postal_code" required />
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-input label="{{ __('Country code') }}" name="country_code" required />
                <x-input label="{{ __('Phone') }}" name="phone" />
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="is_default" value="1" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                {{ __('Set as default address') }}
            </label>
            <x-button type="submit">{{ __('Add address') }}</x-button>
        </form>

        <div class="space-y-4">
            @forelse ($addresses as $address)
                <article class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">{{ $address->full_name }}</h2>
                        @if ($address->is_default)
                            <span class="rounded-full bg-primary-100 px-2 py-0.5 text-xs font-semibold text-primary-700">{{ __('Default') }}</span>
                        @endif
                    </div>
                    <p class="mt-2 text-sm text-gray-600">{{ $address->full_address }}</p>
                    <div class="mt-4 flex items-center gap-3 text-sm text-gray-500">
                        <form method="post" action="{{ route('frontend.profile.update-address', $address) }}" class="flex flex-wrap gap-2">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="set_default" value="1">
                            <x-button type="submit" color="secondary">{{ __('Make default') }}</x-button>
                        </form>
                        <form method="post" action="{{ route('frontend.profile.delete-address', $address) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Delete') }}</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="rounded-lg border border-dashed border-gray-300 p-12 text-center text-gray-500">
                    {{ __('You have not added any addresses yet.') }}
                </div>
            @endforelse
        </div>
    </x-container>
@endsection
