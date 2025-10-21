<?php

declare(strict_types=1);

return [
    'default_locales'  => array_values(array_filter(array_map('trim', explode(',', (string) env('FILAMENT_LANGUAGE_TABS_LOCALES', 'lt,en,ru'))))),
    'required_locales' => array_values(array_filter(array_map('trim', explode(',', (string) env('FILAMENT_LANGUAGE_TABS_REQUIRED_LOCALES', 'lt,en'))))),
];
