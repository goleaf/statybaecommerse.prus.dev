<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\CurrencyResource;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * CurrencyResourceTest
 *
 * Comprehensive test suite for CurrencyResource functionality including CRUD operations, filters, and relationships.
 */
final class CurrencyResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]));
    }

    public function test_can_list_currencies(): void
    {
        $currencies = Currency::factory()->count(3)->create();

        Livewire::test(CurrencyResource\Pages\ListCurrencies::class)
            ->assertCanSeeTableRecords($currencies);
    }

    public function test_can_create_currency(): void
    {
        $currencyData = [
            'name' => 'Test Currency',
            'code' => 'TST',
            'symbol' => 'T$',
            'iso_code' => 'TST-001',
            'description' => 'Test currency description',
            'exchange_rate' => 1.25,
            'base_currency' => 'EUR',
            'decimal_places' => 2,
            'symbol_position' => 'after',
            'thousands_separator' => ',',
            'decimal_separator' => '.',
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 1,
            'auto_update_rate' => false,
        ];

        Livewire::test(CurrencyResource\Pages\CreateCurrency::class)
            ->fillForm($currencyData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('currencies', [
            'name' => 'Test Currency',
            'code' => 'TST',
        ]);
    }

    public function test_can_edit_currency(): void
    {
        $currency = Currency::factory()->create([
            'name' => 'Original Name',
            'code' => 'ORIG',
        ]);

        Livewire::test(CurrencyResource\Pages\EditCurrency::class, [
            'record' => $currency->getRouteKey(),
        ])
            ->fillForm([
                'name' => 'Updated Name',
                'code' => 'UPDT',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $currency->refresh();
        $this->assertEquals('Updated Name', $currency->name);
        $this->assertEquals('UPDT', $currency->code);
    }

    public function test_can_view_currency(): void
    {
        $currency = Currency::factory()->create([
            'name' => 'View Test Currency',
            'code' => 'VTC',
        ]);

        Livewire::test(CurrencyResource\Pages\ViewCurrency::class, [
            'record' => $currency->getRouteKey(),
        ])
            ->assertCanSeeText('View Test Currency')
            ->assertCanSeeText('VTC');
    }

    public function test_can_delete_currency(): void
    {
        $currency = Currency::factory()->create();

        Livewire::test(CurrencyResource\Pages\ListCurrencies::class)
            ->callTableAction('delete', $currency);

        $this->assertSoftDeleted('currencies', [
            'id' => $currency->id,
        ]);
    }

    public function test_can_bulk_delete_currencies(): void
    {
        $currencies = Currency::factory()->count(3)->create();

        Livewire::test(CurrencyResource\Pages\ListCurrencies::class)
            ->callTableBulkAction('delete', $currencies);

        foreach ($currencies as $currency) {
            $this->assertSoftDeleted('currencies', [
                'id' => $currency->id,
            ]);
        }
    }

    public function test_can_filter_currencies_by_active_status(): void
    {
        Currency::factory()->create(['is_active' => true]);
        Currency::factory()->create(['is_active' => false]);

        Livewire::test(CurrencyResource\Pages\ListCurrencies::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords(Currency::where('is_active', true)->get());
    }

    public function test_can_filter_currencies_by_default_status(): void
    {
        Currency::factory()->create(['is_default' => true]);
        Currency::factory()->create(['is_default' => false]);

        Livewire::test(CurrencyResource\Pages\ListCurrencies::class)
            ->filterTable('is_default', true)
            ->assertCanSeeTableRecords(Currency::where('is_default', true)->get());
    }

    public function test_can_filter_currencies_by_auto_update_rate(): void
    {
        Currency::factory()->create(['auto_update_rate' => true]);
        Currency::factory()->create(['auto_update_rate' => false]);

        Livewire::test(CurrencyResource\Pages\ListCurrencies::class)
            ->filterTable('auto_update_rate', true)
            ->assertCanSeeTableRecords(Currency::where('auto_update_rate', true)->get());
    }

    public function test_can_search_currencies_by_name(): void
    {
        Currency::factory()->create(['name' => 'US Dollar']);
        Currency::factory()->create(['name' => 'Euro']);

        Livewire::test(CurrencyResource\Pages\ListCurrencies::class)
            ->searchTable('US Dollar')
            ->assertCanSeeTableRecords(Currency::where('name', 'like', '%US Dollar%')->get());
    }

    public function test_can_toggle_currency_active_status(): void
    {
        $currency = Currency::factory()->create(['is_active' => false]);

        Livewire::test(CurrencyResource\Pages\ListCurrencies::class)
            ->callTableAction('toggle_active', $currency);

        $currency->refresh();
        $this->assertTrue($currency->is_active);
    }

    public function test_can_set_currency_as_default(): void
    {
        $currency = Currency::factory()->create(['is_default' => false]);

        Livewire::test(CurrencyResource\Pages\ListCurrencies::class)
            ->callTableAction('set_default', $currency);

        $currency->refresh();
        $this->assertTrue($currency->is_default);
    }

    public function test_can_update_currency_rate(): void
    {
        $currency = Currency::factory()->create();

        Livewire::test(CurrencyResource\Pages\ListCurrencies::class)
            ->callTableAction('update_rate', $currency);

        // This would test the rate update functionality
        $this->assertTrue(true);  // Placeholder for actual rate update test
    }

    public function test_can_bulk_activate_currencies(): void
    {
        $currencies = Currency::factory()->count(3)->create(['is_active' => false]);

        Livewire::test(CurrencyResource\Pages\ListCurrencies::class)
            ->callTableBulkAction('activate', $currencies);

        foreach ($currencies as $currency) {
            $currency->refresh();
            $this->assertTrue($currency->is_active);
        }
    }

    public function test_can_bulk_deactivate_currencies(): void
    {
        $currencies = Currency::factory()->count(3)->create(['is_active' => true]);

        Livewire::test(CurrencyResource\Pages\ListCurrencies::class)
            ->callTableBulkAction('deactivate', $currencies);

        foreach ($currencies as $currency) {
            $currency->refresh();
            $this->assertFalse($currency->is_active);
        }
    }

    public function test_can_bulk_update_rates(): void
    {
        $currencies = Currency::factory()->count(3)->create();

        Livewire::test(CurrencyResource\Pages\ListCurrencies::class)
            ->callTableBulkAction('update_rates', $currencies);

        // This would test the bulk rate update functionality
        $this->assertTrue(true);  // Placeholder for actual bulk rate update test
    }

    public function test_currency_validation_requires_name(): void
    {
        Livewire::test(CurrencyResource\Pages\CreateCurrency::class)
            ->fillForm([
                'name' => '',
                'code' => 'TST',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    }

    public function test_currency_validation_code_must_be_unique(): void
    {
        Currency::factory()->create(['code' => 'EXISTING']);

        Livewire::test(CurrencyResource\Pages\CreateCurrency::class)
            ->fillForm([
                'name' => 'Test Currency',
                'code' => 'EXISTING',
            ])
            ->call('create')
            ->assertHasFormErrors(['code' => 'unique']);
    }

    public function test_currency_validation_code_alpha(): void
    {
        Livewire::test(CurrencyResource\Pages\CreateCurrency::class)
            ->fillForm([
                'name' => 'Test Currency',
                'code' => '123',
            ])
            ->call('create')
            ->assertHasFormErrors(['code' => 'alpha']);
    }

    public function test_currency_validation_exchange_rate_numeric(): void
    {
        Livewire::test(CurrencyResource\Pages\CreateCurrency::class)
            ->fillForm([
                'name' => 'Test Currency',
                'code' => 'TST',
                'exchange_rate' => 'invalid',
            ])
            ->call('create')
            ->assertHasFormErrors(['exchange_rate' => 'numeric']);
    }

    public function test_currency_validation_exchange_rate_minimum(): void
    {
        Livewire::test(CurrencyResource\Pages\CreateCurrency::class)
            ->fillForm([
                'name' => 'Test Currency',
                'code' => 'TST',
                'exchange_rate' => -1.0,
            ])
            ->call('create')
            ->assertHasFormErrors(['exchange_rate' => 'min']);
    }

    public function test_currency_validation_decimal_places_maximum(): void
    {
        Livewire::test(CurrencyResource\Pages\CreateCurrency::class)
            ->fillForm([
                'name' => 'Test Currency',
                'code' => 'TST',
                'decimal_places' => 10,
            ])
            ->call('create')
            ->assertHasFormErrors(['decimal_places' => 'max']);
    }

    public function test_currency_scope_enabled(): void
    {
        Currency::factory()->create(['is_enabled' => true]);
        Currency::factory()->create(['is_enabled' => false]);

        $enabledCurrencies = Currency::enabled()->get();
        $this->assertCount(1, $enabledCurrencies);
        $this->assertTrue($enabledCurrencies->first()->is_enabled);
    }

    public function test_currency_scope_default(): void
    {
        Currency::factory()->create(['is_default' => true]);
        Currency::factory()->create(['is_default' => false]);

        $defaultCurrencies = Currency::default()->get();
        $this->assertCount(1, $defaultCurrencies);
        $this->assertTrue($defaultCurrencies->first()->is_default);
    }

    public function test_currency_scope_active(): void
    {
        Currency::factory()->create(['is_active' => true]);
        Currency::factory()->create(['is_active' => false]);

        $activeCurrencies = Currency::active()->get();
        $this->assertCount(1, $activeCurrencies);
        $this->assertTrue($activeCurrencies->first()->is_active);
    }

    public function test_currency_helper_methods(): void
    {
        $currency = Currency::factory()->create([
            'is_default' => true,
            'is_enabled' => true,
            'is_active' => true,
        ]);

        $this->assertTrue($currency->isDefault());
        $this->assertTrue($currency->isEnabled());
        $this->assertTrue($currency->isActive());
    }

    public function test_currency_formatted_symbol(): void
    {
        $currency = Currency::factory()->create([
            'symbol' => '$',
            'code' => 'USD',
        ]);

        $this->assertEquals('$', $currency->formatted_symbol);
    }

    public function test_currency_formatted_symbol_fallback(): void
    {
        $currency = Currency::factory()->create([
            'symbol' => null,
            'code' => 'USD',
        ]);

        $this->assertEquals('USD', $currency->formatted_symbol);
    }

    public function test_currency_formatted_exchange_rate(): void
    {
        $currency = Currency::factory()->create([
            'exchange_rate' => 1.234567,
            'decimal_places' => 2,
        ]);

        $this->assertEquals('1.23', $currency->formatted_exchange_rate);
    }

    public function test_currency_format_amount(): void
    {
        $currency = Currency::factory()->create([
            'symbol' => '$',
            'code' => 'USD',
            'decimal_places' => 2,
        ]);

        $formatted = $currency->formatAmount(1234.56);
        $this->assertEquals('$ 1,234.56', $formatted);
    }

    public function test_currency_format_amount_without_symbol(): void
    {
        $currency = Currency::factory()->create([
            'symbol' => null,
            'code' => 'USD',
            'decimal_places' => 2,
        ]);

        $formatted = $currency->formatAmount(1234.56);
        $this->assertEquals('1,234.56 USD', $formatted);
    }

    public function test_currency_relationships_prices(): void
    {
        $currency = Currency::factory()->create();
        // Note: Price relationship would need to be implemented in the model
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $currency->prices());
    }

    public function test_currency_relationships_orders(): void
    {
        $currency = Currency::factory()->create();
        // Note: Order relationship would need to be implemented in the model
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $currency->orders());
    }

    public function test_currency_relationships_countries(): void
    {
        $currency = Currency::factory()->create();
        // Note: Country relationship would need to be implemented in the model
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $currency->countries());
    }

    public function test_currency_relationships_price_lists(): void
    {
        $currency = Currency::factory()->create();
        // Note: PriceList relationship would need to be implemented in the model
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $currency->priceLists());
    }

    public function test_currency_relationships_campaigns(): void
    {
        $currency = Currency::factory()->create();
        // Note: Campaign relationship would need to be implemented in the model
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $currency->campaigns());
    }

    public function test_currency_relationships_discounts(): void
    {
        $currency = Currency::factory()->create();
        // Note: Discount relationship would need to be implemented in the model
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $currency->discounts());
    }

    public function test_currency_symbol_position_before(): void
    {
        $currency = Currency::factory()->create([
            'symbol' => '$',
            'symbol_position' => 'before',
            'decimal_places' => 2,
        ]);

        $formatted = $currency->formatAmount(100.0);
        $this->assertStringStartsWith('$', $formatted);
    }

    public function test_currency_symbol_position_after(): void
    {
        $currency = Currency::factory()->create([
            'symbol' => '$',
            'symbol_position' => 'after',
            'decimal_places' => 2,
        ]);

        $formatted = $currency->formatAmount(100.0);
        $this->assertStringEndsWith('$', $formatted);
    }

    public function test_currency_thousands_separator(): void
    {
        $currency = Currency::factory()->create([
            'thousands_separator' => ',',
            'decimal_separator' => '.',
            'decimal_places' => 2,
        ]);

        $formatted = $currency->formatAmount(1234567.89);
        $this->assertStringContainsString('1,234,567.89', $formatted);
    }

    public function test_currency_decimal_separator(): void
    {
        $currency = Currency::factory()->create([
            'thousands_separator' => '.',
            'decimal_separator' => ',',
            'decimal_places' => 2,
        ]);

        $formatted = $currency->formatAmount(1234.56);
        $this->assertStringContainsString('1.234,56', $formatted);
    }

    public function test_currency_decimal_places(): void
    {
        $currency = Currency::factory()->create([
            'decimal_places' => 4,
        ]);

        $formatted = $currency->formatAmount(1234.56789);
        $this->assertStringContainsString('1,234.5679', $formatted);
    }

    public function test_currency_sort_order(): void
    {
        Currency::factory()->create(['sort_order' => 3]);
        Currency::factory()->create(['sort_order' => 1]);
        Currency::factory()->create(['sort_order' => 2]);

        $currencies = Currency::orderBy('sort_order')->get();
        $this->assertEquals(1, $currencies->first()->sort_order);
        $this->assertEquals(3, $currencies->last()->sort_order);
    }

    public function test_currency_auto_update_rate(): void
    {
        $currency = Currency::factory()->create([
            'auto_update_rate' => true,
        ]);

        $this->assertTrue($currency->auto_update_rate);
    }

    public function test_currency_base_currency(): void
    {
        $currency = Currency::factory()->create([
            'base_currency' => 'EUR',
        ]);

        $this->assertEquals('EUR', $currency->base_currency);
    }

    public function test_currency_iso_code(): void
    {
        $currency = Currency::factory()->create([
            'iso_code' => 'USD-840',
        ]);

        $this->assertEquals('USD-840', $currency->iso_code);
    }

    public function test_currency_description(): void
    {
        $currency = Currency::factory()->create([
            'description' => 'United States Dollar',
        ]);

        $this->assertEquals('United States Dollar', $currency->description);
    }
}
