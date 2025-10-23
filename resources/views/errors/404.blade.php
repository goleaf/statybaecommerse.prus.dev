@php
    $locale = app()->getLocale();
@endphp

@extends('errors.4xx', [
    'code' => '404',
    'title' => __('We couldn\'t find that page'),
    'description' => __('The page you are looking for may have been moved or no longer exists. Double-check the address or explore one of the helpful links below.'),
    'showSearch' => true,
    'primaryAction' => [
        'label' => __('Go Home'),
        'url' => route('localized.home', ['locale' => $locale]) ?? url('/'),
    ],
    'secondaryAction' => [
        'label' => __('Go Back'),
        'type' => 'back',
    ],
    'links' => [
        [
            'label' => __('Browse Categories'),
            'url' => route('localized.categories.index', ['locale' => $locale]),
            'icon' => 'categories',
        ],
        [
            'label' => __('Shop Products'),
            'url' => route('products.index', ['locale' => $locale]) ?? url('/products'),
            'icon' => 'products',
        ],
        [
            'label' => __('Discover Brands'),
            'url' => route('localized.brands.index', ['locale' => $locale]),
            'icon' => 'brands',
        ],
        [
            'label' => __('View Cart'),
            'url' => route('cart.index', ['locale' => $locale]) ?? url('/cart'),
            'icon' => 'cart',
        ],
    ],
])
