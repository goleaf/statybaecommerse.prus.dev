@php
    $supported = config('app.supported_locales', ['en']);
    $locales = collect(is_array($supported) ? $supported : explode(',', (string) $supported))
        ->map(fn($v) => trim($v))
        ->filter()
        ->values();
    $full = url()->full();
    $path = parse_url($full, PHP_URL_PATH) ?? '/';
    $qs = parse_url($full, PHP_URL_QUERY);
    $query = $qs ? '?' . $qs : '';

    $parts = explode('/', ltrim($path, '/'));
    if (isset($parts[0]) && in_array($parts[0], $locales->all(), true)) {
        array_shift($parts);
    }
    $rest = trim(implode('/', $parts), '/');
    $canonical = $rest === '' ? url('/' . app()->getLocale()) : url('/' . app()->getLocale() . '/' . $rest);
@endphp
<link rel="canonical" href="{{ $canonical }}{{ $query }}" />
