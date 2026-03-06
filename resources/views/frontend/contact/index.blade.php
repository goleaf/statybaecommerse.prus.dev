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
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="mb-4 text-xl font-semibold text-slate-900">
                            {{ __('frontend.contact.support.title') }}
                        </h2>
                        <ul class="space-y-4 text-slate-700">
                            @if ($supportEmail)
                                <li>
                                    <div class="text-sm uppercase tracking-wide text-slate-500">
                                        {{ __('frontend.contact.support.email') }}
                                    </div>
                                    <a href="mailto:{{ $supportEmail }}" class="mt-1 inline-flex items-center gap-2 text-cyan-700 transition hover:text-cyan-800 hover:underline">
                                        <x-untitledui-mail-02 class="h-5 w-5" />
                                        <span>{{ $supportEmail }}</span>
                                    </a>
                                </li>
                            @endif

                            @if ($company?->phone)
                                <li>
                                    <div class="text-sm uppercase tracking-wide text-slate-500">
                                        {{ __('frontend.contact.support.phone') }}
                                    </div>
                                    <a href="tel:{{ preg_replace('/\s+/', '', $company->phone) }}" class="mt-1 inline-flex items-center gap-2 text-cyan-700 transition hover:text-cyan-800 hover:underline">
                                        <x-untitledui-phone class="h-5 w-5" />
                                        <span>{{ $company->phone }}</span>
                                    </a>
                                </li>
                            @endif

                            @if ($company?->address)
                                <li>
                                    <div class="text-sm uppercase tracking-wide text-slate-500">
                                        {{ __('frontend.contact.support.address') }}
                                    </div>
                                    <div class="mt-1 flex items-start gap-2">
                                        <x-untitledui-info-circle class="h-5 w-5 text-cyan-600" />
                                        <span>{{ $company->address }}</span>
                                    </div>
                                </li>
                            @endif

                            <li>
                                <div class="text-sm uppercase tracking-wide text-slate-500">
                                    {{ __('frontend.contact.support.hours') }}
                                </div>
                                <div class="mt-1 flex items-start gap-2">
                                    <x-untitledui-info-circle class="h-5 w-5 text-cyan-600" />
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
