<?php declare(strict_types=1);

use Illuminate\Support\Facades\App;

it('formats money in Lithuanian locale with comma and euro symbol', function () {
    App::setLocale('lt');
    $formatted = format_money(1234.56, 'EUR', 'lt');

    expect($formatted)
        ->toBeString()
        ->and($formatted)->toContain('€');

    // Lithuanian locale commonly uses comma as decimal separator
    expect(str_contains($formatted, ','))->toBeTrue();
});

it('formats money in English locale with dot and euro symbol', function () {
    App::setLocale('en');
    $formatted = format_money(1234.56, 'EUR', 'en');

    expect($formatted)
        ->toBeString()
        ->and($formatted)->toContain('€');

    // English (generic) commonly uses dot as decimal separator
    expect(str_contains($formatted, '.'))->toBeTrue();
});

it('app_money_format uses current currency when none provided', function () {
    // Default current_currency() is EUR (per helpers/config)
    $formatted = app_money_format(10);
    expect($formatted)->toBeString()->toContain('€');
});

