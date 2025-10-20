@php
    use Illuminate\Support\Facades\Route;

    $locale = app()->getLocale();
    $homeUrl = route('localized.home', ['locale' => $locale]) ?? url('/');
    $statusUrl = Route::has('status.page')
        ? route('status.page', ['locale' => $locale])
        : url('/status');
    $supportUrl = Route::has('localized.support.index')
        ? route('localized.support.index', ['locale' => $locale])
        : url('/support');
    $contactUrl = Route::has('localized.contact.index')
        ? route('localized.contact.index', ['locale' => $locale])
        : url('/contact');

    $resolvedCode = isset($exception) && method_exists($exception, 'getStatusCode')
        ? (string) $exception->getStatusCode()
        : ((string) ($code ?? '5xx'));
@endphp

@extends('errors.layout', [
    'code' => $resolvedCode,
    'title' => $title ?? __('Something went wrong on our side'),
    'description' => $description ?? __('We hit an unexpected error while handling your request. Our team has been alerted and is already looking into it.'),
    'primaryAction' => $primaryAction ?? [
        'label' => __('Return Home'),
        'url' => $homeUrl,
    ],
    'secondaryAction' => $secondaryAction ?? [
        'label' => __('Check System Status'),
        'url' => $statusUrl,
    ],
    'links' => $links ?? [
        [
            'label' => __('Check System Status'),
            'url' => $statusUrl,
            'icon' => 'status',
        ],
        [
            'label' => __('Visit Support Center'),
            'url' => $supportUrl,
            'icon' => 'support',
        ],
        [
            'label' => __('Contact Support'),
            'url' => $contactUrl,
            'icon' => 'refresh',
        ],
        [
            'label' => __('Go Home'),
            'url' => $homeUrl,
            'icon' => 'categories',
        ],
    ],
])
