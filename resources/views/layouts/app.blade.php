<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth" data-theme="light"
      style="scroll-behavior: smooth;">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Title --}}
    <title>@yield('title', config('app.name'))</title>

    {{-- Enhanced SEO Meta Tags --}}
    @hasSection('meta')
        @yield('meta')
    @else
        <meta name="description" content="{{ $description ?? __('meta_description_home') }}">
        <meta name="keywords" content="{{ $keywords ?? __('meta_keywords') }}">
        <meta name="author" content="{{ config('app.name') }}">
        <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
        <meta name="googlebot" content="index, follow">
        <meta name="bingbot" content="index, follow">

        {{-- Open Graph / Facebook --}}
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="@yield('title', config('app.name'))">
        <meta property="og:description" content="{{ $description ?? __('meta_description_home') }}">
        <meta property="og:image" content="{{ asset('images/og-image.jpg') }}">
        <meta property="og:site_name" content="{{ config('app.name') }}">
        <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">

        {{-- Twitter --}}
        <meta property="twitter:card" content="summary_large_image">
        <meta property="twitter:url" content="{{ url()->current() }}">
        <meta property="twitter:title" content="@yield('title', config('app.name'))">
        <meta property="twitter:description" content="{{ $description ?? __('meta_description_home') }}">
        <meta property="twitter:image" content="{{ asset('images/twitter-image.jpg') }}">

        {{-- Additional SEO --}}
        <meta name="theme-color" content="#0ea5e9">
        <meta name="msapplication-TileColor" content="#0ea5e9">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}">

        {{-- Structured Data --}}
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Organization",
            "name": "{{ config('app.name') }}",
            "url": "{{ url('/') }}",
            "logo": "{{ asset('images/logo.png') }}",
            "description": "{{ __('meta_description_home') }}",
            "address": {
                "@type": "PostalAddress",
                "addressCountry": "LT"
            },
            "contactPoint": {
                "@type": "ContactPoint",
                "contactType": "customer service",
                "availableLanguage": ["lt", "en", "de", "ru"]
            },
            "sameAs": [
                "{{ $socialFacebook ?? '#' }}",
                "{{ $socialInstagram ?? '#' }}"
            ]
        }
        </script>
    @endhasSection

    {{-- Canonical URL --}}
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">

    {{-- Hreflang --}}
    @if (config('app.supported_locales'))
        @foreach (config('app.supported_locales') as $locale)
            <link rel="alternate" hreflang="{{ $locale }}"
                  href="{{ url()->current() }}?locale={{ $locale }}">
        @endforeach
    @endif

    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    {{-- Local Fonts - No CDN --}}
    <link rel="preload" href="{{ asset('fonts/inter-var.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('fonts/space-grotesk-var.woff2') }}" as="font" type="font/woff2"
          crossorigin>

    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Livewire Styles --}}
    @livewireStyles

    {{-- Additional Head Content --}}
    @stack('head')
</head>

<body class="h-full bg-gray-50 text-gray-900 font-sans antialiased">
    {{-- Skip to content link for accessibility --}}
    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-primary-600 text-white px-4 py-2 rounded-lg z-50 shadow-lg transition-all duration-200">
        {{ __('skip_to_results') }}
    </a>

    {{-- Enhanced Loading overlay with modern design --}}
    <div id="loading-overlay"
         class="fixed inset-0 bg-white/95 backdrop-blur-xl z-50 flex items-center justify-center opacity-0 pointer-events-none transition-all duration-500">
        <div class="flex flex-col items-center gap-6">
            <div class="relative">
                <div class="w-16 h-16 border-4 border-gray-200 border-t-blue-600 rounded-full animate-spin"></div>
                <div class="absolute inset-0 w-16 h-16 border-4 border-transparent border-r-purple-500 rounded-full animate-spin"
                     style="animation-direction: reverse; animation-duration: 1.5s;"></div>
            </div>
            <div class="text-center">
                <p class="text-gray-700 font-semibold text-lg">{{ __('Loading...') }}</p>
                <p class="text-gray-500 text-sm mt-1">{{ __('Please wait while we prepare everything for you') }}</p>
            </div>
        </div>
    </div>

    {{-- Header --}}
    <x-layouts.header />

    {{-- Main Content --}}
    <main id="main-content" class="flex-1 min-h-screen">
        @yield('content')
    </main>

    {{-- Footer --}}
    <x-layouts.footer />

    {{-- Notifications --}}
    <x-shared.notifications />

    {{-- Enhanced Back to top button with modern design --}}
    <button id="back-to-top"
            class="fixed bottom-8 right-8 bg-gradient-to-r from-blue-600 to-purple-600 text-white p-4 rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-110 transition-all duration-300 opacity-0 pointer-events-none z-40 group"
            aria-label="{{ __('Back to top') }}">
        <svg class="w-6 h-6 group-hover:animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18">
            </path>
        </svg>
        <div
             class="absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-500 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-10 blur-sm">
        </div>
    </button>

    {{-- Livewire Scripts --}}
    @livewireScripts

    {{-- Additional Scripts --}}
    @stack('scripts')

    {{-- Local Alpine.js - No CDN --}}
    <script defer src="{{ asset('js/alpine.min.js') }}"></script>

    {{-- Back to top functionality --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const backToTopButton = document.getElementById('back-to-top');

            if (backToTopButton) {
                // Show/hide button based on scroll position
                window.addEventListener('scroll', function() {
                    if (window.pageYOffset > 300) {
                        backToTopButton.classList.remove('opacity-0', 'pointer-events-none');
                        backToTopButton.classList.add('opacity-100');
                    } else {
                        backToTopButton.classList.add('opacity-0', 'pointer-events-none');
                        backToTopButton.classList.remove('opacity-100');
                    }
                });

                // Smooth scroll to top
                backToTopButton.addEventListener('click', function() {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            }
        });
    </script>
</body>

</html>
