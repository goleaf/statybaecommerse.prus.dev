@extends('components.layouts.base')

@section('title', __('Edit profile'))

@section('content')
    <x-container class="py-8 space-y-6">
        <h1 class="text-3xl font-semibold text-gray-900">{{ __('Edit profile') }}</h1>

        <form method="post" action="{{ route('frontend.profile.update') }}" class="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PUT')
            <x-input label="{{ __('Name') }}" name="name" value="{{ old('name', $user?->name) }}" required />
            <x-input label="{{ __('Email') }}" name="email" type="email" value="{{ old('email', $user?->email) }}" required />
            <x-button type="submit">{{ __('Save changes') }}</x-button>
        </form>
    </x-container>
@endsection
