@props([
    'title' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    @includeIf('components.favicons')
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <style id="critical-css">
        .hero-title {
            font-size: clamp(2rem, 4vw, 3.75rem);
            line-height: 1.1;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #0b0f19;
        }

        .hero-cta {
            display: inline-block;
            padding: 0.75rem 1.25rem;
            border-radius: 0.5rem;
            background: #111827;
            color: #fff;
            text-decoration: none;
        }

        .hero-cta:hover {
            filter: brightness(1.05);
        }
    </style>

    <title>{{ $title ?? 'Starter Kit' }} // {{ config('app.name') }}</title>
    @includeIf('components.canonical')
    <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}" />
    @php
        $supported = config('app.supported_locales', ['en']);
        $locales = collect(is_array($supported) ? $supported : explode(',', (string) $supported))
            ->map(fn($v) => trim($v))
            ->filter()
            ->values();
    @endphp
    @foreach ($locales as $loc)
        @if ($loc !== app()->getLocale())
            <meta property="og:locale:alternate" content="{{ str_replace('_', '-', $loc) }}" />
        @endif
    @endforeach
    <meta property="og:title" content="{{ $title ?? config('app.name') }}" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta name="twitter:card" content="summary_large_image" />
    @if (View::hasSection('meta'))
        @yield('meta')
    @endif
    @includeIf('components.social-meta')
    @stack('jsonld')
    <!-- Fonts are bundled locally via @fontsource in app.css -->

    @if (request()->is('admin*'))
        @filamentStyles
    @endif

    @livewireStyles
    <x-hreflang />
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "WebSite",
      "url": "{{ config('app.url') ?? url('/') }}",
      "potentialAction": {
        "@@type": "SearchAction",
        "target": "{{ route('search.index', ['locale' => app()->getLocale()]) }}?q={search_term_string}",
        "query-input": "required name=search_term_string"
      }
    }
    </script>
    @vite('resources/css/app.css')
</head>

<body
      class="antialiased bg-gray-50 text-gray-900 dark:bg-gray-950 dark:text-gray-100 selection:bg-primary-600 selection:text-white">
    <x-impersonation-banner />
    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 focus:bg-white focus:text-black focus:px-3 focus:py-2 rounded">{{ __('Skip to content') }}</a>
    <main id="main-content" role="main" tabindex="-1">
        {{ $slot }}
    </main>

    @if (request()->is('admin*'))
        @includeIf('components.adminbar')
    @endif

    @if (!app()->environment('testing') && request()->is('admin*'))
        @livewire(\Filament\Notifications\Livewire\Notifications::class)
    @endif

    @if (request()->is('admin*'))
        @filamentScripts
    @endif

    @livewireScripts
    @vite('resources/js/app.js')
    @stack('scripts')
    @php
        $org = config('app.name');
    @endphp
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "Organization",
      "name": "{{ addslashes($org) }}",
      "url": "{{ config('app.url') ?? url('/') }}"
    }
    </script>
    @php
        $searchUrl = route('search.index', ['locale' => app()->getLocale()]);
    @endphp
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "WebSite",
      "name": "{{ addslashes(config('app.name')) }}",
      "url": "{{ config('app.url') ?? url('/') }}",
      "potentialAction": {
        "@@type": "SearchAction",
        "target": "{{ $searchUrl }}?q={search_term_string}",
        "query-input": "required name=search_term_string"
      }
    }
    </script>
</body>

</html>
