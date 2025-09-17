<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ZoneManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles and permissions
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);

        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->adminUser->assignRole('super_admin');
    }

    public function test_zone_multilanguage_functionality(): void
    {
        $currency = Currency::factory()->create([
            'name' => 'Euro',
            'code' => 'EUR',
            'symbol' => '€',
        ]);

        $zone = Zone::create([
            'name' => ['lt' => 'Lietuva', 'en' => 'Lithuania'],
            'slug' => 'lithuania',
            'code' => 'LT',
            'currency_id' => $currency->id,
            'is_enabled' => true,
            'is_default' => true,
            'tax_rate' => 21.0,
            'shipping_rate' => 5.0,
            'sort_order' => 1,
            'description' => ['lt' => 'Lietuvos zona', 'en' => 'Lithuania zone'],
        ]);

        $this->assertDatabaseHas('zones', [
            'slug' => 'lithuania',
            'code' => 'LT',
            'currency_id' => $currency->id,
        ]);

        // Test multilanguage functionality
        app()->setLocale('lt');
        $this->assertEquals('Lietuva', $zone->name['lt']);

        app()->setLocale('en');
        $this->assertEquals('Lithuania', $zone->name['en']);
    }

    public function test_zone_tax_and_shipping_calculations(): void
    {
        $currency = Currency::factory()->create([
            'name' => 'Euro',
            'code' => 'EUR',
            'symbol' => '€',
        ]);

        $zone = Zone::create([
            'name' => ['lt' => 'Lietuva', 'en' => 'Lithuania'],
            'slug' => 'lithuania',
            'code' => 'LT',
            'currency_id' => $currency->id,
            'tax_rate' => 21.0,
            'shipping_rate' => 5.0,
        ]);

        // Test tax calculation
        $taxAmount = $zone->calculateTax(100.0);
        $this->assertEquals(21.0, $taxAmount);

        // Test shipping calculation
        $shippingAmount = $zone->calculateShipping(2.0);
        $this->assertEquals(10.0, $shippingAmount);  // 5.0 * 2.0
    }

    public function test_zone_scopes(): void
    {
        $currency = Currency::factory()->create([
            'name' => 'Euro',
            'code' => 'EUR',
            'symbol' => '€',
        ]);

        Zone::create([
            'name' => ['lt' => 'Lietuva', 'en' => 'Lithuania'],
            'slug' => 'lithuania',
            'code' => 'LT',
            'currency_id' => $currency->id,
            'is_enabled' => true,
            'is_default' => true,
        ]);

        Zone::create([
            'name' => ['lt' => 'Latvija', 'en' => 'Latvia'],
            'slug' => 'latvia',
            'code' => 'LV',
            'currency_id' => $currency->id,
            'is_enabled' => false,
            'is_default' => false,
        ]);

        // Test enabled scope
        $enabledZones = Zone::enabled()->count();
        $this->assertEquals(1, $enabledZones);

        // Test default scope
        $defaultZones = Zone::default()->count();
        $this->assertEquals(1, $defaultZones);
    }
}
