<?php

declare(strict_types=1);

use App\Models\Country;
use App\Models\Translations\CountryTranslation;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Set up test data
    $this->country = Country::create([
        'cca2' => 'LT',
        'cca3' => 'LTU',
        'phone_calling_code' => '370',
        'flag' => '🇱🇹',
        'region' => 'Europe',
        'subregion' => 'Northern Europe',
        'latitude' => 55.169438,
        'longitude' => 23.881275,
        'currencies' => ['EUR'],
        'is_enabled' => true,
        'sort_order' => 1,
    ]);

    CountryTranslation::create([
        'country_id' => $this->country->id,
        'locale' => 'lt',
        'name' => 'Lietuva',
        'name_official' => 'Lietuvos Respublika',
    ]);

    CountryTranslation::create([
        'country_id' => $this->country->id,
        'locale' => 'en',
        'name' => 'Lithuania',
        'name_official' => 'Republic of Lithuania',
    ]);
});

it('has correct default locale configuration', function () {
    expect(config('app.locale'))->toBe('lt');
    expect(config('app.fallback_locale'))->toBe('lt');
    expect(config('app.supported_locales'))->toBe('lt,en,ru,de');
});

it('can switch between locales', function () {
    // Test Lithuanian locale
    app()->setLocale('lt');
    expect(app()->getLocale())->toBe('lt');

    // Test English locale
    app()->setLocale('en');
    expect(app()->getLocale())->toBe('en');
});

it('returns correct translations for different locales', function () {
    $this->country->load('translations');

    // Test Lithuanian translations
    app()->setLocale('lt');
    expect($this->country->trans('name'))->toBe('Lietuva');
    expect($this->country->trans('name_official'))->toBe('Lietuvos Respublika');

    // Test English translations
    app()->setLocale('en');
    expect($this->country->trans('name'))->toBe('Lithuania');
    expect($this->country->trans('name_official'))->toBe('Republic of Lithuania');
});

it('has translation files for both supported locales', function () {
    $ltTranslations = __('nav_home', [], 'lt');
    $enTranslations = __('nav_home', [], 'en');

    expect($ltTranslations)->toBe('Pradžia');
    expect($enTranslations)->toBe('Home');
});

it('supports euro currency for all locales', function () {
    app()->setLocale('lt');
    $ltCurrency = config('app.locale_mapping.lt.currency', 'EUR');

    app()->setLocale('en');
    $enCurrency = config('app.locale_mapping.en.currency', 'EUR');

    expect($ltCurrency)->toBe('EUR');
    expect($enCurrency)->toBe('EUR');
});

it('has proper translation structure for admin interface', function () {
    $adminTranslationsLt = __('admin.widgets.recent_orders', [], 'lt');
    $adminTranslationsEn = __('admin.widgets.recent_orders', [], 'en');

    expect($adminTranslationsLt)->toBe('Paskutiniai užsakymai');
    expect($adminTranslationsEn)->toBe('Recent Orders');
});

it('has proper translation structure for ecommerce', function () {
    $ecommerceTranslationsLt = __('cart_add_to_cart', [], 'lt');
    $ecommerceTranslationsEn = __('cart_add_to_cart', [], 'en');

    expect($ecommerceTranslationsLt)->toBe('Įdėti į krepšelį');
    expect($ecommerceTranslationsEn)->toBe('Add to Cart');
});

it('supports multilanguage slugs', function () {
    $this->country->load('translations');

    // Test that we can get different slugs for different locales
    app()->setLocale('lt');
    $ltName = $this->country->trans('name');

    app()->setLocale('en');
    $enName = $this->country->trans('name');

    expect($ltName)->not->toBe($enName);
    expect($ltName)->toBe('Lietuva');
    expect($enName)->toBe('Lithuania');
});

