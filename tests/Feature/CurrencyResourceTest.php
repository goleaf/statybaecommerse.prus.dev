<?php declare(strict_types=1);

use App\Filament\Resources\CurrencyResource;
use App\Models\Currency;
use App\Models\User;
use Filament\Actions\Testing\TestAction;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

describe('Currency Resource', function () {
    it('can render currency index page', function () {
        $this
            ->get(CurrencyResource::getUrl('index'))
            ->assertSuccessful();
    });

    it('can list currencies', function () {
        $currencies = Currency::factory()->count(3)->create();

        livewire(CurrencyResource\Pages\ListCurrencies::class)
            ->assertCanSeeTableRecords($currencies);
    });

    it('can render currency create page', function () {
        $this
            ->get(CurrencyResource::getUrl('create'))
            ->assertSuccessful();
    });

    it('can create currency', function () {
        $newData = Currency::factory()->make();

        livewire(CurrencyResource\Pages\CreateCurrency::class)
            ->fillForm([
                'code' => $newData->code,
                'symbol' => $newData->symbol,
                'exchange_rate' => $newData->exchange_rate,
                'decimal_places' => $newData->decimal_places,
                'is_enabled' => $newData->is_enabled,
                'is_default' => $newData->is_default,
                'name' => [
                    'en' => 'US Dollar',
                    'lt' => 'JAV doleris',
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Currency::class, [
            'code' => $newData->code,
            'symbol' => $newData->symbol,
            'is_enabled' => $newData->is_enabled,
            'is_default' => $newData->is_default,
        ]);
    });

    it('can validate currency creation', function () {
        livewire(CurrencyResource\Pages\CreateCurrency::class)
            ->fillForm([
                'code' => '',
                'symbol' => '',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'code' => 'required',
                'symbol' => 'required',
            ]);
    });

    it('can render currency edit page', function () {
        $currency = Currency::factory()->create();

        $this->get(CurrencyResource::getUrl('edit', [
            'record' => $currency,
        ]))->assertSuccessful();
    });

    it('can retrieve currency data for editing', function () {
        $currency = Currency::factory()->create();

        livewire(CurrencyResource\Pages\EditCurrency::class, [
            'record' => $currency->getRouteKey(),
        ])
            ->assertFormSet([
                'code' => $currency->code,
                'symbol' => $currency->symbol,
                'exchange_rate' => $currency->exchange_rate,
                'decimal_places' => $currency->decimal_places,
                'is_enabled' => $currency->is_enabled,
                'is_default' => $currency->is_default,
            ]);
    });

    it('can save currency', function () {
        $currency = Currency::factory()->create();
        $newData = Currency::factory()->make();

        livewire(CurrencyResource\Pages\EditCurrency::class, [
            'record' => $currency->getRouteKey(),
        ])
            ->fillForm([
                'code' => $newData->code,
                'symbol' => $newData->symbol,
                'exchange_rate' => $newData->exchange_rate,
                'decimal_places' => $newData->decimal_places,
                'is_enabled' => $newData->is_enabled,
                'is_default' => $newData->is_default,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($currency->refresh())
            ->code
            ->toBe($newData->code)
            ->symbol
            ->toBe($newData->symbol)
            ->exchange_rate
            ->toEqual($newData->exchange_rate)
            ->decimal_places
            ->toBe($newData->decimal_places)
            ->is_enabled
            ->toBe($newData->is_enabled)
            ->is_default
            ->toBe($newData->is_default);
    });

    it('can render currency view page', function () {
        $currency = Currency::factory()->create();

        $this->get(CurrencyResource::getUrl('view', [
            'record' => $currency,
        ]))->assertSuccessful();
    });

    it('can delete currency', function () {
        $currency = Currency::factory()->create();

        livewire(CurrencyResource\Pages\ListCurrencies::class)
            ->callAction(TestAction::make('delete')->table($currency));

        $this->assertSoftDeleted($currency);
    });

    it('can bulk delete currencies', function () {
        $currencies = Currency::factory()->count(3)->create();

        livewire(CurrencyResource\Pages\ListCurrencies::class)
            ->selectTableRecords($currencies->pluck('id')->toArray())
            ->callAction(TestAction::make('delete')->table()->bulk());

        foreach ($currencies as $currency) {
            $this->assertSoftDeleted($currency);
        }
    });

    it('can filter currencies by enabled status', function () {
        $enabledCurrency = Currency::factory()->create(['is_enabled' => true]);
        $disabledCurrency = Currency::factory()->create(['is_enabled' => false]);

        livewire(CurrencyResource\Pages\ListCurrencies::class)
            ->filterTable('is_enabled', true)
            ->assertCanSeeTableRecords([$enabledCurrency])
            ->assertCanNotSeeTableRecords([$disabledCurrency]);
    });

    it('can filter currencies by default status', function () {
        $defaultCurrency = Currency::factory()->create(['is_default' => true]);
        $nonDefaultCurrency = Currency::factory()->create(['is_default' => false]);

        livewire(CurrencyResource\Pages\ListCurrencies::class)
            ->filterTable('is_default', true)
            ->assertCanSeeTableRecords([$defaultCurrency])
            ->assertCanNotSeeTableRecords([$nonDefaultCurrency]);
    });

    it('can search currencies by name', function () {
        $currency1 = Currency::factory()->create(['name' => ['en' => 'US Dollar']]);
        $currency2 = Currency::factory()->create(['name' => ['en' => 'Euro']]);

        livewire(CurrencyResource\Pages\ListCurrencies::class)
            ->searchTable('Dollar')
            ->assertCanSeeTableRecords([$currency1])
            ->assertCanNotSeeTableRecords([$currency2]);
    });

    it('can search currencies by code', function () {
        $currency1 = Currency::factory()->create(['code' => 'USD']);
        $currency2 = Currency::factory()->create(['code' => 'EUR']);

        livewire(CurrencyResource\Pages\ListCurrencies::class)
            ->searchTable('USD')
            ->assertCanSeeTableRecords([$currency1])
            ->assertCanNotSeeTableRecords([$currency2]);
    });

    it('can sort currencies by name', function () {
        $currencyA = Currency::factory()->create(['name' => ['en' => 'A Currency']]);
        $currencyZ = Currency::factory()->create(['name' => ['en' => 'Z Currency']]);

        livewire(CurrencyResource\Pages\ListCurrencies::class)
            ->sortTable('name')
            ->assertCanSeeTableRecords([$currencyA, $currencyZ], inOrder: true);
    });

    it('can sort currencies by exchange rate', function () {
        $lowRate = Currency::factory()->create(['exchange_rate' => 0.5]);
        $highRate = Currency::factory()->create(['exchange_rate' => 2.0]);

        livewire(CurrencyResource\Pages\ListCurrencies::class)
            ->sortTable('exchange_rate')
            ->assertCanSeeTableRecords([$lowRate, $highRate], inOrder: true);
    });

    it('validates unique currency code', function () {
        $existingCurrency = Currency::factory()->create(['code' => 'USD']);

        livewire(CurrencyResource\Pages\CreateCurrency::class)
            ->fillForm([
                'code' => 'USD',
                'symbol' => '$',
                'name' => ['en' => 'US Dollar'],
            ])
            ->call('create')
            ->assertHasFormErrors(['code' => 'unique']);
    });

    it('validates currency code length', function () {
        livewire(CurrencyResource\Pages\CreateCurrency::class)
            ->fillForm([
                'code' => 'TOOLONG',
                'symbol' => '$',
                'name' => ['en' => 'Test Currency'],
            ])
            ->call('create')
            ->assertHasFormErrors(['code' => 'max']);
    });

    it('validates exchange rate is numeric', function () {
        livewire(CurrencyResource\Pages\CreateCurrency::class)
            ->fillForm([
                'code' => 'TST',
                'symbol' => 'T',
                'exchange_rate' => 'not-a-number',
                'name' => ['en' => 'Test Currency'],
            ])
            ->call('create')
            ->assertHasFormErrors(['exchange_rate' => 'numeric']);
    });

    it('validates decimal places range', function () {
        livewire(CurrencyResource\Pages\CreateCurrency::class)
            ->fillForm([
                'code' => 'TST',
                'symbol' => 'T',
                'decimal_places' => 10,
                'name' => ['en' => 'Test Currency'],
            ])
            ->call('create')
            ->assertHasFormErrors(['decimal_places' => 'max']);
    });
});
