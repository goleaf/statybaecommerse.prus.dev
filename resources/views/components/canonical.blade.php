@php
    $supported = config('app.supported_locales', ['en']);
    $locales = collect(is_array($supported) ? $supported : explode(',', (string) $supported))
        ->map(fn($v) => trim($v))
        ->filter()
        ->values();
    $full = url()->full();
    $path = parse_url($full, PHP_URL_PATH) ?? '/';
    $qs = parse_url($full, PHP_URL_QUERY);
    $query = '';

    if ($qs) {
        // Normalise the query string by removing tracking parameters (to avoid duplicate-content penalties)
        // and collapsing duplicate key/value pairs into a predictable alphabetical order.
        $ignoredPrefixes = ['utm_', 'pk_', 'mc_', 'ga_'];
        $ignoredKeys = ['fbclid', 'gclid', 'yclid', 'ref', 'source'];

        $pairs = collect(explode('&', $qs))
            ->map(static fn ($pair) => trim($pair))
            ->filter(static fn ($pair) => $pair !== '');

        $filteredPairs = $pairs
            ->map(static function ($pair) {
                [$key, $value] = array_pad(explode('=', $pair, 2), 2, '');
                $normalizedKey = strtolower($key);

                return [
                    'original' => $pair,
                    'key' => $key,
                    'value' => $value,
                    'normalized_key' => $normalizedKey,
                ];
            })
            ->filter(static function (array $pair) use ($ignoredPrefixes, $ignoredKeys) {
                $key = $pair['normalized_key'];

                if ($key === '') {
                    return false;
                }

                if (in_array($key, $ignoredKeys, true)) {
                    return false;
                }

                foreach ($ignoredPrefixes as $prefix) {
                    if (str_starts_with($key, $prefix)) {
                        return false;
                    }
                }

                return true;
            })
            ->unique(static fn (array $pair) => $pair['normalized_key'] . '=' . $pair['value'])
            ->sortBy(static fn (array $pair) => $pair['normalized_key'] . '=' . $pair['value'])
            ->map(static function (array $pair) {
                $value = $pair['value'];

                return $pair['key'] . ($value !== '' ? '=' . $value : '');
            })
            ->values();

        if ($filteredPairs->isNotEmpty()) {
            $query = '?' . $filteredPairs->implode('&');
        }
    }

    $parts = explode('/', ltrim($path, '/'));
    if (isset($parts[0]) && in_array($parts[0], $locales->all(), true)) {
        array_shift($parts);
    }
    $rest = trim(implode('/', $parts), '/');
    $canonical = $rest === '' ? url('/' . app()->getLocale()) : url('/' . app()->getLocale() . '/' . $rest);
@endphp
<link rel="canonical" href="{{ $canonical }}{{ $query }}" />
