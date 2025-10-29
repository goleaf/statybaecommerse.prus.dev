<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light dark">

    @php
        /**
         * Determine the meta information for the minimal layout using the
         * explicitly provided data or fallback to the yielded sections.
         */
        $sectionTitle = trim($__env->yieldContent('title'));
        $resolvedTitle = $title ?? ($sectionTitle !== '' ? $sectionTitle : config('app.name'));

        // Resolve the meta description with graceful fallback handling.
        $sectionDescription = trim($__env->yieldContent('description'));
        $resolvedDescription = $description ?? ($sectionDescription !== '' ? $sectionDescription : null);

        // Resolve the meta keywords to support SEO customization per page.
        $sectionKeywords = trim($__env->yieldContent('keywords'));
        $resolvedKeywords = $keywords ?? ($sectionKeywords !== '' ? $sectionKeywords : null);

        $canonicalLink = $canonicalUrl ?? null;
    @endphp

    <title>{{ $resolvedTitle }}</title>

    @isset($resolvedDescription)
        <meta name="description" content="{{ $resolvedDescription }}">
    @endisset

    @isset($resolvedKeywords)
        <meta name="keywords" content="{{ $resolvedKeywords }}">
    @endisset

    @if ($canonicalLink)
        <link rel="canonical" href="{{ $canonicalLink }}">
    @endif

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @php
        $shouldLoadViteAssets = false;

        if (!app()->runningUnitTests()) {
            $manifestPath = public_path('build/manifest.json');

            if (is_file($manifestPath) && is_readable($manifestPath)) {
                $manifestContents = file_get_contents($manifestPath);

                if ($manifestContents !== false) {
                    $manifestData = json_decode($manifestContents, true);
                    $shouldLoadViteAssets = is_array($manifestData) && $manifestData !== [];
                }
            }
        }
    @endphp

    @if ($shouldLoadViteAssets)
        @vite(['resources/css/app.scss', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <script type="module" src="{{ asset('js/app.js') }}" defer></script>
    @endif

    @stack('styles')

    {{ $head ?? '' }}
    @stack('head')
</head>

<body class="font-sans antialiased h-full bg-gray-50 dark:bg-gray-900">
    <div class="min-h-screen">
        {{ $slot ?? '' }}
        @yield('content')
    </div>

    @stack('scripts')
    {{ $scripts ?? '' }}
</body>

</html>
