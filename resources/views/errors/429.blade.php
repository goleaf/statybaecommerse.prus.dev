@php
    $locale = app()->getLocale();
    $homeUrl = route('localized.home', ['locale' => $locale]) ?? url('/');
    $supportUrl = \Illuminate\Support\Facades\Route::has('localized.support.index')
        ? route('localized.support.index', ['locale' => $locale])
        : url('/support');
    $statusUrl = \Illuminate\Support\Facades\Route::has('status.page')
        ? route('status.page', ['locale' => $locale])
        : url('/status');
    $productsUrl = \Illuminate\Support\Facades\Route::has('products.index')
        ? route('products.index', ['locale' => $locale])
        : url('/products');
    $newsUrl = \Illuminate\Support\Facades\Route::has('localized.news.index')
        ? route('localized.news.index', ['locale' => $locale])
        : url('/news');
@endphp

@extends('errors.layout', [
    'code' => '429',
    'title' => __('Too Many Requests'),
    'description' => __('We are receiving too many requests right now. Take a short break and try again in a moment.'),
    'primaryAction' => [
        'label' => __('Try Again'),
        'type' => 'refresh',
    ],
    'secondaryAction' => [
        'label' => __('Return Home'),
        'url' => $homeUrl,
    ],
    'links' => [
        [
            'label' => __('Check System Status'),
            'url' => $statusUrl,
            'icon' => 'status',
        ],
        [
            'label' => __('Browse Products'),
            'url' => $productsUrl,
            'icon' => 'products',
        ],
        [
            'label' => __('Support Center'),
            'url' => $supportUrl,
            'icon' => 'support',
        ],
        [
            'label' => __('Read the Latest News'),
            'url' => $newsUrl,
            'icon' => 'refresh',
        ],
    ],
])
