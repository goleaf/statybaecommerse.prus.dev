@php
    $locale = app()->getLocale();
    $homeUrl = route('home', []) ?? url('/');
    $supportUrl = \Illuminate\Support\Facades\Route::has('localized.support.index')
        ? route('localized.support.index', [])
        : url('/support');
    $statusUrl = \Illuminate\Support\Facades\Route::has('status.page')
        ? route('status.page', [])
        : url('/status');
    $newsUrl = \Illuminate\Support\Facades\Route::has('localized.news.index')
        ? route('localized.news.index', [])
        : url('/news');
    $contactUrl = \Illuminate\Support\Facades\Route::has('frontend.contact.index')
        ? route('frontend.contact.index', [])
        : url('/contact');
@endphp

@extends('errors.layout', [
    'code' => '500',
    'title' => __('frontend.errors.error_500.title'),
    'description' => __('frontend.errors.error_500.description'),
    'primaryAction' => [
        'label' => __('frontend.errors.actions.return_home'),
        'url' => $homeUrl,
    ],
    'secondaryAction' => [
        'label' => __('frontend.errors.actions.contact_support'),
        'url' => $contactUrl,
    ],
    'supportTitle' => __('frontend.errors.error_500.support_title'),
    'supportDescription' => __('frontend.errors.error_500.support_description'),
    'links' => [
        [
            'label' => __('frontend.errors.actions.check_system_status'),
            'url' => $statusUrl,
            'icon' => 'status',
        ],
        [
            'label' => __('frontend.errors.actions.visit_support_center'),
            'url' => $supportUrl,
            'icon' => 'support',
        ],
        [
            'label' => __('frontend.errors.actions.read_updates'),
            'url' => $newsUrl,
            'icon' => 'refresh',
        ],
        [
            'label' => __('frontend.errors.actions.back_to_home'),
            'url' => $homeUrl,
            'icon' => 'categories',
        ],
    ],
])


