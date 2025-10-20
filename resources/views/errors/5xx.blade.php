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

    $supportTitle = $supportTitle ?? __('Our team is already looking into this');
    $supportDescription = $supportDescription ?? __('Share the reference ID with our support specialists so we can investigate the issue and keep you updated.');

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
    'title' => $title ?? __('Something went wrong on our side'),
    'description' => $description ?? __('We hit an unexpected error while handling your request. Our team has been alerted and is already looking into it.'),
    'primaryAction' => $primaryAction ?? [
        'label' => __('Try Again'),
        'type' => 'refresh',
    ],
    'secondaryAction' => $secondaryAction ?? [
        'label' => __('Check System Status'),
        'url' => $statusUrl,
    ],
    'correlationId' => $correlationId,
    'supportTitle' => $supportTitle,
    'supportDescription' => $supportDescription,
    'supportPageUrl' => $supportUrl,
    'statusPageUrl' => $statusUrl,
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
