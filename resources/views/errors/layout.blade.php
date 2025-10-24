@php
    $code = $code ?? 'Error';
    $title = $title ?? __('Something went wrong');
    $description = $description ?? __('An unexpected error occurred.');
    $primaryAction = $primaryAction ?? null;
    $secondaryAction = $secondaryAction ?? null;
    $showSearch = $showSearch ?? false;
    $searchTitle = $searchTitle ?? __('Search for what you need');
    $searchPlaceholder = $searchPlaceholder ?? __('Search products...');
    $links = $links ?? [];
    $topCategoriesTitle = $topCategoriesTitle ?? __('Top Categories');
    $topCategories = collect($topCategories ?? [])
        ->filter(static fn ($category) => is_array($category) && filled($category['label'] ?? null) && filled($category['url'] ?? null))
        ->values();
    $contactCta = is_array($contactCta ?? null) ? $contactCta : null;
    $locale = app()->getLocale();
    $homeRoute = \Illuminate\Support\Facades\Route::has('localized.home')
        ? route('localized.home', ['locale' => $locale])
        : (\Illuminate\Support\Facades\Route::has('home')
            ? route('home')
            : url('/'));
    $searchRoute = \Illuminate\Support\Facades\Route::has('localized.search')
        ? route('localized.search', ['locale' => $locale])
        : (\Illuminate\Support\Facades\Route::has('frontend.search.index')
            ? route('frontend.search.index')
            : (\Illuminate\Support\Facades\Route::has('search')
                ? route('search')
                : url('/search')));
    $correlationHeaderConfig = config('app.correlation_header', 'X-Correlation-ID');
    $correlationHeader = is_string($correlationHeaderConfig) && $correlationHeaderConfig !== ''
        ? $correlationHeaderConfig
        : 'X-Correlation-ID';

    $traceId = $traceId ?? null;
    $correlationId = $correlationId ?? null;

    if (! is_string($traceId) || $traceId === '') {
        if (is_string($correlationId) && $correlationId !== '') {
            $traceId = $correlationId;
        }
    }

    if (! is_string($traceId) || $traceId === '') {
        if (app()->bound('request_correlation_id')) {
            $resolvedCorrelation = app()->make('request_correlation_id');
            if (is_string($resolvedCorrelation) && $resolvedCorrelation !== '') {
                $traceId = $resolvedCorrelation;
            }
        }
    }

    if ((! is_string($traceId) || $traceId === '') && function_exists('request')) {
        $request = request();

        if ($request !== null) {
            $attributeCorrelation = $request->attributes->get('correlation_id');
            if (is_string($attributeCorrelation) && $attributeCorrelation !== '') {
                $traceId = $attributeCorrelation;
            }

            if (! is_string($traceId) || $traceId === '') {
                $headerCorrelation = (string) $request->headers->get($correlationHeader, '');
                if ($headerCorrelation !== '') {
                    $traceId = $headerCorrelation;
                }
            }
        }
    }

    if (! is_string($traceId) || $traceId === '') {
        $traceId = null;
    }

    $correlationId = $traceId;

    $localizedSupportEmail = __('company_email');
    $fallbackSupportEmail = config('mail.from.address', 'support@example.com');
    $resolvedSupportEmail = $localizedSupportEmail !== 'company_email'
        ? $localizedSupportEmail
        : $fallbackSupportEmail;

    $supportEmail = $supportEmail ?? $resolvedSupportEmail;
    $supportTitle = $supportTitle ?? __('Need more help?');
    $supportDescription = $supportDescription ?? __('If the problem continues, contact our support team and share the trace ID below so we can assist you quickly.');
    $statusPageUrl = $statusPageUrl ?? (\Illuminate\Support\Facades\Route::has('status.page')
        ? route('status.page', ['locale' => $locale])
        : url('/status'));
    $supportPageUrl = $supportPageUrl ?? (\Illuminate\Support\Facades\Route::has('localized.support.index')
        ? route('localized.support.index', ['locale' => $locale])
        : url('/support'));

    $iconLibrary = [
        'categories' => '<svg class="w-8 h-8 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 11h10"></path></svg>',
        'products' => '<svg class="w-8 h-8 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>',
        'brands' => '<svg class="w-8 h-8 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>',
        'cart' => '<svg class="w-8 h-8 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path></svg>',
        'support' => '<svg class="w-8 h-8 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 13a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'status' => '<svg class="w-8 h-8 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a1 1 0 011-1h4a1 1 0 011 1v2m-5-4h4m5 4V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2z"/></svg>',
        'refresh' => '<svg class="w-8 h-8 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6M20 20v-6h-6M5.636 18.364A9 9 0 0118.364 5.636M18.364 18.364A9 9 0 015.636 5.636"/></svg>',
    ];
