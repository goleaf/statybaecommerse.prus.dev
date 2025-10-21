@php
    $locale = app()->getLocale();
    $homeUrl = route('localized.home', ['locale' => $locale]) ?? url('/');
    $profileUrl = \Illuminate\Support\Facades\Route::has('profile.overview')
        ? route('profile.overview', ['locale' => $locale])
        : url('/profile');
    $ordersUrl = \Illuminate\Support\Facades\Route::has('orders.index')
        ? route('orders.index', ['locale' => $locale])
        : url('/orders');
    $supportUrl = \Illuminate\Support\Facades\Route::has('localized.support.index')
        ? route('localized.support.index', ['locale' => $locale])
        : url('/support');
    $contactUrl = \Illuminate\Support\Facades\Route::has('localized.contact.index')
        ? route('localized.contact.index', ['locale' => $locale])
        : url('/contact');
    $productsUrl = \Illuminate\Support\Facades\Route::has('products.index')
        ? route('products.index', ['locale' => $locale])
        : url('/products');
@endphp

@extends('errors.layout', [
    'code' => '403',
    'title' => __('Access Denied'),
    'description' => __('You do not have permission to access this page. If you believe this is an error, please contact our support team.'),
    'primaryAction' => [
        'label' => __('Return Home'),
        'url' => $homeUrl,
    ],
    'secondaryAction' => [
        'label' => __('Contact Support'),
        'url' => $contactUrl,
    ],
    'links' => [
        [
            'label' => __('View Account'),
            'url' => $profileUrl,
            'icon' => 'status',
        ],
        [
            'label' => __('Order Status'),
            'url' => $ordersUrl,
            'icon' => 'products',
        ],
        [
            'label' => __('Support Center'),
            'url' => $supportUrl,
            'icon' => 'support',
        ],
        [
            'label' => __('Shop New Arrivals'),
            'url' => $productsUrl,
            'icon' => 'categories',
        ],
    ],
])
