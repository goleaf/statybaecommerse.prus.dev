<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Address;
use App\Models\User;
use App\Models\Country;
use App\Models\Region;
use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_address_can_be_created(): void
    {
        $user = User::factory()->create();
        
        $address = Address::factory()->create([
            'user_id' => $user->id,
            'type' => 'shipping',
            'street' => 'Test Street 123',
            'city' => 'Vilnius',
            'postal_code' => 'LT-01234',
            'is_default' => true,
        ]);

        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'type' => 'shipping',
            'street' => 'Test Street 123',
            'city' => 'Vilnius',
            'postal_code' => 'LT-01234',
            'is_default' => true,
        ]);
    }

    public function test_address_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $address->user);
        $this->assertEquals($user->id, $address->user->id);
    }

    public function test_address_belongs_to_country(): void
    {
        $country = Country::factory()->create();
        $address = Address::factory()->create(['country_id' => $country->id]);

        $this->assertInstanceOf(Country::class, $address->country);
        $this->assertEquals($country->id, $address->country->id);
    }

    public function test_address_belongs_to_region(): void
    {
        $region = Region::factory()->create();
        $address = Address::factory()->create(['region_id' => $region->id]);

        $this->assertInstanceOf(Region::class, $address->region);
        $this->assertEquals($region->id, $address->region->id);
    }

    public function test_address_belongs_to_city(): void
    {
        $city = City::factory()->create();
        $address = Address::factory()->create(['city_id' => $city->id]);

        $this->assertInstanceOf(City::class, $address->city);
        $this->assertEquals($city->id, $address->city->id);
    }

    public function test_address_casts_work_correctly(): void
    {
        $address = Address::factory()->create([
            'is_default' => true,
            'is_active' => true,
            'created_at' => now(),
        ]);

        $this->assertIsBool($address->is_default);
        $this->assertIsBool($address->is_active);
        $this->assertInstanceOf(\Carbon\Carbon::class, $address->created_at);
    }

    public function test_address_fillable_attributes(): void
    {
        $address = new Address();
        $fillable = $address->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('type', $fillable);
        $this->assertContains('street', $fillable);
        $this->assertContains('city', $fillable);
        $this->assertContains('postal_code', $fillable);
        $this->assertContains('is_default', $fillable);
    }

    public function test_address_scope_shipping(): void
    {
        $shippingAddress = Address::factory()->create(['type' => 'shipping']);
        $billingAddress = Address::factory()->create(['type' => 'billing']);

        $shippingAddresses = Address::shipping()->get();

        $this->assertTrue($shippingAddresses->contains($shippingAddress));
        $this->assertFalse($shippingAddresses->contains($billingAddress));
    }

    public function test_address_scope_billing(): void
    {
        $shippingAddress = Address::factory()->create(['type' => 'shipping']);
        $billingAddress = Address::factory()->create(['type' => 'billing']);

        $billingAddresses = Address::billing()->get();

        $this->assertFalse($billingAddresses->contains($shippingAddress));
        $this->assertTrue($billingAddresses->contains($billingAddress));
    }

    public function test_address_scope_default(): void
    {
        $defaultAddress = Address::factory()->create(['is_default' => true]);
        $nonDefaultAddress = Address::factory()->create(['is_default' => false]);

        $defaultAddresses = Address::default()->get();

        $this->assertTrue($defaultAddresses->contains($defaultAddress));
        $this->assertFalse($defaultAddresses->contains($nonDefaultAddress));
    }

    public function test_address_scope_active(): void
    {
        $activeAddress = Address::factory()->create(['is_active' => true]);
        $inactiveAddress = Address::factory()->create(['is_active' => false]);

        $activeAddresses = Address::active()->get();

        $this->assertTrue($activeAddresses->contains($activeAddress));
        $this->assertFalse($activeAddresses->contains($inactiveAddress));
    }

    public function test_address_scope_for_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $address1 = Address::factory()->create(['user_id' => $user1->id]);
        $address2 = Address::factory()->create(['user_id' => $user2->id]);

        $user1Addresses = Address::forUser($user1->id)->get();

        $this->assertTrue($user1Addresses->contains($address1));
        $this->assertFalse($user1Addresses->contains($address2));
    }

    public function test_address_can_have_company_information(): void
    {
        $address = Address::factory()->create([
            'company_name' => 'Test Company',
            'company_vat' => 'LT123456789',
        ]);

        $this->assertEquals('Test Company', $address->company_name);
        $this->assertEquals('LT123456789', $address->company_vat);
    }

    public function test_address_can_have_contact_information(): void
    {
        $address = Address::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+37012345678',
            'email' => 'john.doe@example.com',
        ]);

        $this->assertEquals('John', $address->first_name);
        $this->assertEquals('Doe', $address->last_name);
        $this->assertEquals('+37012345678', $address->phone);
        $this->assertEquals('john.doe@example.com', $address->email);
    }

    public function test_address_can_have_additional_information(): void
    {
        $address = Address::factory()->create([
            'apartment' => 'Apt 5B',
            'floor' => '3rd Floor',
            'building' => 'Building A',
            'landmark' => 'Near the shopping center',
            'instructions' => 'Ring the doorbell twice',
        ]);

        $this->assertEquals('Apt 5B', $address->apartment);
        $this->assertEquals('3rd Floor', $address->floor);
        $this->assertEquals('Building A', $address->building);
        $this->assertEquals('Near the shopping center', $address->landmark);
        $this->assertEquals('Ring the doorbell twice', $address->instructions);
    }

    public function test_address_can_get_full_address(): void
    {
        $address = Address::factory()->create([
            'street' => 'Test Street 123',
            'apartment' => 'Apt 5B',
            'city' => 'Vilnius',
            'postal_code' => 'LT-01234',
        ]);

        $fullAddress = $address->getFullAddress();
        
        $this->assertStringContainsString('Test Street 123', $fullAddress);
        $this->assertStringContainsString('Apt 5B', $fullAddress);
        $this->assertStringContainsString('Vilnius', $fullAddress);
        $this->assertStringContainsString('LT-01234', $fullAddress);
    }

    public function test_address_can_get_full_name(): void
    {
        $address = Address::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $fullName = $address->getFullName();
        
        $this->assertEquals('John Doe', $fullName);
    }
}
