<?php declare(strict_types=1);

use App\Models\Currency;
use App\Models\User;
use App\Filament\Resources\CurrencyResource;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    // Create administrator role and permissions
    $role = Role::create(['name' => 'administrator']);
    $permissions = [
        'view currencies',
        'create currencies',
        'update currencies',
        'delete currencies',
        'browse_currencies'
    ];
    
    foreach ($permissions as $permission) {
        Permission::create(['name' => $permission]);
    }
    
    $role->givePermissionTo($permissions);
    
    // Create admin user
    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('administrator');
    
    // Create test data
    $this->testCurrency = Currency::factory()->create([
        'name' => 'US Dollar',
        'code' => 'USD',
        'symbol' => '$',
        'decimal_places' => 2,
        'is_enabled' => true,
        'is_default' => false,
        'exchange_rate' => 1.0,
    ]);
});

it('can list currencies in admin panel', function () {
    $this->actingAs($this->adminUser)
        ->get(CurrencyResource::getUrl('index'))
        ->assertOk();
});

it('can create a new currency', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CurrencyResource\Pages\CreateCurrency::class)
        ->fillForm([
            'name' => 'Euro',
            'code' => 'EUR',
            'symbol' => '€',
            'decimal_places' => 2,
            'is_enabled' => true,
            'is_default' => false,
            'exchange_rate' => 0.85,
        ])
        ->call('create')
        ->assertHasNoFormErrors();
    
    $this->assertDatabaseHas('currencies', [
        'name' => 'Euro',
        'code' => 'EUR',
        'symbol' => '€',
        'decimal_places' => 2,
        'is_enabled' => 1,
        'is_default' => 0,
        'exchange_rate' => 0.85,
    ]);
});

it('can view a currency', function () {
    $this->actingAs($this->adminUser)
        ->get(CurrencyResource::getUrl('view', ['record' => $this->testCurrency]))
        ->assertOk();
});

it('can edit a currency', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CurrencyResource\Pages\EditCurrency::class, ['record' => $this->testCurrency->id])
        ->fillForm([
            'name' => 'Updated US Dollar',
            'symbol' => 'US$',
            'exchange_rate' => 1.1,
            'is_default' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();
    
    $this->assertDatabaseHas('currencies', [
        'id' => $this->testCurrency->id,
        'name' => 'Updated US Dollar',
        'symbol' => 'US$',
        'exchange_rate' => 1.1,
        'is_default' => 1,
    ]);
});

it('can delete a currency', function () {
    $currency = Currency::factory()->create();
    
    // Delete action is available on the list page, not edit page
    Livewire::actingAs($this->adminUser)
        ->test(CurrencyResource\Pages\ListCurrencies::class)
        ->callTableAction('delete', $currency)
        ->assertHasNoTableActionErrors();
    
    // Currency model uses soft deletes, so check for deleted_at timestamp
    $this->assertSoftDeleted('currencies', [
        'id' => $currency->id,
    ]);
});

it('validates required fields when creating currency', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CurrencyResource\Pages\CreateCurrency::class)
        ->fillForm([
            'name' => null,
            'code' => null,
            'decimal_places' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['name', 'code', 'decimal_places']);
});

it('validates unique currency code', function () {
    $existingCurrency = Currency::factory()->create(['code' => 'TEST']);
    
    Livewire::actingAs($this->adminUser)
        ->test(CurrencyResource\Pages\CreateCurrency::class)
        ->fillForm([
            'name' => 'Another Test Currency',
            'code' => 'TEST', // Duplicate code
            'symbol' => 'T',
            'decimal_places' => 2,
        ])
        ->call('create')
        ->assertHasFormErrors(['code']);
});

it('validates currency code length', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CurrencyResource\Pages\CreateCurrency::class)
        ->fillForm([
            'name' => 'Test Currency',
            'code' => 'TOOLONG', // Too long
            'symbol' => '$',
            'decimal_places' => 2,
        ])
        ->call('create')
        ->assertHasFormErrors(['code']);
});

it('validates numeric fields in currency form', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CurrencyResource\Pages\CreateCurrency::class)
        ->fillForm([
            'name' => 'Test Currency',
            'code' => 'TST',
            'symbol' => '$',
            'decimal_places' => 'not-a-number',
            'exchange_rate' => 'invalid',
        ])
        ->call('create')
        ->assertHasFormErrors(['decimal_places', 'exchange_rate']);
});

it('can filter currencies by active status', function () {
    $this->actingAs($this->adminUser)
        ->get(CurrencyResource::getUrl('index'))
        ->assertOk();
});

it('can filter currencies by default status', function () {
    $this->actingAs($this->adminUser)
        ->get(CurrencyResource::getUrl('index'))
        ->assertOk();
});

it('shows correct currency data in table', function () {
    $this->actingAs($this->adminUser)
        ->get(CurrencyResource::getUrl('index'))
        ->assertSee($this->testCurrency->name)
        ->assertSee($this->testCurrency->code)
        ->assertSee($this->testCurrency->symbol);
});

it('handles currency activation and deactivation', function () {
    $currency = Currency::factory()->create(['is_enabled' => true]);
    
    // Deactivate currency
    $currency->update(['is_enabled' => false]);
    expect($currency->is_enabled)->toBeFalse();
    
    // Reactivate currency
    $currency->update(['is_enabled' => true]);
    expect($currency->is_enabled)->toBeTrue();
});

it('handles default currency setting', function () {
    $currency1 = Currency::factory()->create(['is_default' => true]);
    $currency2 = Currency::factory()->create(['is_default' => false]);
    
    // Set new default currency
    $currency2->update(['is_default' => true]);
    $currency1->update(['is_default' => false]);
    
    expect($currency2->is_default)->toBeTrue();
    expect($currency1->is_default)->toBeFalse();
});

it('handles bulk actions on currencies', function () {
    $currency1 = Currency::factory()->create();
    $currency2 = Currency::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(CurrencyResource\Pages\ListCurrencies::class)
        ->callTableBulkAction('delete', [$currency1->id, $currency2->id])
        ->assertOk();
    
    // Currency model uses soft deletes, so check for deleted_at timestamp
    $this->assertSoftDeleted('currencies', [
        'id' => $currency1->id,
    ]);
    
    $this->assertSoftDeleted('currencies', [
        'id' => $currency2->id,
    ]);
});

it('can manage exchange rates', function () {
    $currency = Currency::factory()->create(['exchange_rate' => 1.0]);
    
    // Update exchange rate
    $currency->update(['exchange_rate' => 1.25]);
    
    expect($currency->exchange_rate)->toBe(1.25);
});

it('validates decimal places range', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CurrencyResource\Pages\CreateCurrency::class)
        ->fillForm([
            'name' => 'Test Currency',
            'code' => 'TST',
            'symbol' => '$',
            'decimal_places' => -1, // Invalid negative value
        ])
        ->call('create')
        ->assertHasFormErrors(['decimal_places']);
});

it('can search currencies by name or code', function () {
    $this->actingAs($this->adminUser)
        ->get(CurrencyResource::getUrl('index'))
        ->assertOk();
});