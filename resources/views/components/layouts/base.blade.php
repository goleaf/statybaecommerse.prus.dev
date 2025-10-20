<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light">

    @php
        $seoData = $seo ?? [];
        $sectionTitle = trim($__env->yieldContent('title'));
        $resolvedTitle = $seoData['title']
            ?? $title
            ?? ($sectionTitle !== '' ? $sectionTitle : config('app.name', 'E-Commerce'));
        $sectionDescription = trim($__env->yieldContent('description'));
        $resolvedDescription = $seoData['description']
            ?? $description
            ?? ($sectionDescription !== '' ? $sectionDescription : null);
        $canonicalLink = $seoData['canonical_url'] ?? ($canonicalUrl ?? null);
        $alternateLinks = $seoData['alternate_locales'] ?? ($alternateLocales ?? null);
        $metaKeywords = $seoData['keywords'] ?? ($metaKeywords ?? null);
        $resolvedOgTitle = $seoData['og_title'] ?? ($ogTitle ?? $resolvedTitle);
        $resolvedOgDescription = $seoData['og_description'] ?? ($ogDescription ?? $resolvedDescription);
        $resolvedOgImage = $seoData['og_image'] ?? ($ogImage ?? null);
        $resolvedOgType = $seoData['og_type'] ?? ($ogType ?? 'website');
        $resolvedTwitterCard = $seoData['twitter_card'] ?? ($twitterCard ?? 'summary_large_image');
        $structuredPayload = $seoData['structured_data'] ?? ($structuredData ?? []);
    @endphp

    <title>{{ $resolvedTitle }}</title>

    <x-meta
        :title="$resolvedTitle"
        :description="$resolvedDescription"
        :og-title="$resolvedOgTitle"
        :og-description="$resolvedOgDescription"
        :og-image="$resolvedOgImage"
        :og-type="$resolvedOgType"
        :og-url="$canonicalLink"
        :twitter-card="$resolvedTwitterCard"
        :twitter-title="$resolvedOgTitle"
        :twitter-description="$resolvedOgDescription"
        :twitter-url="$canonicalLink"
        :canonical="$canonicalLink"
        :keywords="$metaKeywords"
        :alternate-locales="is_array($alternateLinks) ? $alternateLinks : null"
        :jsonld="$structuredPayload"
    />

    @yield('meta')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Scripts -->
    @vite(['resources/css/app.scss', 'resources/js/app.js'])

    <!-- Livewire Styles -->
    @livewireStyles

    @stack('styles')

    <!-- Additional head content -->
    {{ $head ?? '' }}
    @stack('head')
</head>

<body class="font-sans antialiased h-full bg-gray-50">
    <div class="min-h-full">
        <!-- Header -->
        <x-layouts.header />

        <!-- Page Content -->
        <main>
            {{ $slot ?? '' }}
            @yield('content')
        </main>

        <!-- Footer -->
        <x-layouts.footer />
    </div>

    <!-- Notification Container -->
    <div id="notifications" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <!-- Livewire Scripts -->
    @livewireScripts

    <!-- Additional scripts -->
    {{ $scripts ?? '' }}
    @stack('scripts')
</body>

</html>
