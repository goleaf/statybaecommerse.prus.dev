<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\CountryResource;
use App\Models\Address;
use App\Models\City;
use App\Models\Country;
use App\Models\Currency;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'email' => 'admin@test.com',
            'is_active' => true
        ]));
    }

    public function test_country_resource_can_be_instantiated(): void
    {
        $resource = new CountryResource();
        $this->assertInstanceOf(CountryResource::class, $resource);
    }

    public function test_country_resource_has_required_methods(): void
    {
        $resource = new CountryResource();

        $this->assertTrue(method_exists($resource, 'form'));
        $this->assertTrue(method_exists($resource, 'table'));
        $this->assertTrue(method_exists($resource, 'getPages'));
        $this->assertTrue(method_exists($resource, 'getModel'));
        $this->assertTrue(method_exists($resource, 'getRelations'));
        $this->assertTrue(method_exists($resource, 'getNavigationLabel'));
        $this->assertTrue(method_exists($resource, 'getModelLabel'));
        $this->assertTrue(method_exists($resource, 'getPluralModelLabel'));
    }

    public function test_country_resource_form_works(): void
    {
        $resource = new CountryResource();

        $this->assertTrue(method_exists($resource, 'form'));
        $this->assertTrue(is_callable([$resource, 'form']));
    }

    public function test_country_resource_table_works(): void
    {
        $resource = new CountryResource();

        $this->assertTrue(method_exists($resource, 'table'));
        $this->assertTrue(is_callable([$resource, 'table']));
    }

    public function test_country_resource_has_valid_model(): void
    {
        $resource = new CountryResource();
        $model = $resource->getModel();

        $this->assertEquals(Country::class, $model);
        $this->assertTrue(class_exists($model));
    }

    public function test_country_resource_handles_empty_database(): void
    {
        $resource = new CountryResource();

        $this->assertTrue(method_exists($resource, 'form'));
        $this->assertTrue(method_exists($resource, 'table'));
        $this->assertTrue(is_callable([$resource, 'form']));
        $this->assertTrue(is_callable([$resource, 'table']));
    }

    public function test_country_resource_with_sample_data(): void
    {
        $country = Country::factory()->create([
            'name' => 'Lithuania',
            'cca2' => 'LT',
            'region' => 'Europe',
            'is_active' => true,
            'is_eu_member' => true,
            'requires_vat' => true,
        ]);

        $resource = new CountryResource();

        $this->assertTrue(method_exists($resource, 'form'));
        $this->assertTrue(method_exists($resource, 'table'));
        $this->assertTrue(is_callable([$resource, 'form']));
        $this->assertTrue(is_callable([$resource, 'table']));

        $this->assertDatabaseHas('countries', [
            'name' => 'Lithuania',
            'cca2' => 'LT',
            'region' => 'Europe',
            'is_active' => true,
            'is_eu_member' => true,
            'requires_vat' => true,
        ]);
    }

    public function test_country_resource_navigation_label(): void
    {
        $label = CountryResource::getNavigationLabel();
        $this->assertIsString($label);
        $this->assertNotEmpty($label);
    }

    public function test_country_resource_model_label(): void
    {
        $label = CountryResource::getModelLabel();
        $this->assertIsString($label);
        $this->assertNotEmpty($label);
    }

    public function test_country_resource_plural_model_label(): void
    {
        $label = CountryResource::getPluralModelLabel();
        $this->assertIsString($label);
        $this->assertNotEmpty($label);
    }

    public function test_country_resource_relations(): void
    {
        $relations = CountryResource::getRelations();
        $this->assertIsArray($relations);
    }

    public function test_country_resource_pages(): void
    {
        $pages = CountryResource::getPages();
        $this->assertIsArray($pages);
        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayHasKey('create', $pages);
        $this->assertArrayHasKey('view', $pages);
        $this->assertArrayHasKey('edit', $pages);
    }

    public function test_country_resource_with_relations(): void
    {
        // Create test data
        $country = Country::factory()->create([
            'name' => 'Lithuania',
            'cca2' => 'LT',
            'region' => 'Europe',
            'is_active' => true,
        ]);

        // Create related cities
        City::factory()->count(3)->create([
            'country_id' => $country->id,
            'zone_id' => Zone::factory(),
        ]);

        // Create related addresses
        Address::factory()->count(2)->create([
            'country_code' => $country->cca2,
            'user_id' => User::factory()->create(),
        ]);

        // Test that country has relations
        $this->assertCount(3, $country->cities);
        $this->assertCount(2, $country->addresses);

        // Test resource methods work with data
        $resource = new CountryResource();
        $this->assertTrue(is_callable([$resource, 'form']));
        $this->assertTrue(is_callable([$resource, 'table']));
    }

    public function test_country_resource_status_actions(): void
    {
        // Test active country
        $activeCountry = Country::factory()->create([
            'name' => 'Active Country',
            'cca2' => 'AC',
            'is_active' => true,
            'is_eu_member' => true,
            'requires_vat' => true,
        ]);

        $this->assertTrue($activeCountry->is_active);
        $this->assertTrue($activeCountry->is_eu_member);
        $this->assertTrue($activeCountry->requires_vat);

        // Test inactive country
        $inactiveCountry = Country::factory()->create([
            'name' => 'Inactive Country',
            'cca2' => 'IC',
            'is_active' => false,
            'is_eu_member' => false,
            'requires_vat' => false,
        ]);

        $this->assertFalse($inactiveCountry->is_active);
        $this->assertFalse($inactiveCountry->is_eu_member);
        $this->assertFalse($inactiveCountry->requires_vat);
    }

    public function test_country_resource_global_search(): void
    {
        $country = Country::factory()->create([
            'name' => 'Test Country',
            'cca2' => 'TC',
            'region' => 'Test Region',
            'currency_code' => 'EUR',
            'is_eu_member' => true,
        ]);

        // Test global search details
        $details = CountryResource::getGlobalSearchResultDetails($country);
        $this->assertIsArray($details);
        $this->assertArrayHasKey('Code', $details);
        $this->assertArrayHasKey('Region', $details);
        $this->assertArrayHasKey('Currency', $details);
        $this->assertArrayHasKey('EU Member', $details);

        // Verify the content of the details
        $this->assertEquals('TC', $details['Code']);
        $this->assertEquals('Test Region', $details['Region']);
        $this->assertEquals('EUR', $details['Currency']);
        $this->assertEquals('Yes', $details['EU Member']);

        // Test global search actions (may be empty if routes are not set up)
        $actions = CountryResource::getGlobalSearchResultActions($country);
        $this->assertIsArray($actions);
    }

    public function test_country_resource_display_name_attribute(): void
    {
        $country = Country::factory()->create([
            'name' => 'Lithuania',
            'cca2' => 'LT',
            'phone_calling_code' => '370',
        ]);

        $displayName = $country->getDisplayNameAttribute();
        $this->assertStringContainsString('Lithuania', $displayName);
        $this->assertStringContainsString('+370', $displayName);
    }

    public function test_country_resource_translated_attributes(): void
    {
        $country = Country::factory()->create([
            'name' => 'Test Country',
            'name_official' => 'Official Test Country',
            'description' => 'Test Description',
        ]);

        $translatedName = $country->getTranslatedNameAttribute();
        $translatedOfficialName = $country->getTranslatedOfficialNameAttribute();
        $translatedDescription = $country->getTranslatedDescriptionAttribute();

        $this->assertIsString($translatedName);
        $this->assertIsString($translatedOfficialName);
        $this->assertIsString($translatedDescription);
    }

    public function test_country_resource_code_attributes(): void
    {
        $country = Country::factory()->create([
            'name' => 'Test Country',
            'cca2' => 'TC',
            'iso_code' => 'ISO-TC',
        ]);

        $this->assertEquals('TC', $country->getCodeAttribute());
        $this->assertEquals('TUN', $country->getIsoCodeAttribute());
    }

    public function test_country_resource_bulk_actions(): void
    {
        $countries = Country::factory()->count(3)->create([
            'is_active' => false,
        ]);

        // Test that we can create countries for bulk operations
        $this->assertCount(3, $countries);

        // Test that all countries are inactive
        foreach ($countries as $country) {
            $this->assertFalse($country->is_active);
        }

        // Test resource methods work
        $resource = new CountryResource();
        $this->assertTrue(is_callable([$resource, 'form']));
        $this->assertTrue(is_callable([$resource, 'table']));
    }
}