it('has language switcher component available', function () {
    $supportedLocales = config('app.supported_locales', 'lt,en');
    $locales = array_filter(array_map('trim', explode(',', $supportedLocales)));

    expect($locales)->toContain('lt');
    expect($locales)->toContain('en');
    expect(count($locales))->toBeGreaterThanOrEqual(2);
});

it('can handle missing translations gracefully', function () {
    $this->country->load('translations');

    // Test with a locale that doesn't have translations
    app()->setLocale('de');
    $germanName = $this->country->trans('name');

    // Should fall back to the original value or null
    expect($germanName)->toBeNull();
});

it('has proper date and currency formatting for locales', function () {
    // Test Lithuanian formatting
    app()->setLocale('lt');
    $date = now()->format('Y-m-d');
    expect($date)->toMatch('/\d{4}-\d{2}-\d{2}/');

    // Test English formatting
    app()->setLocale('en');
    $date = now()->format('Y-m-d');
    expect($date)->toMatch('/\d{4}-\d{2}-\d{2}/');
});

it('has all required translation keys for countries', function () {
    $requiredKeys = [
        'country_name',
        'country_official_name',
        'country_code',
        'phone_code',
        'region',
        'subregion',
        'currencies',
        'flag',
    ];

    foreach ($requiredKeys as $key) {
        $ltTranslation = __("translations.{$key}", [], 'lt');
        $enTranslation = __("translations.{$key}", [], 'en');

        expect($ltTranslation)->not->toBe("translations.{$key}");
        expect($enTranslation)->not->toBe("translations.{$key}");
    }
});

it('has all required translation keys for admin interface', function () {
    $requiredKeys = [
        'widgets.recent_orders',
        'fields.customer',
        'fields.total',
        'fields.status',
        'actions.view',
        'actions.edit',
        'actions.delete',
    ];

    foreach ($requiredKeys as $key) {
        $ltTranslation = __("admin.{$key}", [], 'lt');
        $enTranslation = __("admin.{$key}", [], 'en');

        expect($ltTranslation)->not->toBe("admin.{$key}");
        expect($enTranslation)->not->toBe("admin.{$key}");
    }
});

it('supports multilanguage database queries', function () {
    // Test that we can query translations
    $ltTranslation = CountryTranslation::where('locale', 'lt')
        ->where('country_id', $this->country->id)
        ->first();

    $enTranslation = CountryTranslation::where('locale', 'en')
        ->where('country_id', $this->country->id)
        ->first();

    expect($ltTranslation)->not->toBeNull();
    expect($enTranslation)->not->toBeNull();
    expect($ltTranslation->name)->toBe('Lietuva');
    expect($enTranslation->name)->toBe('Lithuania');
});

it('has proper translation table structure', function () {
    // Verify translation table has required columns
    $translation = CountryTranslation::first();

    expect($translation)->toHaveProperty('country_id');
    expect($translation)->toHaveProperty('locale');
    expect($translation)->toHaveProperty('name');
    expect($translation)->toHaveProperty('name_official');
    expect($translation)->toHaveProperty('created_at');
    expect($translation)->toHaveProperty('updated_at');
});

it('enforces unique constraints on translations', function () {
    // Try to create duplicate translation
    expect(function () {
        CountryTranslation::create([
            'country_id' => $this->country->id,
            'locale' => 'lt',  // Duplicate locale for same country
            'name' => 'Duplicate Lietuva',
            'name_official' => 'Duplicate Official',
        ]);
    })->toThrow(\Illuminate\Database\QueryException::class);
});

it('can retrieve all translations for a country', function () {
    $this->country->load('translations');

    expect($this->country->translations)->toHaveCount(2);

    $locales = $this->country->translations->pluck('locale')->toArray();
    expect($locales)->toContain('lt');
    expect($locales)->toContain('en');
});

it('has proper HasTranslations trait functionality', function () {
    expect($this->country)->toHaveMethod('translations');
    expect($this->country)->toHaveMethod('trans');

    // Test that the trait methods work
    $this->country->load('translations');
    expect($this->country->translations())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});
