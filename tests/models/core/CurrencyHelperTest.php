<?php

declare(strict_types=1);

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

// format_price() function tests
it('formats price with default currency and locale', function () {
    App::setLocale('lt');
    $formatted = format_price(123.45);

    expect($formatted)
        ->toBeString()
        ->toContain('€')
        ->toContain('123');
});

it('formats price with custom currency and locale', function () {
    $formatted = format_price(123.45, 'USD', 'en');

    expect($formatted)
        ->toBeString()
        ->toContain('$')
        ->toContain('123.45');
});

it('handles null values gracefully', function () {
    $formatted = format_price(null);

    expect($formatted)->toBe('');
});

it('handles empty string values gracefully', function () {
    $formatted = format_price('');

    expect($formatted)->toBe('');
});

it('handles zero values correctly', function () {
    App::setLocale('lt');
    $formatted = format_price(0);

    expect($formatted)
        ->toBeString()
        ->toContain('€')
        ->toContain('0');
});

it('handles string numeric values', function () {
    App::setLocale('lt');
    $formatted = format_price('123.45');

    expect($formatted)
        ->toBeString()
        ->toContain('€')
        ->toContain('123');
});

it('handles integer values', function () {
    App::setLocale('lt');
    $formatted = format_price(123);

    expect($formatted)
        ->toBeString()
        ->toContain('€')
        ->toContain('123');
});

it('uses current locale when none provided', function () {
    App::setLocale('en');
    $formatted = format_price(123.45);

    expect($formatted)
        ->toBeString()
        ->toContain('€');

    // Should use English formatting (dot as decimal separator)
    expect(str_contains($formatted, '.'))->toBeTrue();
});

it('uses current currency when none provided', function () {
    App::setLocale('lt');
    $formatted = format_price(123.45);

    expect($formatted)
        ->toBeString()
        ->toContain('€');
});

it('formats large amounts correctly', function () {
    App::setLocale('lt');
    $formatted = format_price(1234567.89);

    expect($formatted)
        ->toBeString()
        ->toContain('€')
        ->toContain('234') // Check for part of the number
        ->toContain('567') // Check for another part of the number
        ->toContain(',89'); // Check for decimal part
});

it('formats small decimal amounts correctly', function () {
    App::setLocale('lt');
    $formatted = format_price(0.01);

    expect($formatted)
        ->toBeString()
        ->toContain('€')
        ->toContain('0,01');
});
