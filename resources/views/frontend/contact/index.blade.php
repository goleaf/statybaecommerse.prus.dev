@extends('frontend.layouts.app')

@section('title', __('frontend.contact.meta.title'))
@section('meta_description', __('frontend.contact.meta.description'))

@php
    $routeLocale = request()->route('locale');
    $contactSendAction = is_string($routeLocale) && $routeLocale !== '' && \Illuminate\Support\Facades\Route::has('localized.contact.send')
        ? route('localized.contact.send', ['locale' => $routeLocale])
        : (\Illuminate\Support\Facades\Route::has('frontend.contact.send')
            ? route('frontend.contact.send', [])
            : url('/contact/send'));
    $supportAddress = 'Jurbarkas, Lietuva';
    $supportPhone = '+370 695 72123';
    $supportPhoneHref = '+37069572123';
    $supportEmail = 'info@egisstatyba.lt';
    $supportAddressHref = 'https://maps.google.com/?q=' . urlencode($supportAddress);
@endphp

@section('content')
    <div class="bg-slate-50 py-12 sm:py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-10 lg:grid-cols-3 lg:gap-12">
                <div class="lg:col-span-2">
                    <div class="mb-8">
                        <h1 class="mb-3 text-3xl font-bold text-slate-900 sm:text-4xl">
                            {{ __('frontend.contact.heading.title') }}
                        </h1>
                        <p class="text-lg text-slate-600">
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
                            <ul class="list-inside list-disc space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </x-alert>
                    @endif

                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
                        <form method="POST" action="{{ $contactSendAction }}" class="space-y-6">
                            @csrf
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-slate-700">
                                        {{ __('frontend.contact.form.name') }}
                                    </label>
                                    <input
                                        type="text"
                                        id="name"
                                        name="name"
                                        value="{{ old('name') }}"
                                        required
                                        maxlength="255"
                                        class="mt-1 block w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 placeholder:text-slate-400 focus:border-cyan-500 focus:ring-cyan-500"
                                    >
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-slate-700">
                                        {{ __('frontend.contact.form.email') }}
                                    </label>
                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        required
                                        maxlength="255"
                                        class="mt-1 block w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 placeholder:text-slate-400 focus:border-cyan-500 focus:ring-cyan-500"
                                    >
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-slate-700">
                                        {{ __('frontend.contact.form.phone') }}
                                    </label>
                                    <input
                                        type="tel"
                                        id="phone"
                                        name="phone"
                                        value="{{ old('phone') }}"
                                        maxlength="50"
                                        class="mt-1 block w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 placeholder:text-slate-400 focus:border-cyan-500 focus:ring-cyan-500"
                                    >
                                </div>

                                <div>
                                    <label for="order_number" class="block text-sm font-medium text-slate-700">
                                        {{ __('frontend.contact.form.order_number') }}
                                    </label>
                                    <input
                                        type="text"
                                        id="order_number"
                                        name="order_number"
                                        value="{{ old('order_number') }}"
                                        maxlength="100"
                                        class="mt-1 block w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 placeholder:text-slate-400 focus:border-cyan-500 focus:ring-cyan-500"
                                    >
                                </div>
                            </div>

                            <div>
                                <label for="subject" class="block text-sm font-medium text-slate-700">
                                    {{ __('frontend.contact.form.subject') }}
                                </label>
                                <input
                                    type="text"
                                    id="subject"
                                    name="subject"
                                    value="{{ old('subject') }}"
                                    required
                                    maxlength="255"
                                    class="mt-1 block w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 placeholder:text-slate-400 focus:border-cyan-500 focus:ring-cyan-500"
                                >
                            </div>

                            <div>
                                <label for="message" class="block text-sm font-medium text-slate-700">
                                    {{ __('frontend.contact.form.message') }}
                                </label>
                                <textarea
                                    id="message"
                                    name="message"
                                    rows="6"
                                    required
                                    maxlength="1000"
                                    class="mt-1 block w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 placeholder:text-slate-400 focus:border-cyan-500 focus:ring-cyan-500"
                                >{{ old('message') }}</textarea>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:justify-end">
                                <button type="submit"
                                    class="inline-flex w-full items-center justify-center rounded-full bg-cyan-600 px-6 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-slate-50 sm:w-auto">
                                    {{ __('frontend.contact.form.submit') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <aside class="space-y-6">
                    <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-200 bg-gray-900 px-6 py-4">
                            <h2 class="text-xl font-semibold text-white">
                                {{ __('frontend.contact.support.title') }}
                            </h2>
                        </div>
                        <ul class="space-y-3 p-6">
                            <li>
                                <a href="mailto:{{ $supportEmail }}" class="group flex items-start gap-3 rounded-2xl border border-gray-200 bg-gray-50 p-4 text-gray-800 transition hover:border-gray-300 hover:bg-white hover:shadow-sm">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-600 transition group-hover:text-indigo-600">
                                        <x-untitledui-mail-02 class="h-5 w-5" />
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block text-sm font-medium text-gray-600">
                                            {{ __('frontend.contact.support.email') }}
                                        </span>
                                        <span class="mt-1 block break-all text-base font-semibold text-gray-900 group-hover:text-indigo-700">
                                            {{ $supportEmail }}
                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a href="tel:{{ $supportPhoneHref }}" class="group flex items-start gap-3 rounded-2xl border border-gray-200 bg-gray-50 p-4 text-gray-800 transition hover:border-gray-300 hover:bg-white hover:shadow-sm">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-600 transition group-hover:text-indigo-600">
                                        <x-untitledui-phone class="h-5 w-5" />
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block text-sm font-medium text-gray-600">
                                            {{ __('frontend.contact.support.phone') }}
                                        </span>
                                        <span class="mt-1 block text-base font-semibold text-gray-900 group-hover:text-indigo-700">
                                            {{ $supportPhone }}
                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ $supportAddressHref }}" target="_blank" rel="noopener noreferrer" class="group flex items-start gap-3 rounded-2xl border border-gray-200 bg-gray-50 p-4 text-gray-800 transition hover:border-gray-300 hover:bg-white hover:shadow-sm">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-600 transition group-hover:text-indigo-600">
                                        <x-untitledui-map-pin class="h-5 w-5" />
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block text-sm font-medium text-gray-600">
                                            {{ __('frontend.contact.support.address') }}
                                        </span>
                                        <span class="mt-1 block text-base font-semibold text-gray-900 group-hover:text-indigo-700">
                                            {{ $supportAddress }}
                                        </span>
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </aside>
            </div>
        </div>
    </div>
@endsection
