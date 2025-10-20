@extends('components.layouts.base')

@php
    $status = isset($code) && is_numeric($code) ? (int) $code : 400;
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

    $titles = [
        400 => __('We could not process that request'),
        401 => __('Authentication required'),
        403 => __('You do not have access to this page'),
        405 => __('Method not allowed'),
        419 => __('Your session has expired'),
        429 => __('Too many requests'),
    ];

    $descriptions = [
        400 => __('We could not process the data that was sent. Please review your request and try again.'),
        401 => __('Please log in to continue. If you believe this is an error, contact support.'),
        403 => __('You do not have permission to access this area. Reach out if you need assistance.'),
        405 => __('The action you attempted is not available for this page.'),
        419 => __('For your security, the page has expired. Refresh and try again.'),
        429 => __('You have tried to do that too many times. Please wait a moment and try again.'),
    ];

    $title = $titles[$status] ?? __('Something went wrong');
    $description = $descriptions[$status] ?? __('We could not complete your request. Please try again in a moment.');
@endphp

@section('title', $title . ' - ' . config('app.name'))

@section('meta')
    <x-seo-meta
        :title="$title . ' - ' . config('app.name')"
        :description="$description"
        :noindex="true" />
@endsection

@section('content')
    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-xl">
            <div class="bg-white shadow-soft rounded-3xl px-8 py-12 text-center border border-gray-100">
                <p class="text-sm font-semibold text-blue-600 tracking-wide uppercase mb-4">
                    {{ __('Client error') }}
                </p>

                <h1 class="text-6xl font-bold text-gray-900 mb-4">{{ $status }}</h1>
                <h2 class="text-2xl font-semibold text-gray-700 mb-4">{{ $title }}</h2>
                <p class="text-gray-600 mb-8">
                    {{ $description }}
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center mb-10">
                    <a href="{{ route('localized.home', ['locale' => app()->getLocale()]) ?? url('/') }}"
                       class="btn-gradient px-8 py-3 rounded-xl font-semibold text-center">
                        {{ __('Go Home') }}
                    </a>
                    <button onclick="history.back()"
                            class="border-2 border-gray-200 text-gray-700 px-8 py-3 rounded-xl font-semibold hover:border-gray-400 hover:bg-gray-50 transition-colors duration-200">
                        {{ __('Go Back') }}
                    </button>
                </div>

                @if ($correlationId)
                    <div class="bg-gray-50 border border-dashed border-gray-200 rounded-2xl px-5 py-4 mb-6">
                        <p class="text-sm font-medium text-gray-700 mb-1">{{ __('Reference code for support') }}</p>
                        <code class="text-sm text-gray-500 break-all">{{ $correlationId }}</code>
                    </div>
                @endif

                <p class="text-sm text-gray-500">
                    {{ __('Still stuck? Our support team can help — include the reference code above so we can track the issue quickly.') }}
                </p>
            </div>
        </div>
    </div>
@endsection
