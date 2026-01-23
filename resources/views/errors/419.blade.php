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
    'title' => __('frontend.errors.419.title'),
    'description' => __('frontend.errors.419.description'),
    'primaryAction' => [
        'label' => __('frontend.errors.actions.refresh_page'),
        'type' => 'refresh',
    ],
    'secondaryAction' => [
        'label' => __('frontend.errors.actions.return_home'),
        'url' => $homeUrl,
    ],
    'links' => [
        [
            'label' => __('frontend.errors.419.links.review_cart'),
            'url' => $cartUrl,
            'icon' => 'cart',
        ],
        [
            'label' => __('frontend.errors.actions.browse_products'),
            'url' => $productsUrl,
            'icon' => 'products',
        ],
        [
            'label' => __('frontend.errors.419.links.track_orders'),
            'url' => $ordersUrl,
            'icon' => 'status',
        ],
        [
            'label' => __('frontend.errors.actions.visit_support_center'),
            'url' => $supportUrl,
            'icon' => 'support',
        ],
    ],
])
