@props([
    'title' => null,
    'description' => null,
    'keywords' => null,
    'image' => null,
    'type' => 'website',
    'canonical' => null,
    'noindex' => false,
    'structuredData' => null,
])

@php
    $title = $title ?? config('app.name');
    $description = $description ?? __('meta_description_home');
    $keywords = $keywords ?? __('meta_keywords');
    $image = $image ?? asset('images/og-image.jpg');
    $canonical = $canonical ?? url()->current();
    $currentLocale = app()->getLocale();
@endphp

{{-- Page Title --}}
<title>{{ $title }}</title>

{{-- Basic Meta Tags --}}
<meta name="description" content="{{ $description }}">
<meta name="keywords" content="{{ $keywords }}">
<meta name="author" content="{{ config('app.name') }}">
<meta name="robots"
      content="{{ $noindex ? 'noindex, nofollow' : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' }}">
<meta name="googlebot" content="{{ $noindex ? 'noindex, nofollow' : 'index, follow' }}">
<meta name="bingbot" content="{{ $noindex ? 'noindex, nofollow' : 'index, follow' }}">

{{-- Open Graph / Facebook --}}
<meta property="og:type" content="{{ $type }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:image" content="{{ $image }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:site_name" content="{{ config('app.name') }}">
<meta property="og:locale" content="{{ str_replace('_', '-', $currentLocale) }}">

{{-- Twitter --}}
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{{ $canonical }}">
<meta property="twitter:title" content="{{ $title }}">
<meta property="twitter:description" content="{{ $description }}">
<meta property="twitter:image" content="{{ $image }}">

{{-- Additional SEO --}}
<meta name="theme-color" content="#0ea5e9">
<meta name="msapplication-TileColor" content="#0ea5e9">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}">

{{-- Canonical URL --}}
<link rel="canonical" href="{{ $canonical }}">

{{-- Hreflang for multi-language support --}}
@if (config('app.supported_locales'))
    @foreach (config('app.supported_locales') as $locale)
        @if ($locale !== $currentLocale)
            <link rel="alternate" hreflang="{{ $locale }}"
                  href="{{ route('localized.home', ['locale' => $locale]) ?? url('/?locale=' . $locale) }}">
        @endif
    @endforeach
    <link rel="alternate" hreflang="x-default"
          href="{{ route('localized.home', ['locale' => 'en']) ?? url('/?locale=en') }}">
@endif

{{-- Structured Data --}}
@if ($structuredData)
    <script type="application/ld+json">
        {!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@else
    {{-- Default Organization Schema --}}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "{{ config('app.name') }}",
        "url": "{{ url('/') }}",
        "logo": "{{ asset('images/logo.png') }}",
        "description": "{{ $description }}",
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
            "{{ app_setting('social_facebook') ?? '#' }}",
            "{{ app_setting('social_instagram') ?? '#' }}"
        ]
    }
    </script>
@endif
@endif
