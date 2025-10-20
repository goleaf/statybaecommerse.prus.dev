@extends('components.layouts.base')

@section('title', __('My profile'))

@section('content')
    <x-container class="py-8 space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-semibold text-gray-900">{{ __('My profile') }}</h1>
            <a href="{{ route('frontend.profile.edit') }}" class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-white hover:bg-primary-700">
                {{ __('Edit profile') }}
            </a>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <dl class="grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm text-gray-500">{{ __('Name') }}</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ $user?->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">{{ __('Email') }}</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ $user?->email }}</dd>
                </div>
            </dl>
        </div>

        <a href="{{ route('frontend.profile.addresses') }}" class="inline-flex items-center rounded-md border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:border-primary-300">
            {{ __('Manage addresses') }}
        </a>
    </x-container>
@endsection
