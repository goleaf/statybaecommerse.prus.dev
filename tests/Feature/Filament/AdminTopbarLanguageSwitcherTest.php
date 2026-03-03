<?php

declare(strict_types=1);

it('hides russian locale from the admin topbar language switcher', function (): void {
    config()->set('app.supported_locales', ['en', 'ru']);
    config()->set('app.locales', [
        'en' => ['native' => 'English', 'name' => 'English'],
        'ru' => ['native' => 'Русский', 'name' => 'Russian'],
    ]);

    app()->setLocale('ru');

    $html = view('filament.hooks.topbar-language-switcher')->render();

    expect($html)
        ->not->toContain('(RU)')
        ->and($html)->not->toContain('Русский')
        ->and($html)->toContain('(EN)');
});
