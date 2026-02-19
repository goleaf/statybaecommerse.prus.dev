@extends('errors.4xx', [
    'code' => '404',
    'title' => __('frontend.errors.error_404.title'),
    'description' => __('frontend.errors.error_404.description'),
    'showSearch' => true,
    'searchTitle' => __('frontend.errors.error_404.search_title'),
    'searchPlaceholder' => __('frontend.errors.error_404.search_placeholder'),
    'topCategories' => [],
    'topCategoriesTitle' => __('frontend.errors.error_404.top_categories_title'),
    'primaryAction' => [
        'label' => __('frontend.errors.actions.go_home'),
        'url' => url('/'),
    ],
    'secondaryAction' => [
        'label' => __('frontend.errors.actions.go_back'),
        'type' => 'back',
    ],
    'supportTitle' => __('frontend.errors.error_404.support_title'),
    'supportDescription' => __('frontend.errors.error_404.support_description'),
    'contactActions' => [
        [
            'label' => __('frontend.errors.actions.contact_support'),
            'url' => url('/support'),
            'style' => 'primary',
        ],
    ],
])
