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

    $supportTitle = $supportTitle ?? __('frontend.errors.4xx.support_title');
    $supportDescription = $supportDescription ?? __('frontend.errors.4xx.support_description');

    $resolvedCode = isset($exception) && method_exists($exception, 'getStatusCode')
        ? (string) $exception->getStatusCode()
        : ((string) ($code ?? '4xx'));

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
    'title' => $title ?? __('frontend.errors.4xx.title'),
    'description' => $description ?? __('frontend.errors.4xx.description'),
    'primaryAction' => $primaryAction ?? [
        'label' => __('frontend.errors.actions.return_home'),
        'url' => $homeUrl,
    ],
    'secondaryAction' => $secondaryAction ?? [
        'label' => __('frontend.errors.actions.contact_support'),
        'url' => $contactUrl,
    ],
    'correlationId' => $correlationId,
    'supportTitle' => $supportTitle,
    'supportDescription' => $supportDescription,
    'supportPageUrl' => $supportUrl,
    'statusPageUrl' => $statusUrl,
    'showSearch' => $showSearch ?? false,
    'searchTitle' => $searchTitle ?? __('frontend.errors.search.title'),
    'searchPlaceholder' => $searchPlaceholder ?? __('frontend.errors.search.placeholder'),
    'topCategories' => $topCategories ?? [],
    'topCategoriesTitle' => $topCategoriesTitle ?? __('frontend.errors.top_categories.title'),
    'contactCta' => $contactCta ?? null,
    'links' => $links ?? [
        [
            'label' => __('frontend.errors.actions.visit_support_center'),
            'url' => $supportUrl,
            'icon' => 'support',
        ],
        [
            'label' => __('frontend.errors.actions.check_system_status'),
            'url' => $statusUrl,
            'icon' => 'status',
        ],
        [
            'label' => __('frontend.errors.actions.browse_products'),
            'url' => $productsUrl,
            'icon' => 'products',
        ],
        [
            'label' => __('frontend.errors.actions.go_home'),
            'url' => $homeUrl,
            'icon' => 'categories',
        ],
    ],
])
