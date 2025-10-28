<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AddressType;
use App\Filament\Resources\AddressResource\Pages\CreateAddress;
use App\Filament\Resources\AddressResource\Pages\EditAddress;
use App\Filament\Resources\AddressResource\Pages\ListAddresses;
use App\Filament\Resources\AddressResource\Pages\ViewAddress;
use App\Models\Address;
use App\Models\Country;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class AddressResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user for authentication
        $this->adminUser = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        // Create test country
        $this->country = Country::factory()->create(['cca2' => 'LT']);

        Filament::setCurrentPanel('admin');
        $this->resolveAdminPanel(); // Ensure panel providers register all components before assertions.
        $this->actingAs($this->adminUser); // Authenticate HTTP requests so panel routes stay accessible during tests.
    }

    public function test_can_list_addresses(): void
    {
        // Arrange
        $addresses = Address::factory()->for($this->adminUser)->count(5)->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAddresses::class)
            ->assertOk();
    }

    public function test_can_create_address(): void
    {
        // Arrange
        $user = User::factory()->create();
        $addressData = [
            'user_id'        => $user->id,
            'type'           => AddressType::SHIPPING->value,
            'first_name'     => 'John',
            'last_name'      => 'Doe',
            'address_line_1' => '123 Main St',
            'city'           => 'Vilnius',
            'postal_code'    => '01001',
            'country_code'   => 'LT',
            'is_active'      => true,
            // Explicitly provide a phone number so the Filament form passes validation under stricter rules.
            'phone' => '+37060000000',
        ];

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(CreateAddress::class)
            ->fillForm($addressData, 'form')
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('addresses', [
            'user_id'        => $user->id,
            'first_name'     => 'John',
            'last_name'      => 'Doe',
            'address_line_1' => '123 Main St',
            'city'           => 'Vilnius',
            'postal_code'    => '01001',
            'country_code'   => 'LT',
        ]);
    }

    public function test_can_edit_address(): void
    {
        // Arrange
        $address = Address::factory()->for($this->adminUser)->create([
            'country_code' => 'LT',
            // Seed a deterministic phone number to avoid nullable factory output that violates validation.
            'phone' => '+37060000000',
        ]);
        $newCity = 'Kaunas';

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(EditAddress::class, ['record' => $address->id])
            ->fillForm([
                'user_id'      => (string) $address->user_id,
                'city'         => $newCity,
                'country_code' => $address->country_code,
                // Preserve existing required fields to satisfy validation while focusing the test on the city update.
                'first_name'     => $address->first_name,
                'last_name'      => $address->last_name,
                'address_line_1' => $address->address_line_1,
                'postal_code'    => $address->postal_code,
                'phone'          => '+37060000000',
            ], 'form')
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('addresses', [
            'id'   => $address->id,
            'city' => $newCity,
        ]);
    }

    public function test_can_view_address(): void
    {
        // Arrange
        $address = Address::factory()->for($this->adminUser)->create(['country_code' => 'LT']);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ViewAddress::class, ['record' => $address->id])
            ->assertOk();
    }

    public function test_can_filter_addresses_by_type(): void
    {
        // Arrange
        Address::factory()->for($this->adminUser)->count(3)->create(['type' => AddressType::SHIPPING]);
        Address::factory()->for($this->adminUser)->count(2)->create(['type' => AddressType::BILLING]);

        // Act & Assert
        $this
            ->get('/admin/addresses?tableFilters[type][value]=' . AddressType::SHIPPING->value)
            ->assertOk();
    }

    public function test_can_filter_addresses_by_country(): void
    {
        // Arrange
        Address::factory()->for($this->adminUser)->count(3)->create(['country_code' => 'LT']);
        Address::factory()->for($this->adminUser)->count(2)->create(['country_code' => 'US']);

        // Act & Assert
        $this
            ->get('/admin/addresses?tableFilters[country_code][value]=LT')
            ->assertOk();
    }

    public function test_can_set_address_as_default(): void
    {
        // Arrange
        $address = Address::factory()->for($this->adminUser)->create(['is_default' => false]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAddresses::class)
            ->callTableAction('set_default', $address)
            ->assertNotified();

        $this->assertDatabaseHas('addresses', [
            'id'         => $address->id,
            'is_default' => true,
        ]);
    }

    public function test_can_duplicate_address(): void
    {
        // Arrange
        $address = Address::factory()->for($this->adminUser)->create(['is_default' => true]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAddresses::class)
            ->callTableAction('duplicate', $address)
            ->assertNotified();

        $this->assertDatabaseHas('addresses', [
            'user_id'    => $address->user_id,
            'first_name' => $address->first_name,
            'is_default' => false,
        ]);
    }

    public function test_can_toggle_address_active_status(): void
    {
        // Arrange
        $address = Address::factory()->for($this->adminUser)->create(['is_active' => true]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAddresses::class)
            ->callTableAction('toggle_active', $address)
            ->assertNotified();

        $this->assertDatabaseHas('addresses', [
            'id'        => $address->id,
            'is_active' => false,
        ]);
    }

    public function test_can_bulk_activate_addresses(): void
    {
        // Arrange
        $addresses = Address::factory()->for($this->adminUser)->count(3)->create(['is_active' => false]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAddresses::class)
            ->callTableBulkAction('activate', $addresses)
            ->assertNotified();

        foreach ($addresses as $address) {
            $this->assertDatabaseHas('addresses', [
                'id'        => $address->id,
                'is_active' => true,
            ]);
        }
    }

    public function test_can_bulk_set_addresses_as_billing(): void
    {
        // Arrange
        $addresses = Address::factory()->for($this->adminUser)->count(3)->create(['is_billing' => false]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAddresses::class)
            ->callTableBulkAction('set_billing', $addresses)
            ->assertNotified();

        foreach ($addresses as $address) {
            $this->assertDatabaseHas('addresses', [
                'id'         => $address->id,
                'is_billing' => true,
            ]);
        }
    }

    public function test_can_search_addresses(): void
    {
        // Arrange
        Address::factory()->for($this->adminUser)->create(['first_name' => 'Unique Name']);
        Address::factory()->for($this->adminUser)->count(3)->create();

        // Act & Assert
        $this
            ->get('/admin/addresses?search=Unique%20Name')
            ->assertOk();
    }

    public function test_can_sort_addresses_by_created_at(): void
    {
        // Arrange
        Address::factory()->for($this->adminUser)->create(['created_at' => now()->subDay()]);
        Address::factory()->for($this->adminUser)->create(['created_at' => now()]);

        // Act & Assert
        $this
            ->get('/admin/addresses?sort=created_at&direction=desc')
            ->assertOk();
    }

    public function test_validates_required_fields_on_create(): void
    {
        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(CreateAddress::class)
            ->fillForm([], 'form')
            ->call('create')
            ->assertHasFormErrors(['user_id', 'address_line_1', 'city', 'postal_code']);
    }

    public function test_can_export_addresses(): void
    {
        // Arrange
        $addresses = Address::factory()->for($this->adminUser)->count(5)->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAddresses::class)
            ->callTableBulkAction('export', $addresses)
            ->assertNotified();
    }

    public function test_can_delete_address(): void
    {
        // Arrange
        $address = Address::factory()->for($this->adminUser)->create(['country_code' => 'LT']);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAddresses::class)
            ->callTableAction('delete', $address, [], ['confirmed' => true])
            ->assertNotified();

        $this->assertSoftDeleted('addresses', ['id' => $address->id]);
    }

    public function test_can_bulk_delete_addresses(): void
    {
        // Arrange
        $addresses = Address::factory()->for($this->adminUser)->count(3)->create(['country_code' => 'LT']);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAddresses::class)
            ->callTableBulkAction('delete', $addresses, [], ['confirmed' => true])
            ->assertNotified();

        foreach ($addresses as $address) {
            $this->assertSoftDeleted('addresses', ['id' => $address->id]);
        }
    }

    public function test_can_filter_addresses_with_company(): void
    {
        // Arrange
        Address::factory()->for($this->adminUser)->count(3)->create(['company_name' => 'Test Company']);
        Address::factory()->for($this->adminUser)->count(2)->create(['company_name' => null]);

        // Act & Assert
        $this
            ->get('/admin/addresses?tableFilters[has_company][isActive]=true')
            ->assertOk();
    }

    public function test_can_filter_addresses_created_this_month(): void
    {
        // Arrange
        Address::factory()->for($this->adminUser)->count(3)->create(['created_at' => now()]);
        Address::factory()->for($this->adminUser)->count(2)->create(['created_at' => now()->subMonth()]);

        // Act & Assert
        $this
            ->get('/admin/addresses?tableFilters[created_this_month][isActive]=true')
            ->assertOk();
    }
}
