<?php declare(strict_types=1);

use App\Models\Currency;
use App\Models\Price;
use App\Models\Zone;

describe('Currency Model', function () {
    it('can create a currency', function () {
        $currency = Currency::factory()->create([
            'name' => ['en' => 'US Dollar', 'lt' => 'JAV doleris'],
            'code' => 'USD',
            'symbol' => '$',
            'exchange_rate' => 1.0,
            'is_default' => true,
            'is_enabled' => true,
            'decimal_places' => 2,
        ]);

        expect($currency)
            ->toBeInstanceOf(Currency::class)
            ->code
            ->toBe('USD')
            ->symbol
            ->toBe('$')
            ->exchange_rate
            ->toEqual(1.0)
            ->is_default
            ->toBeTrue()
            ->is_enabled
            ->toBeTrue()
            ->decimal_places
            ->toBe(2);
    });

    it('has translatable name attribute', function () {
        $currency = Currency::factory()->create([
            'name' => ['en' => 'US Dollar', 'lt' => 'JAV doleris'],
        ]);

        expect($currency->getTranslation('name', 'en'))->toBe('US Dollar');
        expect($currency->getTranslation('name', 'lt'))->toBe('JAV doleris');
    });

    it('can have zones relationship', function () {
        $currency = Currency::factory()->create();
        $zone = Zone::factory()->create(['currency_id' => $currency->id]);

        expect($currency->zones)->toHaveCount(1);
        expect($currency->zones->first())->toBeInstanceOf(Zone::class);
    });

    it('can have prices relationship', function () {
        $currency = Currency::factory()->create();
        $price = Price::factory()->create(['currency_id' => $currency->id]);

        expect($currency->prices)->toHaveCount(1);
        expect($currency->prices->first())->toBeInstanceOf(Price::class);
    });

    it('can scope enabled currencies', function () {
        Currency::factory()->create(['is_enabled' => true]);
        Currency::factory()->create(['is_enabled' => false]);

        $enabledCurrencies = Currency::enabled()->get();

        expect($enabledCurrencies)->toHaveCount(1);
        expect($enabledCurrencies->first()->is_enabled)->toBeTrue();
    });

    it('can scope default currency', function () {
        Currency::factory()->create(['is_default' => false]);
        $defaultCurrency = Currency::factory()->create(['is_default' => true]);

        $result = Currency::default()->first();

        expect($result->id)->toBe($defaultCurrency->id);
        expect($result->is_default)->toBeTrue();
    });

    it('has formatted symbol attribute', function () {
        $currency = Currency::factory()->create(['symbol' => '$']);
        expect($currency->formatted_symbol)->toBe('$');

        $currencyWithoutSymbol = Currency::factory()->create(['symbol' => null, 'code' => 'USD']);
        expect($currencyWithoutSymbol->formatted_symbol)->toBe('USD');
    });

    it('casts attributes correctly', function () {
        $currency = Currency::factory()->create([
            'exchange_rate' => '1.234567',
            'is_default' => 1,
            'is_enabled' => 0,
            'decimal_places' => '2',
        ]);

        expect($currency->exchange_rate)->toBeFloat();
        expect($currency->is_default)->toBeBool();
        expect($currency->is_enabled)->toBeBool();
        expect($currency->decimal_places)->toBeInt();
    });

    it('uses soft deletes', function () {
        $currency = Currency::factory()->create();
        $currencyId = $currency->id;

        $currency->delete();

        expect(Currency::find($currencyId))->toBeNull();
        expect(Currency::withTrashed()->find($currencyId))->not->toBeNull();
    });

    it('has correct fillable attributes', function () {
        $fillable = [
            'name',
            'code',
            'symbol',
            'exchange_rate',
            'is_default',
            'is_enabled',
            'decimal_places',
        ];

        $currency = new Currency();
        expect($currency->getFillable())->toBe($fillable);
    });

    it('has correct table name', function () {
        $currency = new Currency();
        expect($currency->getTable())->toBe('currencies');
    });

    it('has translatable attributes defined', function () {
        $currency = new Currency();
        expect($currency->translatable)->toBe(['name']);
    });

    it('validates exchange rate precision', function () {
        $currency = Currency::factory()->create(['exchange_rate' => 1.123456]);

        // The exchange_rate should be cast to decimal with 6 decimal places
        expect($currency->exchange_rate)->toBeFloat();
        expect(number_format($currency->exchange_rate, 6))->toBe('1.123456');
    });

    it('can set and get translations', function () {
        $currency = Currency::factory()->create();

        $currency->setTranslation('name', 'en', 'English Name');
        $currency->setTranslation('name', 'lt', 'Lithuanian Name');
        $currency->save();

        $currency->refresh();

        expect($currency->getTranslation('name', 'en'))->toBe('English Name');
        expect($currency->getTranslation('name', 'lt'))->toBe('Lithuanian Name');
    });

    it('falls back to default locale for translations', function () {
        $currency = Currency::factory()->create([
            'name' => ['en' => 'English Name'],
        ]);

        // When requesting a translation that doesn't exist, it should fall back
        expect($currency->getTranslation('name', 'fr', 'en'))->toBe('English Name');
    });

    it('can check if currency is default', function () {
        $defaultCurrency = Currency::factory()->create(['is_default' => true]);
        $regularCurrency = Currency::factory()->create(['is_default' => false]);

        expect($defaultCurrency->is_default)->toBeTrue();
        expect($regularCurrency->is_default)->toBeFalse();
    });

    it('can check if currency is enabled', function () {
        $enabledCurrency = Currency::factory()->create(['is_enabled' => true]);
        $disabledCurrency = Currency::factory()->create(['is_enabled' => false]);

        expect($enabledCurrency->is_enabled)->toBeTrue();
        expect($disabledCurrency->is_enabled)->toBeFalse();
    });
});
