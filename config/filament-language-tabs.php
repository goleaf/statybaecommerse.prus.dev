<?php

declare(strict_types=1);

$defaultLocales = config('shared.localization.supported_locales', []);

if ($defaultLocales === [] || ! is_array($defaultLocales)) {
    $rawSupported = config('app.supported_locales', 'lt,en');
    $supported = is_string($rawSupported) ? $rawSupported : 'lt,en';

    $defaultLocales = array_filter(array_map(
        static fn (string $locale): string => trim($locale),
        explode(',', $supported),
    ));
}

$defaultLocales = array_values(array_unique($defaultLocales));

$requiredLocales = array_filter([
    config('app.locale'),
    config('app.fallback_locale'),
]);

if ($requiredLocales === []) {
    $requiredLocales = [
        $defaultLocales[0] ?? 'lt',
    ];
}

return [
    'default_locales'  => $defaultLocales,
    'required_locales' => array_values(array_unique($requiredLocales)),
];
