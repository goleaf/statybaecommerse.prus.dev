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

    $supportTitle = $supportTitle ?? __('frontend.errors.5xx.support_title');
    $supportDescription = $supportDescription ?? __('frontend.errors.5xx.support_description');

    $resolvedCode = isset($exception) && method_exists($exception, 'getStatusCode')
        ? (string) $exception->getStatusCode()
        : ((string) ($code ?? '5xx'));

    $request = function_exists('request') ? request() : null;
    $correlationId = $correlationId ?? null;
    $correlationHeaderConfig = config('app.correlation_header', 'X-Correlation-ID');
    $correlationHeader = is_string($correlationHeaderConfig) && $correlationHeaderConfig !== ''
        ? $correlationHeaderConfig
        : 'X-Correlation-ID';

    if ($request !== null) {
        $attributeCorrelation = $request->attributes->get('correlation_id');
        if (is_string($attributeCorrelation) && $attributeCorrelation !== '') {
            $correlationId = $attributeCorrelation;
        }

        if ($correlationId === null) {
            $headerCorrelation = (string) $request->headers->get($correlationHeader, '');
            if ($headerCorrelation !== '') {
                $correlationId = $headerCorrelation;
            }
        }
    }

    if ($correlationId === null && app()->bound('request_correlation_id')) {
        $resolvedCorrelation = app()->make('request_correlation_id');
        if (is_string($resolvedCorrelation) && $resolvedCorrelation !== '') {
            $correlationId = $resolvedCorrelation;
        }
    }
@endphp

@extends('errors.layout', [
    'code' => $resolvedCode,
    'title' => $title ?? __('messages.frontend),
    'description' => $description ?? __('messages.frontend),
    'primaryAction' => $primaryAction ?? [
        'label' => __('frontend.errors.actions.try_again'),
        'type' => 'refresh',
    ],
    'secondaryAction' => $secondaryAction ?? [
        'label' => __('frontend.errors.actions.check_system_status'),
        'url' => $statusUrl,
    ],
    'correlationId' => $correlationId,
    'supportTitle' => $supportTitle,
    'supportDescription' => $supportDescription,
    'supportPageUrl' => $supportUrl,
    'statusPageUrl' => $statusUrl,
    'links' => $links ?? [
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
            'label' => __('frontend.errors.actions.contact_support'),
            'url' => $contactUrl,
            'icon' => 'refresh',
        ],
        [
            'label' => __('frontend.errors.actions.go_home'),
            'url' => $homeUrl,
            'icon' => 'categories',
        ],
    ],
])
