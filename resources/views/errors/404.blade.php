@php
    $locale = app()->getLocale();
@endphp

@extends('errors.layout', [
    'code' => '404',
    'title' => __('Page Not Found'),
    'description' => __('The page you are looking for could not be found. It might have been moved, deleted, or you entered the wrong URL.'),
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
            'label' => __('Categories'),
            'url' => route('localized.categories.index', ['locale' => $locale]),
            'icon' => 'categories',
        ],
        [
            'label' => __('Products'),
            'url' => route('products.index', ['locale' => $locale]) ?? url('/products'),
            'icon' => 'products',
        ],
        [
            'label' => __('Brands'),
            'url' => route('localized.brands.index', ['locale' => $locale]),
            'icon' => 'brands',
        ],
        [
            'label' => __('Cart'),
            'url' => route('cart.index', ['locale' => $locale]) ?? url('/cart'),
            'icon' => 'cart',
        ],
    ],
])
