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
    'title' => __('messages.frontend),
    '),
    'description' => __('messages.frontend),
    '),
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
            'label' => __('frontend.errors.429.links.read_news'),
            'url' => $newsUrl,
            'icon' => 'refresh',
        ],
    ],
])
