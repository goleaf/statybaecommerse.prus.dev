@php
    $traceId = $traceId ?? null;
    $correlationId = $correlationId ?? $traceId;
    $locale = app()->getLocale();
    $homeUrl = \Illuminate\Support\Facades\Route::has('home')
        ? route('home', [])
        : url('/');
    $supportUrl = \Illuminate\Support\Facades\Route::has('localized.support.index')
        ? route('localized.support.index', [])
        : url('/support');
    $statusUrl = \Illuminate\Support\Facades\Route::has('status.page')
        ? route('status.page', [])
        : url('/status');
    $contactUrl = \Illuminate\Support\Facades\Route::has('frontend.contact.index')
        ? route('frontend.contact.index', [])
        : url('/contact');
@endphp

@extends('errors.layout', [
    'code' => '500',
    'title' => __('frontend.errors.unexpected.title'),
    'description' => __('frontend.errors.unexpected.description'),
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
            'label' => __('frontend.errors.actions.visit_help_center'),
            'url' => $supportUrl,
            'icon' => 'support',
        ],
        [
            'label' => __('frontend.errors.actions.view_system_status'),
            'url' => $statusUrl,
            'icon' => 'status',
        ],
        [
            'label' => __('frontend.errors.actions.back_to_home'),
            'url' => $homeUrl,
            'icon' => 'categories',
        ],
    ],
    'supportPageUrl' => $supportUrl,
    'statusPageUrl' => $statusUrl,
    'traceId' => $traceId,
    'correlationId' => $correlationId,
])


