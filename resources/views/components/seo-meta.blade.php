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
<meta name="robots" content="{{ $noindex ? 'noindex, nofollow' : 'index, follow' }}">

{{-- Open Graph / Facebook --}}
<meta property="og:type" content="{{ $type }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:image" content="{{ $image }}">

{{-- Twitter --}}
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{{ $canonical }}">
<meta property="twitter:title" content="{{ $title }}">
<meta property="twitter:description" content="{{ $description }}">
<meta property="twitter:image" content="{{ $image }}">

{{-- Canonical URL --}}
<link rel="canonical" href="{{ $canonical }}">
