<?php

declare(strict_types=1);

use App\Models\Currency;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
// Keep the database lifecycle consistent; the shared Pest bootstrap wires the Laravel TestCase automatically.
uses(RefreshDatabase::class);

it('exposes expected fillable attributes for mass assignment', function (): void {
    // Instantiate the model to inspect its fillable configuration without hitting the database.
    $fillable = (new Currency)->getFillable();

    expect($fillable)->toMatchArray([
        'name',
        'code',
        'symbol',
        'iso_code',
        'description',
        'exchange_rate',
        'base_currency',
        'decimal_places',
        'symbol_position',
        'thousands_separator',
        'decimal_separator',
        'is_active',
        'is_default',
        'is_enabled',
        'sort_order',
        'auto_update_rate',
    ]);
});

it('casts numeric and boolean attributes predictably', function (): void {
    $currency = Currency::factory()->create([
        'name'             => 'Test Currency',
        'code'             => 'TST',
        'iso_code'         => 'TST-001',
        'exchange_rate'    => 1.2345,
        'decimal_places'   => 3,
        'is_active'        => true,
        'auto_update_rate' => true,
    ]);

    expect($currency->exchange_rate)->toBeFloat();
    expect($currency->decimal_places)->toBeInt();
    expect($currency->is_active)->toBeTrue();
    expect($currency->auto_update_rate)->toBeTrue();
});

it('orders currencies by their code using the shared scope', function (): void {
    Currency::factory()->create(['name' => 'US Dollar', 'code' => 'USD', 'iso_code' => 'USD-840']);
    Currency::factory()->create(['name' => 'Euro', 'code' => 'EUR', 'iso_code' => 'EUR-978']);
    Currency::factory()->create(['name' => 'British Pound', 'code' => 'GBP', 'iso_code' => 'GBP-826']);

    $ascending = Currency::query()->orderedByName()->pluck('code');
    $descending = Currency::query()->orderedByName('desc')->pluck('code');

    expect($ascending)->toBeInstanceOf(Collection::class);
    expect($ascending->all())->toBe(['EUR', 'GBP', 'USD']);
    expect($descending->all())->toBe(['USD', 'GBP', 'EUR']);
});

it('exposes relationships to related models', function (): void {
    $currency = new Currency;

    expect($currency->prices())->toBeInstanceOf(HasMany::class);
    expect($currency->orders())->toBeInstanceOf(HasMany::class);
    expect($currency->translations())->toBeInstanceOf(HasMany::class);
    expect($currency->countries())->toBeInstanceOf(BelongsToMany::class);
    expect($currency->priceLists())->toBeInstanceOf(HasMany::class);
    expect($currency->campaigns())->toBeInstanceOf(HasMany::class);
    expect($currency->discounts())->toBeInstanceOf(HasMany::class);
});
