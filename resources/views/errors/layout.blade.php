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
    $locale = app()->getLocale();
    $homeRoute = route('localized.home', ['locale' => $locale]) ?? url('/');
    $searchRoute = route('search', ['locale' => $locale]) ?? '/search';

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
            </div>
        </div>
    </div>
@endsection
