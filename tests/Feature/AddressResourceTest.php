<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AddressType;
use App\Filament\Resources\AddressResource;
use App\Models\Address;
use App\Models\Country;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * AddressResourceTest
 *
 * Comprehensive test suite for AddressResource with Filament v4 compatibility
 */
final class AddressResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->country = Country::factory()->create();
        $this->zone = Zone::factory()->create();
    }

    /**
     * @test
     */
    public function it_can_render_address_resource(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(AddressResource\Pages\ListAddresses::class);

        $component->assertSuccessful();
    }

    /**
     * @test
     */
    public function it_can_create_address(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(AddressResource\Pages\CreateAddress::class);

        $component
            ->fillForm([
                'user_id' => $this->user->id,
                'type' => AddressType::SHIPPING,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'address_line_1' => '123 Main St',
                'city' => 'Vilnius',
                'postal_code' => '12345',
                'country_code' => 'LT',
                'country_id' => $this->country->id,
                'zone_id' => $this->zone->id,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('addresses', [
            'user_id' => $this->user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line_1' => '123 Main St',
            'city' => 'Vilnius',
            'postal_code' => '12345',
            'country_code' => 'LT',
        ]);
    }

    /**
     * @test
     */
    public function it_can_edit_address(): void
    {
        $this->actingAs($this->user);

        $address = Address::factory()->create([
            'user_id' => $this->user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $component = Livewire::test(AddressResource\Pages\EditAddress::class, [
            'record' => $address->getRouteKey(),
        ]);

        $component
            ->fillForm([
                'first_name' => 'Jane',
                'last_name' => 'Smith',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);
    }

    /**
     * @test
     */
    public function it_can_delete_address(): void
    {
        $this->actingAs($this->user);

        $address = Address::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $component = Livewire::test(AddressResource\Pages\ListAddresses::class);

        $component
            ->callTableAction('delete', $address)
            ->assertHasNoFormErrors();

        // Check that the address is soft deleted
        $this->assertSoftDeleted('addresses', [
            'id' => $address->id,
        ]);
    }

    /**
     * @test
     */
    public function it_can_set_address_as_default(): void
    {
        $this->actingAs($this->user);

        $address = Address::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => false,
        ]);

        $component = Livewire::test(AddressResource\Pages\ListAddresses::class);

        $component
            ->callTableAction('set_default', $address)
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'is_default' => true,
        ]);
    }

    /**
     * @test
     */
    public function it_can_duplicate_address(): void
    {
        $this->actingAs($this->user);

        $address = Address::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => true,
        ]);

        $component = Livewire::test(AddressResource\Pages\ListAddresses::class);

        $component
            ->callTableAction('duplicate', $address)
            ->assertHasNoFormErrors();

        $this->assertDatabaseCount('addresses', 2);
        $this->assertDatabaseHas('addresses', [
            'user_id' => $this->user->id,
            'first_name' => $address->first_name,
            'last_name' => $address->last_name,
            'is_default' => false,
        ]);
    }

    /**
     * @test
     */
    public function it_can_toggle_address_active_status(): void
    {
        $this->actingAs($this->user);

        $address = Address::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
        ]);

        $component = Livewire::test(AddressResource\Pages\ListAddresses::class);

        $component
            ->callTableAction('toggle_active', $address)
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'is_active' => false,
        ]);
    }

    /**
     * @test
     */
    public function it_can_filter_addresses_by_type(): void
    {
        $this->actingAs($this->user);

        Address::factory()->create([
            'user_id' => $this->user->id,
            'type' => AddressType::SHIPPING,
        ]);

        Address::factory()->create([
            'user_id' => $this->user->id,
            'type' => AddressType::BILLING,
        ]);

        $component = Livewire::test(AddressResource\Pages\ListAddresses::class);

        $component
            ->filterTable('type', AddressType::SHIPPING)
            ->assertCanSeeTableRecords(
                Address::where('type', AddressType::SHIPPING)->get()
            );
    }

    /**
     * @test
     */
    public function it_can_filter_addresses_by_country(): void
    {
        $this->actingAs($this->user);

        $country1 = Country::factory()->create();
        $country2 = Country::factory()->create();

        Address::factory()->create([
            'user_id' => $this->user->id,
            'country_code' => $country1->cca2,
        ]);

        Address::factory()->create([
            'user_id' => $this->user->id,
            'country_code' => $country2->cca2,
        ]);

        $component = Livewire::test(AddressResource\Pages\ListAddresses::class);

        $component
            ->filterTable('country_code', $country1->cca2)
            ->assertCanSeeTableRecords(
                Address::where('country_code', $country1->cca2)->get()
            );
    }

    /**
     * @test
     */
    public function it_can_perform_bulk_activate(): void
    {
        $this->actingAs($this->user);

        $addresses = Address::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'is_active' => false,
        ]);

        $component = Livewire::test(AddressResource\Pages\ListAddresses::class);

        $component
            ->callTableBulkAction('activate', $addresses)
            ->assertHasNoFormErrors();

        foreach ($addresses as $address) {
            $this->assertDatabaseHas('addresses', [
                'id' => $address->id,
                'is_active' => true,
            ]);
        }
    }

    /**
     * @test
     */
    public function it_can_perform_bulk_set_billing(): void
    {
        $this->actingAs($this->user);

        $addresses = Address::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'is_billing' => false,
        ]);

        $component = Livewire::test(AddressResource\Pages\ListAddresses::class);

        $component
            ->callTableBulkAction('set_billing', $addresses)
            ->assertHasNoFormErrors();

        foreach ($addresses as $address) {
            $this->assertDatabaseHas('addresses', [
                'id' => $address->id,
                'is_billing' => true,
            ]);
        }
    }

    /**
     * @test
     */
    public function it_can_perform_bulk_set_shipping(): void
    {
        $this->actingAs($this->user);

        $addresses = Address::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'is_shipping' => false,
        ]);

        $component = Livewire::test(AddressResource\Pages\ListAddresses::class);

        $component
            ->callTableBulkAction('set_shipping', $addresses)
            ->assertHasNoFormErrors();

        foreach ($addresses as $address) {
            $this->assertDatabaseHas('addresses', [
                'id' => $address->id,
                'is_shipping' => true,
            ]);
        }
    }

    /**
     * @test
     */
    public function it_validates_required_fields(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(AddressResource\Pages\CreateAddress::class);

        $component
            ->fillForm([
                'user_id' => null,
                'type' => null,
                'first_name' => null,
                'last_name' => null,
                'address_line_1' => null,
                'city' => null,
                'postal_code' => null,
                'country_code' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'user_id',
                'type',
                'first_name',
                'last_name',
                'address_line_1',
                'city',
                'postal_code',
                'country_code',
            ]);
    }

    /**
     * @test
     */
    public function it_validates_email_format(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(AddressResource\Pages\CreateAddress::class);

        $component
            ->fillForm([
                'user_id' => $this->user->id,
                'type' => AddressType::SHIPPING,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'address_line_1' => '123 Main St',
                'city' => 'Vilnius',
                'postal_code' => '12345',
                'country_code' => 'LT',
                'email' => 'invalid-email',
            ])
            ->call('create')
            ->assertHasFormErrors(['email']);
    }

    /**
     * @test
     */
    public function it_validates_phone_format(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(AddressResource\Pages\CreateAddress::class);

        $component
            ->fillForm([
                'user_id' => $this->user->id,
                'type' => AddressType::SHIPPING,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'address_line_1' => '123 Main St',
                'city' => 'Vilnius',
                'postal_code' => '12345',
                'country_code' => 'LT',
                'phone' => 'invalid-phone',
            ])
            ->call('create')
            ->assertHasFormErrors(['phone']);
    }
}
