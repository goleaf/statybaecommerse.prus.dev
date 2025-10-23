@php
    use Illuminate\Support\Facades\Route;

    $locale = app()->getLocale();
    $homeUrl = route('localized.home', ['locale' => $locale]) ?? url('/');
    $supportUrl = Route::has('localized.support.index')
        ? route('localized.support.index', ['locale' => $locale])
        : url('/support');
    $contactUrl = Route::has('localized.contact.index')
        ? route('localized.contact.index', ['locale' => $locale])
        : url('/contact');
    $productsUrl = Route::has('products.index')
        ? route('products.index', ['locale' => $locale])
        : url('/products');
    $statusUrl = Route::has('status.page')
        ? route('status.page', ['locale' => $locale])
        : url('/status');

    $resolvedCode = isset($exception) && method_exists($exception, 'getStatusCode')
        ? (string) $exception->getStatusCode()
        : ((string) ($code ?? '4xx'));
@endphp

@extends('errors.layout', [
    'code' => $resolvedCode,
    'title' => $title ?? __('We can\'t complete that request'),
    'description' => $description ?? __('The page you requested could not be processed right now. Please check the address or try again.'),
    'primaryAction' => $primaryAction ?? [
        'label' => __('Return Home'),
        'url' => $homeUrl,
    ],
    'secondaryAction' => $secondaryAction ?? [
        'label' => __('Contact Support'),
        'url' => $contactUrl,
    ],
    'links' => $links ?? [
        [
            'label' => __('Visit Support Center'),
            'url' => $supportUrl,
            'icon' => 'support',
        ],
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
            'label' => __('Go Home'),
            'url' => $homeUrl,
            'icon' => 'categories',
        ],
    ],
])
