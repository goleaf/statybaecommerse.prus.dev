@php
    $traceId = $traceId ?? null;
    $correlationId = $correlationId ?? $traceId;
    $locale = app()->getLocale();
    $homeUrl = \Illuminate\Support\Facades\Route::has('localized.home')
        ? route('localized.home', ['locale' => $locale])
        : url('/');
    $supportUrl = \Illuminate\Support\Facades\Route::has('localized.support.index')
        ? route('localized.support.index', ['locale' => $locale])
        : url('/support');
    $statusUrl = \Illuminate\Support\Facades\Route::has('status.page')
        ? route('status.page', ['locale' => $locale])
        : url('/status');
    $contactUrl = \Illuminate\Support\Facades\Route::has('localized.contact.index')
        ? route('localized.contact.index', ['locale' => $locale])
        : url('/contact');
@endphp

@extends('errors.layout', [
    'code' => '500',
    'title' => __('errors.pages.unexpected.title'),
    'description' => __('errors.pages.unexpected.description'),
    'primaryAction' => [
        'label' => __('errors.pages.unexpected.primary'),
        'url' => $homeUrl,
    ],
    'secondaryAction' => [
        'label' => __('errors.pages.unexpected.secondary'),
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
