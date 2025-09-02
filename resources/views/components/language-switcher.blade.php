@php
    $supported = config('app.supported_locales', ['en']);
    $locales = collect(is_array($supported) ? $supported : explode(',', (string) $supported))
        ->map(fn($v) => trim($v))
        ->filter()
        ->values();
    $current = app()->getLocale();
    $path = request()->path();
    $parts = explode('/', $path);
    if (isset($parts[0]) && in_array($parts[0], $locales->all(), true)) {
        array_shift($parts);
    }
    $rest = implode('/', $parts);
    $rest = trim($rest, '/');
    $query = request()->getQueryString();
    $qs = $query ? '?' . $query : '';
@endphp

<nav aria-label="Language switcher">
    <ul class="inline-flex gap-2 items-center">
        @foreach ($locales as $loc)
            @php($active = $loc === $current)
            @php($href = $rest === '' ? "/{$loc}" : "/{$loc}/{$rest}")
            <li>
                <a href="{{ $href }}{{ $qs }}"
                   @class([
                       'px-2 py-1 rounded text-sm',
                       'bg-primary-600 text-white' => $active,
                       'hover:underline text-gray-700 dark:text-gray-300' => !$active,
                   ])
                   aria-current="{{ $active ? 'true' : 'false' }}">
                    {{ strtoupper($loc) }}
                </a>
            </li>
        @endforeach
    </ul>
</nav>
