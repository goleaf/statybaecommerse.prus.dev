@php
    $provided = $alternateLocales ?? (View::shared('alternateLocales') ?? null);
@endphp

@if (is_array($provided) && !empty($provided))
    @foreach ($provided as $loc => $href)
        <link rel="alternate" hreflang="{{ $loc }}" href="{{ $href }}" />
    @endforeach
    @if (!array_key_exists('x-default', $provided))
        @php
            $first = reset($provided);
        @endphp
        @if ($first)
            <link rel="alternate" hreflang="x-default" href="{{ $first }}" />
        @endif
    @endif
@else
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
        $rest = implode('/', $parts);
        $rest = trim($rest, '/');
    @endphp
    @foreach ($locales as $loc)
        @php
            $href = $rest === '' ? url("/$loc") : url("/$loc/$rest");
        @endphp
        <link rel="alternate" hreflang="{{ $loc }}" href="{{ $href }}{{ $query }}" />
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ url('/' . $rest) }}{{ $query }}" />
@endif
