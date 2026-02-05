@php
    $locale = app()->getLocale();
    $homeUrl = route('localized.home', ['locale' => $locale]) ?? url('/');
    $supportUrl = \Illuminate\Support\Facades\Route::has('localized.support.index')
        ? route('localized.support.index', ['locale' => $locale])
        : url('/support');
    $statusUrl = \Illuminate\Support\Facades\Route::has('status.page')
        ? route('status.page', ['locale' => $locale])
        : url('/status');
    $newsUrl = \Illuminate\Support\Facades\Route::has('localized.news.index')
        ? route('localized.news.index', ['locale' => $locale])
        : url('/news');
    $contactUrl = \Illuminate\Support\Facades\Route::has('localized.contact.index')
        ? route('localized.contact.index', ['locale' => $locale])
        : url('/contact');
@endphp

@extends('errors.layout', [
    'code' => '500',
    'title' => __('frontend.errors.500.title'),
    'description' => __('frontend.errors.500.description'),
    'primaryAction' => [
        'label' => __('frontend.errors.actions.return_home'),
        'url' => $homeUrl,
    ],
    'secondaryAction' => [
        'label' => __('frontend.errors.actions.contact_support'),
        'url' => $contactUrl,
    ],
    'supportTitle' => __('frontend.errors.500.support_title'),
    'supportDescription' => __('frontend.errors.500.support_description'),
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
