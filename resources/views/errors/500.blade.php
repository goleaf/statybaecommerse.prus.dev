@php
    $locale = app()->getLocale();
    $homeUrl = route('localized.home', ['locale' => $locale]) ?? url('/');
    $supportUrl = \Illuminate\Support\Facades\Route::has('localized.support.index')
        ? route('localized.support.index', ['locale' => $locale])
        : url('/support');
    $statusUrl = \Illuminate\Support\Facades\Route::has('status.page')
        ? route('status.page', ['locale' => $locale])
        : url('/status');
    $newsUrl = \Illuminate\Support\Facades\Route::has('localized.news.index')
        ? route('localized.news.index', ['locale' => $locale])
        : url('/news');
    $contactUrl = \Illuminate\Support\Facades\Route::has('localized.contact.index')
        ? route('localized.contact.index', ['locale' => $locale])
        : url('/contact');
@endphp

<!-- Slider Analytics -->

@extends('errors.layout', [
    'code' => '500',
    'title' => __('Something Went Wrong'),
    'description' => __('We hit an unexpected error. Our team has been notified and is already working on a fix.'),
    'primaryAction' => [
        'label' => __('Return Home'),
        'url' => $homeUrl,
    ],
    'secondaryAction' => [
        'label' => __('Contact Support'),
        'url' => $contactUrl,
    ],
    'supportTitle' => __('Need immediate assistance?'),
    'supportDescription' => __('Share the reference ID below with our engineers so we can restore your experience as quickly as possible.'),
    'links' => [
        [
            'label' => __('Check System Status'),
            'url' => $statusUrl,
            'icon' => 'status',
        ],
        [
            'label' => __('Visit Support Center'),
            'url' => $supportUrl,
            'icon' => 'support',
        ],
        [
            'label' => __('Read Updates'),
            'url' => $newsUrl,
            'icon' => 'refresh',
        ],
        [
            'label' => __('Back to Home'),
            'url' => $homeUrl,
            'icon' => 'categories',
        ],
    ],
])
