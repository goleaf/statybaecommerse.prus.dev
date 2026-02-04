<?php

declare(strict_types=1);

it('provides companies and locale translations in the messages group', function (string $locale): void {
    $path = base_path(sprintf('lang/%s/messages.php', $locale));

    expect($path)->toBeFile();

    $messages = include $path;
    $keys = ['companies', 'locale'];

    expect($messages)->toBeArray();

    foreach ($keys as $key) {
        expect($messages)->toHaveKey($key);
        expect($messages[$key])->not->toBe('');
    }

    app()->setLocale($locale);

    foreach ($keys as $key) {
        expect(__('messages.' . $key))->not->toBe('messages.' . $key);
    }
})->with(['en', 'lt', 'de', 'ru']);
