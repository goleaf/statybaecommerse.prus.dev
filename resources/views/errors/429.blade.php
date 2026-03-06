@php
    $locale = app()->getLocale();
    $homeUrl = route('home', []) ?? url('/');
    $supportUrl = \Illuminate\Support\Facades\Route::has('localized.support.index')
        ? route('localized.support.index', [])
        : url('/support');
    $statusUrl = \Illuminate\Support\Facades\Route::has('status.page')
        ? route('status.page', [])
        : url('/status');
    $productsUrl = \Illuminate\Support\Facades\Route::has('products.index')
        ? route('products.index', [])
        : url('/products');
    $newsUrl = \Illuminate\Support\Facades\Route::has('localized.news.index')
        ? route('localized.news.index', [])
        : url('/news');
@endphp

@extends('errors.layout', [
    'code' => '429',
    'title' => __('frontend.errors.error_429.title'),
    'description' => __('frontend.errors.error_429.description'),
    'primaryAction' => [
        'label' => __('frontend.errors.actions.try_again'),
        'type' => 'refresh',
    ],
    'secondaryAction' => [
        'label' => __('frontend.errors.actions.return_home'),
        'url' => $homeUrl,
    ],
    'links' => [
        [
            'label' => __('frontend.errors.actions.check_system_status'),
            'url' => $statusUrl,
            'icon' => 'status',
        ],
        [
            'label' => __('frontend.errors.actions.browse_products'),
            'url' => $productsUrl,
            'icon' => 'products',
        ],
        [
            'label' => __('frontend.errors.actions.visit_support_center'),
            'url' => $supportUrl,
            'icon' => 'support',
        ],
        [
            'label' => __('frontend.errors.error_429.links.read_news'),
            'url' => $newsUrl,
            'icon' => 'refresh',
        ],
    ],
])


