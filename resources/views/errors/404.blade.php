@extends('layouts.app')

@section('title', __('Page Not Found') . ' - ' . config('app.name'))

@section('meta')
    <x-seo-meta
                :title="__('Page Not Found') . ' - ' . config('app.name')"
                :description="__('The page you are looking for could not be found.')"
                :noindex="true" />
@endsection

@section('content')
    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="text-center">
                {{-- 404 Illustration --}}
                <div class="mx-auto h-64 w-64 mb-8">
                    <svg class="w-full h-full text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                              d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.29-1.009-5.824-2.709M15 12a3 3 0 11-6 0 3 3 0 016 0z">
                        </path>
                    </svg>
                </div>

                {{-- Error Message --}}
                <h1 class="text-6xl font-bold text-gray-900 mb-4">404</h1>
                <h2 class="text-2xl font-semibold text-gray-700 mb-4">{{ __('Page Not Found') }}</h2>
                <p class="text-gray-600 mb-8 max-w-md mx-auto">
                    {{ __('The page you are looking for could not be found. It might have been moved, deleted, or you entered the wrong URL.') }}
                </p>

                {{-- Action Buttons --}}
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('localized.home', ['locale' => app()->getLocale()]) ?? url('/') }}"
                       class="btn-gradient px-8 py-3 rounded-xl font-semibold text-center">
                        {{ __('Go Home') }}
                    </a>
                    <button onclick="history.back()"
                            class="border-2 border-gray-300 text-gray-700 px-8 py-3 rounded-xl font-semibold hover:border-gray-400 hover:bg-gray-50 transition-colors duration-200">
                        {{ __('Go Back') }}
                    </button>
                </div>

                {{-- Search Box --}}
                <div class="mt-12 max-w-md mx-auto">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">{{ __('Search for what you need') }}</h3>
                    <form action="{{ route('search', ['locale' => app()->getLocale()]) ?? '/search' }}" method="GET"
                          class="flex gap-2">
                        <input type="search"
                               name="q"
                               placeholder="{{ __('Search products...') }}"
                               class="flex-1 px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <button type="submit"
                                class="bg-blue-600 text-white px-6 py-3 rounded-xl hover:bg-blue-700 transition-colors duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                    </form>
                </div>

                {{-- Popular Links --}}
                <div class="mt-12">
                    <h3 class="text-lg font-semibold text-gray-700 mb-6">{{ __('Popular Pages') }}</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a href="{{ route('localized.categories.index', ['locale' => app()->getLocale()]) }}"
                           class="text-center p-4 bg-white rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-soft transition-all duration-200">
                            <svg class="w-8 h-8 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 11h10">
                                </path>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">{{ __('Categories') }}</span>
                        </a>

                        <a href="{{ route('products.index', ['locale' => app()->getLocale()]) ?? '/products' }}"
                           class="text-center p-4 bg-white rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-soft transition-all duration-200">
                            <svg class="w-8 h-8 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">{{ __('Products') }}</span>
                        </a>

                        <a href="{{ route('localized.brands.index', ['locale' => app()->getLocale()]) }}"
                           class="text-center p-4 bg-white rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-soft transition-all duration-200">
                            <svg class="w-8 h-8 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                </path>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">{{ __('Brands') }}</span>
                        </a>

                        <a href="{{ route('cart.index', ['locale' => app()->getLocale()]) ?? '/cart' }}"
                           class="text-center p-4 bg-white rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-soft transition-all duration-200">
                            <svg class="w-8 h-8 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01">
                                </path>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">{{ __('Cart') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
