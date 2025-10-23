@extends('frontend.layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-10 space-y-6">
        <header class="space-y-2 text-center">
            <h1 class="text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Edit profile') }}</h1>
            <p class="text-slate-600 dark:text-slate-300">{{ __('Update your personal information below.') }}</p>
        </header>

        <form method="post" action="{{ route('frontend.profile.update') }}" class="space-y-6 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6">
            @csrf
            @method('put')
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300" for="name">{{ __('Name') }}</label>
                <input id="name" name="name" value="{{ old('name', $user->name) }}" required class="mt-1 w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300" for="email">{{ __('Email') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="mt-1 w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300" for="phone">{{ __('Phone') }}</label>
                <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="mt-1 w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900">
            </div>
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('frontend.profile.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">{{ __('Cancel') }}</a>
                <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700">{{ __('Save changes') }}</button>
            </div>
        </form>
    </div>
@endsection
