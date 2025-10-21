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
    $productsUrl = \Illuminate\Support\Facades\Route::has('products.index')
        ? route('products.index', ['locale' => $locale])
        : url('/products');
@endphp

@extends('errors.layout', [
    'code' => '503',
    'title' => __('We\'ll Be Right Back'),
    'description' => __('We are temporarily offline for maintenance. Thanks for your patience while we improve your experience.'),
    'primaryAction' => [
        'label' => __('View Status Page'),
        'url' => $statusUrl,
    ],
    'secondaryAction' => [
        'label' => __('Return Home'),
        'url' => $homeUrl,
    ],
    'supportTitle' => __('Need an update?'),
    'supportDescription' => __('Check our status page or reach out to support and we will notify you as soon as we are back.'),
    'links' => [
        [
            'label' => __('Browse Products'),
            'url' => $productsUrl,
            'icon' => 'products',
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
            'label' => __('Check System Status'),
            'url' => $statusUrl,
            'icon' => 'status',
        ],
    ],
])
