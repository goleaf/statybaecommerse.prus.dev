@extends('frontend.layouts.app')

@section('title', __('frontend.contact.meta.title'))
@section('meta_description', __('frontend.contact.meta.description'))

@section('content')
    <div class="bg-gray-50 dark:bg-gray-900 py-12 sm:py-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <div class="lg:col-span-2">
                    <div class="mb-8">
                        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-3">
                            {{ __('frontend.contact.heading.title') }}
                        </h1>
                        <p class="text-lg text-gray-600 dark:text-gray-300">
                            {{ __('frontend.contact.heading.subtitle') }}
                        </p>
                    </div>

                    @if (session('success'))
                        <x-alert type="success" class="mb-6">
                            {{ session('success') }}
                        </x-alert>
                    @endif

                    @if ($errors->any())
                        <x-alert type="error" class="mb-6">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </x-alert>
                    @endif

                    <div class="bg-white dark:bg-gray-800 shadow rounded-2xl p-6 sm:p-10">
                        <form method="POST" action="{{ route('frontend.contact.send') }}" class="space-y-6">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                        {{ __('frontend.contact.form.name') }}
                                    </label>
                                    <input
                                        type="text"
                                        id="name"
                                        name="name"
                                        value="{{ old('name') }}"
                                        required
                                        maxlength="255"
                                        class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-4 py-3 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                        {{ __('frontend.contact.form.email') }}
                                    </label>
                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        required
                                        maxlength="255"
                                        class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-4 py-3 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                        {{ __('frontend.contact.form.phone') }}
                                    </label>
                                    <input
                                        type="tel"
                                        id="phone"
                                        name="phone"
                                        value="{{ old('phone') }}"
                                        maxlength="50"
                                        class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-4 py-3 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>

                                <div>
                                    <label for="order_number" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                        {{ __('frontend.contact.form.order_number') }}
                                    </label>
                                    <input
                                        type="text"
                                        id="order_number"
                                        name="order_number"
                                        value="{{ old('order_number') }}"
                                        maxlength="100"
                                        class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-4 py-3 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                            </div>

                            <div>
                                <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                    {{ __('frontend.contact.form.subject') }}
                                </label>
                                <input
                                    type="text"
                                    id="subject"
                                    name="subject"
                                    value="{{ old('subject') }}"
                                    required
                                    maxlength="255"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-4 py-3 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500"
                                >
                            </div>

                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                    {{ __('frontend.contact.form.message') }}
                                </label>
                                <textarea
                                    id="message"
                                    name="message"
                                    rows="6"
                                    required
                                    maxlength="1000"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-4 py-3 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500"
                                >{{ old('message') }}</textarea>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:justify-end">
                                <button type="submit"
                                        class="w-full sm:w-auto inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                                    {{ __('frontend.contact.form.submit') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <aside class="space-y-6">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-2xl p-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                            {{ __('frontend.contact.support.title') }}
                        </h2>
                        <ul class="space-y-4 text-gray-700 dark:text-gray-300">
                            @if ($supportEmail)
                                <li>
                                    <div class="text-sm uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        {{ __('frontend.contact.support.email') }}
                                    </div>
                                    <a href="mailto:{{ $supportEmail }}" class="mt-1 inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:underline">
                                        <x-untitledui-mail-02 class="h-5 w-5" />
                                        <span>{{ $supportEmail }}</span>
                                    </a>
                                </li>
                            @endif

                            @if ($company?->phone)
                                <li>
                                    <div class="text-sm uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        {{ __('frontend.contact.support.phone') }}
                                    </div>
                                    <a href="tel:{{ preg_replace('/\s+/', '', $company->phone) }}" class="mt-1 inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:underline">
                                        <x-untitledui-phone class="h-5 w-5" />
                                        <span>{{ $company->phone }}</span>
                                    </a>
                                </li>
                            @endif

                            @if ($company?->address)
                                <li>
                                    <div class="text-sm uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        {{ __('frontend.contact.support.address') }}
                                    </div>
                                    <div class="mt-1 flex items-start gap-2">
                                        <x-untitledui-info-circle class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                        <span>{{ $company->address }}</span>
                                    </div>
                                </li>
                            @endif

                            <li>
                                <div class="text-sm uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    {{ __('frontend.contact.support.hours') }}
                                </div>
                                <div class="mt-1 flex items-start gap-2">
                                    <x-untitledui-info-circle class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                    <span>{{ data_get($company, 'metadata.business_hours', __('frontend.contact.support.fallback_hours')) }}</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </aside>
            </div>
        </div>
    </div>
@endsection
