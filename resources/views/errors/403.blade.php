@php
    $locale = app()->getLocale();
    $homeUrl = route('home', []) ?? url('/');
    $profileUrl = \Illuminate\Support\Facades\Route::has('profile.overview')
        ? route('profile.overview', [])
        : url('/profile');
    $ordersUrl = \Illuminate\Support\Facades\Route::has('orders.index')
        ? route('orders.index', [])
        : url('/orders');
    $supportUrl = \Illuminate\Support\Facades\Route::has('localized.support.index')
        ? route('localized.support.index', [])
        : url('/support');
    $contactUrl = \Illuminate\Support\Facades\Route::has('frontend.contact.index')
        ? route('frontend.contact.index', [])
        : url('/contact');
    $productsUrl = \Illuminate\Support\Facades\Route::has('products.index')
        ? route('products.index', [])
        : url('/products');
@endphp

@extends('errors.layout', [
    'code' => '403',
    'title' => __('frontend.errors.error_403.title'),
    'description' => __('frontend.errors.error_403.description'),
    'primaryAction' => [
        'label' => __('frontend.errors.actions.return_home'),
        'url' => $homeUrl,
    ],
    'secondaryAction' => [
        'label' => __('frontend.errors.actions.contact_support'),
        'url' => $contactUrl,
    ],
    'links' => [
        [
            'label' => __('frontend.errors.error_403.links.view_account'),
            'url' => $profileUrl,
            'icon' => 'status',
        ],
        [
            'label' => __('frontend.errors.error_403.links.order_status'),
            'url' => $ordersUrl,
            'icon' => 'products',
        ],
        [
            'label' => __('frontend.errors.error_403.links.support_center'),
            'url' => $supportUrl,
            'icon' => 'support',
        ],
        [
            'label' => __('frontend.errors.error_403.links.shop_new_arrivals'),
            'url' => $productsUrl,
            'icon' => 'categories',
        ],
    ],
])


