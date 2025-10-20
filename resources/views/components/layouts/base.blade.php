<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light">

    @php
        $sectionTitle = trim($__env->yieldContent('title'));
        $resolvedTitle = $title ?? ($sectionTitle !== '' ? $sectionTitle : config('app.name', 'E-Commerce'));
        $sectionDescription = trim($__env->yieldContent('description'));
        $resolvedDescription = $description ?? ($sectionDescription !== '' ? $sectionDescription : null);
    @endphp

    <title>{{ $resolvedTitle }}</title>

    @if($resolvedDescription)
        <meta name="description" content="{{ $resolvedDescription }}">
    @endif

    @yield('meta')

    @includeWhen(view()->exists('components.hreflang'), 'components.hreflang')
    @includeWhen(view()->exists('components.canonical'), 'components.canonical')

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
