<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Title --}}
    <title>{{ $title ?? config('app.name') }}</title>

    {{-- Meta Tags --}}
    @hasSection('meta')
        @yield('meta')
    @else
        <meta name="description" content="{{ $description ?? __('meta_description_home') }}">
        <meta name="keywords" content="{{ $keywords ?? __('meta_keywords') }}">
    @endhasSection

    {{-- Canonical URL --}}
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">

    {{-- Hreflang --}}
    @if (config('app.supported_locales'))
        @foreach (config('app.supported_locales') as $locale)
            <link rel="alternate" hreflang="{{ $locale }}"
                  href="{{ switch_locale_url($locale) }}">
        @endforeach
    @endif

    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Livewire Styles --}}
    @livewireStyles

    {{-- Additional Head Content --}}
    @stack('head')
</head>

<body class="h-full bg-gray-50 dark:bg-gray-900 font-sans antialiased">
    {{-- Skip to content link for accessibility --}}
    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-blue-600 text-white px-4 py-2 rounded-md z-50">
        {{ __('skip_to_results') }}
    </a>

    {{-- Header --}}
    <x-layouts.header />

    {{-- Main Content --}}
    <main id="main-content" class="flex-1">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <x-layouts.footer />

    {{-- Notifications --}}
    <x-shared.notifications />

    {{-- Livewire Scripts --}}
    @livewireScripts

    {{-- Additional Scripts --}}
    @stack('scripts')

    {{-- Alpine.js --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>

</html>