@endphp

@extends('components.layouts.base')

@section('title', $title . ' - ' . config('app.name'))

@section('meta')
    <x-seo-meta
        :title="$title . ' - ' . config('app.name')"
        :description="$description"
        :noindex="true" />
@endsection

@section('content')
    <div class="min-h-screen bg-gradient-to-b from-white via-gray-50 to-gray-100 flex flex-col justify-center py-16 px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-2xl">
            <div class="bg-white shadow-soft rounded-3xl p-10 text-center border border-gray-100">
                <div class="mb-6 flex justify-center">
                    <span class="inline-flex items-center justify-center rounded-full bg-blue-100 text-blue-600 h-16 w-16 font-semibold text-2xl">{{ $code }}</span>
                </div>

                <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $title }}</h1>
                <p class="text-gray-600 mb-8 max-w-xl mx-auto">{{ $description }}</p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    @if($primaryAction)
                        @php($primaryType = $primaryAction['type'] ?? 'link')
                        @if($primaryType === 'refresh')
                            <button onclick="window.location.reload()"
                                    class="btn-gradient px-8 py-3 rounded-xl font-semibold text-center">
                                {{ $primaryAction['label'] ?? __('Refresh Page') }}
                            </button>
                        @elseif($primaryType === 'back')
                            <button onclick="history.back()"
                                    class="btn-gradient px-8 py-3 rounded-xl font-semibold text-center">
                                {{ $primaryAction['label'] ?? __('Go Back') }}
                            </button>
                        @else
                            <a href="{{ $primaryAction['url'] ?? $homeRoute }}"
                               class="btn-gradient px-8 py-3 rounded-xl font-semibold text-center">
                                {{ $primaryAction['label'] ?? __('Go Home') }}
                            </a>
                        @endif
                    @endif

                    @if($secondaryAction)
                        @php($secondaryType = $secondaryAction['type'] ?? 'link')
                        @if($secondaryType === 'back')
                            <button onclick="history.back()"
                                    class="border-2 border-gray-200 text-gray-700 px-8 py-3 rounded-xl font-semibold hover:border-gray-400 hover:bg-gray-50 transition-colors duration-200">
                                {{ $secondaryAction['label'] ?? __('Go Back') }}
                            </button>
                        @elseif($secondaryType === 'refresh')
                            <button onclick="window.location.reload()"
                                    class="border-2 border-gray-200 text-gray-700 px-8 py-3 rounded-xl font-semibold hover:border-gray-400 hover:bg-gray-50 transition-colors duration-200">
                                {{ $secondaryAction['label'] ?? __('Refresh Page') }}
                            </button>
                        @else
                            <a href="{{ $secondaryAction['url'] ?? $homeRoute }}"
                               class="border-2 border-gray-200 text-gray-700 px-8 py-3 rounded-xl font-semibold hover:border-gray-400 hover:bg-gray-50 transition-colors duration-200">
                                {{ $secondaryAction['label'] ?? __('Contact Support') }}
                            </a>
                        @endif
                    @endif
                </div>

                @if($showSearch)
                    <div class="mt-12 max-w-xl mx-auto text-left">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4 text-center">{{ $searchTitle }}</h3>
                        <form action="{{ $searchRoute }}" method="GET" class="flex gap-2">
                            <input type="search"
                                   name="q"
                                   placeholder="{{ $searchPlaceholder }}"
                                   class="flex-1 px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                            <button type="submit"
                                    class="bg-blue-600 text-white px-6 py-3 rounded-xl hover:bg-blue-700 transition-colors duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                @endif

                @if($topCategories->isNotEmpty())
                    <div class="mt-12">
                        <h3 class="text-lg font-semibold text-gray-700 mb-6 text-center">{{ $topCategoriesTitle }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($topCategories as $category)
                                <a href="{{ $category['url'] }}"
                                   class="p-5 bg-white rounded-2xl border border-gray-200 hover:border-blue-300 hover:shadow-soft transition-all duration-200">
                                    <div class="flex items-start gap-4">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600 font-semibold">
                                            {{ $category['label'][0] ?? '•' }}
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="text-base font-semibold text-gray-900">{{ $category['label'] }}</span>
                                                @if(!empty($category['product_count']))
                                                    <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full">{{ trans_choice(__('frontend.collections.stats.products'), (int) $category['product_count'], ['count' => (int) $category['product_count']]) }}</span>
                                                @endif
                                            </div>
                                            @if(!empty($category['description']))
                                                <p class="mt-2 text-sm text-gray-500">{{ $category['description'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(!empty($links))
                    <div class="mt-12">
                        <h3 class="text-lg font-semibold text-gray-700 mb-6">{{ __('Popular Pages') }}</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach($links as $link)
                                <a href="{{ $link['url'] ?? $homeRoute }}"
                                   class="text-center p-4 bg-white rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-soft transition-all duration-200">
                                    {!! $iconLibrary[$link['icon'] ?? 'categories'] ?? '' !!}
                                    <span class="block text-sm font-medium text-gray-700">{{ $link['label'] ?? __('Explore') }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($contactCta)
                    <div class="mt-12">
                        <div class="bg-blue-50 border border-blue-100 rounded-3xl p-8 text-left sm:flex sm:items-center sm:justify-between sm:gap-8">
                            <div class="max-w-xl">
                                <h3 class="text-2xl font-semibold text-gray-900 mb-3">{{ $contactCta['title'] ?? __('Need more help?') }}</h3>
                                <p class="text-sm text-gray-600">{{ $contactCta['description'] ?? __('Reach out to our support team and we will guide you to the right place.') }}</p>
                            </div>
                            @if(!empty($contactCta['actions']) && is_array($contactCta['actions']))
                                <div class="mt-6 sm:mt-0 flex flex-col sm:flex-row gap-3">
                                    @foreach($contactCta['actions'] as $action)
                                        @continue(!is_array($action) || empty($action['label']) || empty($action['url']))
                                        @php($style = $action['style'] ?? 'primary')
                                        <a href="{{ $action['url'] }}"
                                           @class([
                                               'px-6 py-3 rounded-xl font-semibold text-center transition-colors duration-200',
                                               'bg-blue-600 text-white hover:bg-blue-700' => $style === 'primary',
                                               'border-2 border-blue-200 text-blue-700 hover:border-blue-300 hover:bg-blue-100' => $style !== 'primary',
                                           ])>
                                            {{ $action['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="mt-12 border-t border-gray-100 pt-8">
                    <h2 class="text-lg font-semibold text-gray-800 text-center mb-3">{{ $supportTitle }}</h2>
                    <p class="text-sm text-gray-500 max-w-xl mx-auto text-center">{{ $supportDescription }}</p>

                    @if($traceId !== null)
                        <div class="mt-6 flex items-center justify-center">
                            <div class="inline-flex flex-col sm:flex-row sm:items-center gap-2 bg-gray-50 border border-gray-200 rounded-xl px-5 py-3">
                                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Trace ID') }}</span>
                                <span class="font-mono text-sm text-gray-800">{{ $traceId }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="mt-6 flex flex-col sm:flex-row justify-center gap-4">
                        @if(is_string($supportEmail) && $supportEmail !== '')
                            <a href="mailto:{{ $supportEmail }}"
                               class="border-2 border-gray-200 text-gray-700 px-6 py-3 rounded-xl font-semibold hover:border-gray-400 hover:bg-gray-50 transition-colors duration-200">
                                {{ __('Email Support') }}
                            </a>
                        @endif

                        <a href="{{ $supportPageUrl }}"
                           class="border-2 border-gray-200 text-gray-700 px-6 py-3 rounded-xl font-semibold hover:border-gray-400 hover:bg-gray-50 transition-colors duration-200">
                            {{ __('Visit Help Center') }}
                        </a>

                        <a href="{{ $statusPageUrl }}"
                           class="border-2 border-gray-200 text-gray-700 px-6 py-3 rounded-xl font-semibold hover:border-gray-400 hover:bg-gray-50 transition-colors duration-200">
                            {{ __('View System Status') }}
                        </a>
                    </div>

                    <p class="mt-6 text-xs text-gray-400 text-center">{{ __('Share the trace ID with our support team so we can find your request faster.') }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
