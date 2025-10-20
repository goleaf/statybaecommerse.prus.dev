@php
    $locale = app()->getLocale();
    $homeUrl = route('localized.home', ['locale' => $locale]) ?? url('/');
    $cartUrl = \Illuminate\Support\Facades\Route::has('cart.index')
        ? route('cart.index', ['locale' => $locale])
        : url('/cart');
    $ordersUrl = \Illuminate\Support\Facades\Route::has('orders.index')
        ? route('orders.index', ['locale' => $locale])
        : url('/orders');
    $supportUrl = \Illuminate\Support\Facades\Route::has('localized.support.index')
        ? route('localized.support.index', ['locale' => $locale])
        : url('/support');
    $productsUrl = \Illuminate\Support\Facades\Route::has('products.index')
        ? route('products.index', ['locale' => $locale])
        : url('/products');
@endphp

@extends('errors.layout', [
    'code' => '419',
    'title' => __('Session Expired'),
    'description' => __('Your session has expired due to inactivity or a security check. Please refresh the page and try again.'),
    'primaryAction' => [
        'label' => __('Refresh Page'),
        'type' => 'refresh',
    ],
    'secondaryAction' => [
        'label' => __('Return Home'),
        'url' => $homeUrl,
    ],
    'links' => [
        [
            'label' => __('Review Cart'),
            'url' => $cartUrl,
            'icon' => 'cart',
        ],
        [
            'label' => __('Browse Products'),
            'url' => $productsUrl,
            'icon' => 'products',
        ],
        [
            'label' => __('Track Orders'),
            'url' => $ordersUrl,
            'icon' => 'status',
        ],
        [
            'label' => __('Support Center'),
            'url' => $supportUrl,
            'icon' => 'support',
        ],
    ],
])
