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
        @php
            $route = request()->route();
            $alternateUrls = [];

            if ($route && ($name = $route->getName()) && str_starts_with($name, 'localized.')) {
                foreach ((array) config('app.supported_locales') as $locale) {
                    $parameters = $route->parameters();
                    $parameters['locale'] = $locale;
                    $alternateUrls[$locale] = route($name, $parameters, true);
                }
            } else {
                foreach ((array) config('app.supported_locales') as $locale) {
                    $alternateUrls[$locale] = url('/' . ltrim($locale, '/'));
                }
            }
        @endphp
        @foreach ($alternateUrls as $locale => $alternateUrl)
            <link rel="alternate" hreflang="{{ $locale }}" href="{{ $alternateUrl }}">
        @endforeach
    @endif

    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    {{-- Vite Assets --}}
    @php
        $manifestPath = public_path('build/manifest.json');
        $manifest = file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : null;
        $cssFile = $manifest['resources/css/app.scss']['file'] ?? null;
        $jsFile = $manifest['resources/js/app.js']['file'] ?? null;
        $useViteDev = app()->environment('local') && \App\Support\ViteManifest::isPopulated();
    @endphp

    @if ($useViteDev)
        @vite(['resources/css/app.scss', 'resources/js/app.js'])
    @elseif ($cssFile && $jsFile)
        {{-- Use compiled assets in production --}}
        <link rel="stylesheet" href="{{ asset('build/' . $cssFile) }}">
        <script type="module" src="{{ asset('build/' . $jsFile) }}"></script>
    @else
        {{-- Provide a graceful no-op when the Vite manifest is missing during backend tests. --}}
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <script type="module" src="{{ asset('js/app.js') }}"></script>
    @endif

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
    @livewireScripts()

    {{-- Livewire client hardening: avoid accidental $wire.toJSON() server calls --}}
    <script>
        document.addEventListener('livewire:init', () => {
            const patchComponent = (component) => {
                try {
                    const w = component?.$wire;
                    if (!w || typeof w !== 'object') return;

                    // Define a non-enumerable toJSON to stop JSON.stringify($wire)
                    if (!Object.prototype.hasOwnProperty.call(w, 'toJSON')) {
                        Object.defineProperty(w, 'toJSON', {
                            value: () => ({ id: component.id, name: component.name || 'livewire-component' }),
                            enumerable: false,
                            configurable: true,
                        });
                    }
                } catch (_) {
                    // no-op: defensive guard only
                }
            };

            // Patch existing components on load
            try {
                document.querySelectorAll('[wire\\:id]')
                    .forEach((el) => {
                        const id = el.getAttribute('wire:id');
                        const cmp = window.Livewire?.find?.(id);
                        if (cmp) patchComponent(cmp);
                    });
            } catch (_) {}

            // Patch after each update
            window.Livewire?.hook?.('message.processed', (message, component) => patchComponent(component));
        });
    </script>

    {{-- Additional Scripts --}}
    @stack('scripts')

    {{-- Alpine.js - Load after app.js to ensure Alpine components are registered --}}
    <script>
        // Ensure Alpine loads only once and after app.js components are registered
        (function() {
            if (typeof window.Alpine !== 'undefined') {
                return; // Alpine already loaded
            }
            
            function checkComponentsReady() {
                return typeof window.createDesktopSearchComponent !== 'undefined' &&
                       typeof window.createMobileSearchComponent !== 'undefined' &&
                       typeof window.createCartButtonComponent !== 'undefined';
            }
            
            function loadAlpine() {
                if (typeof window.Alpine !== 'undefined') {
                    return; // Already loaded
                }
                
                const script = document.createElement('script');
                script.src = 'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js';
                script.defer = true;
                document.head.appendChild(script);
            }
            
            function initAlpine() {
                if (typeof window.Alpine !== 'undefined') {
                    return; // Already initialized
                }
                
                // Check if components are ready
                if (checkComponentsReady()) {
                    loadAlpine();
                    return;
                }
                
                // Wait for app.js module to load and register components
                let attempts = 0;
                const maxAttempts = 200; // 10 seconds max wait
                
                const checkInterval = setInterval(() => {
                    attempts++;
                    if (checkComponentsReady()) {
                        clearInterval(checkInterval);
                        loadAlpine();
                    } else if (attempts >= maxAttempts) {
                        clearInterval(checkInterval);
                        // Load Alpine anyway - components might be registered later
                        console.warn('Alpine components not ready after timeout, loading Alpine anyway');
                        loadAlpine();
                    }
                }, 50);
            }
            
            // Wait for all scripts to load
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    // Wait a bit for module scripts to execute
                    window.addEventListener('load', initAlpine);
                    // Also try after a short delay
                    setTimeout(initAlpine, 200);
                });
            } else {
                window.addEventListener('load', initAlpine);
                setTimeout(initAlpine, 200);
            }
        })();
    </script>
    
    {{-- Filament Alpine functions stub for compatibility --}}
    <script>
        (function() {
            if (typeof window.filamentSchema === 'undefined') {
                window.filamentSchema = function() {
                    return {};
                };
            }
            if (typeof window.filamentSchemaComponent === 'undefined') {
                window.filamentSchemaComponent = function() {
                    return {};
                };
            }
        })();
    </script>
</body>
</html>
