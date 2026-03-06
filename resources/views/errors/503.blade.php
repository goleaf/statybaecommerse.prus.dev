@php
    $locale = app()->getLocale();
    $homeUrl = route('home', []) ?? url('/');
    $supportUrl = \Illuminate\Support\Facades\Route::has('localized.support.index')
        ? route('localized.support.index', [])
        : url('/support');
    $statusUrl = \Illuminate\Support\Facades\Route::has('status.page')
        ? route('status.page', [])
        : url('/status');
    $newsUrl = \Illuminate\Support\Facades\Route::has('localized.news.index')
        ? route('localized.news.index', [])
        : url('/news');
    $productsUrl = \Illuminate\Support\Facades\Route::has('products.index')
        ? route('products.index', [])
        : url('/products');
@endphp

@extends('errors.layout', [
    'code' => '503',
    'title' => __('frontend.errors.error_503.title'),
    'description' => __('frontend.errors.error_503.description'),
    'primaryAction' => [
        'label' => __('frontend.errors.actions.view_status_page'),
        'url' => $statusUrl,
    ],
    'secondaryAction' => [
        'label' => __('frontend.errors.actions.return_home'),
        'url' => $homeUrl,
    ],
    'supportTitle' => __('frontend.errors.error_503.support_title'),
    'supportDescription' => __('frontend.errors.error_503.support_description'),
    'links' => [
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
            'label' => __('frontend.errors.actions.read_updates'),
            'url' => $newsUrl,
            'icon' => 'refresh',
        ],
        [
            'label' => __('frontend.errors.actions.check_system_status'),
            'url' => $statusUrl,
            'icon' => 'status',
        ],
    ],
])


