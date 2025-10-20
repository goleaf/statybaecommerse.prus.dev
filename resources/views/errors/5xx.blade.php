@extends('components.layouts.base')

@php
    $status = isset($code) && is_numeric($code) ? (int) $code : 500;
    $correlationId = null;

    if (app()->bound('request_correlation_id')) {
        $resolvedCorrelation = app('request_correlation_id');
        if (is_string($resolvedCorrelation) && $resolvedCorrelation !== '') {
            $correlationId = $resolvedCorrelation;
        }
    }

    if ($correlationId === null) {
        $attributeCorrelation = request()->attributes->get('correlation_id');
        if (is_string($attributeCorrelation) && $attributeCorrelation !== '') {
            $correlationId = $attributeCorrelation;
        }
    }

    $title = __('We ran into an unexpected error');
    $description = __('Our team has been notified and is investigating. Try again shortly or contact support.');

    $supportEmail = config('mail.from.address');

    if (! is_string($supportEmail) || $supportEmail === '') {
        $host = parse_url(config('app.url'), PHP_URL_HOST);
        $supportEmail = is_string($host) && $host !== ''
            ? 'support@' . $host
            : 'support@example.com';
    }
@endphp

@section('title', $title . ' - ' . config('app.name'))

@section('meta')
    <x-seo-meta
        :title="$title . ' - ' . config('app.name')"
        :description="$description"
        :noindex="true" />
@endsection

@section('content')
    <div class="min-h-screen bg-gray-900 flex flex-col justify-center py-16 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-xl">
            <div class="bg-white/95 backdrop-blur border border-gray-200 rounded-3xl px-8 py-12 text-center shadow-xl">
                <p class="text-sm font-semibold text-rose-600 tracking-wide uppercase mb-4">
                    {{ __('Server error') }}
                </p>

                <h1 class="text-6xl font-bold text-gray-900 mb-4">{{ $status }}</h1>
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">{{ $title }}</h2>
                <p class="text-gray-600 mb-8">
                    {{ $description }}
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center mb-10">
                    <a href="{{ route('localized.home', ['locale' => app()->getLocale()]) ?? url('/') }}"
                       class="btn-gradient px-8 py-3 rounded-xl font-semibold text-center">
                        {{ __('Go Home') }}
                    </a>
                    <a href="mailto:{{ $supportEmail }}"
                       class="border-2 border-rose-200 text-rose-600 px-8 py-3 rounded-xl font-semibold hover:border-rose-300 hover:bg-rose-50 transition-colors duration-200">
                        {{ __('Contact support') }}
                    </a>
                </div>

                @if ($correlationId)
                    <div class="bg-rose-50 border border-rose-100 rounded-2xl px-5 py-4 mb-6">
                        <p class="text-sm font-medium text-rose-700 mb-1">{{ __('Reference code for support') }}</p>
                        <code class="text-sm text-rose-600 break-all">{{ $correlationId }}</code>
                    </div>
                @endif

                <p class="text-sm text-gray-500">
                    {{ __('Including the reference code will help us trace the problem faster. Thank you for your patience!') }}
                </p>
            </div>
        </div>
    </div>
@endsection
