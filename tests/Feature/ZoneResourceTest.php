<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\ZoneResource;
use App\Models\Currency;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ZoneResourceTest extends TestCase
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

    public function test_zone_resource_can_render_list_page(): void
    {
        $currency = Currency::factory()->create([
            'name' => 'Euro',
            'code' => 'EUR',
            'symbol' => '€',
        ]);

        Zone::factory()->create([
            'name' => ['lt' => 'Lietuva', 'en' => 'Lithuania'],
            'slug' => 'lithuania',
            'code' => 'LT',
            'currency_id' => $currency->id,
            'is_enabled' => true,
            'is_default' => true,
            'tax_rate' => 21.0,
            'shipping_rate' => 5.0,
            'sort_order' => 1,
        ]);

        $this
            ->actingAs($this->adminUser)
            ->get(ZoneResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_zone_resource_can_render_create_page(): void
    {
        Currency::factory()->create([
            'name' => 'Euro',
            'code' => 'EUR',
            'symbol' => '€',
        ]);

        $this
            ->actingAs($this->adminUser)
            ->get(ZoneResource::getUrl('create'))
            ->assertSuccessful();
    }

    public function test_zone_resource_can_create_zone(): void
    {
        $currency = Currency::factory()->create([
            'name' => 'Euro',
            'code' => 'EUR',
            'symbol' => '€',
        ]);

        $zoneData = [
            'name.lt' => 'Lietuva',
            'name.en' => 'Lithuania',
            'slug' => 'lithuania',
            'code' => 'LT',
            'currency_id' => $currency->id,
            'is_enabled' => true,
            'is_default' => true,
            'tax_rate' => 21.0,
            'shipping_rate' => 5.0,
            'sort_order' => 1,
            'description.lt' => 'Lietuvos zona',
            'description.en' => 'Lithuania zone',
        ];

        $this
            ->actingAs($this->adminUser)
            ->post(ZoneResource::getUrl('create'), $zoneData)
            ->assertRedirect();

        $this->assertDatabaseHas('zones', [
            'slug' => 'lithuania',
            'code' => 'LT',
            'currency_id' => $currency->id,
            'is_enabled' => true,
            'is_default' => true,
        ]);
    }

    public function test_zone_resource_can_render_edit_page(): void
    {
        $currency = Currency::factory()->create([
            'name' => 'Euro',
            'code' => 'EUR',
            'symbol' => '€',
        ]);

        $zone = Zone::factory()->create([
            'name' => ['lt' => 'Lietuva', 'en' => 'Lithuania'],
            'slug' => 'lithuania',
            'code' => 'LT',
            'currency_id' => $currency->id,
        ]);

        $this
            ->actingAs($this->adminUser)
            ->get(ZoneResource::getUrl('edit', ['record' => $zone]))
            ->assertSuccessful();
    }

    public function test_zone_resource_can_update_zone(): void
    {
        $currency = Currency::factory()->create([
            'name' => 'Euro',
            'code' => 'EUR',
            'symbol' => '€',
        ]);

        $zone = Zone::factory()->create([
            'name' => ['lt' => 'Lietuva', 'en' => 'Lithuania'],
            'slug' => 'lithuania',
            'code' => 'LT',
            'currency_id' => $currency->id,
            'tax_rate' => 21.0,
        ]);

        $updatedData = [
            'name.lt' => 'Lietuva Updated',
            'name.en' => 'Lithuania Updated',
            'tax_rate' => 19.0,
        ];

        $this
            ->actingAs($this->adminUser)
            ->put(ZoneResource::getUrl('edit', ['record' => $zone]), $updatedData)
            ->assertRedirect();

        $zone->refresh();
        $this->assertEquals(19.0, $zone->tax_rate);
    }

    public function test_zone_resource_can_delete_zone(): void
    {
        $currency = Currency::factory()->create([
            'name' => 'Euro',
            'code' => 'EUR',
            'symbol' => '€',
        ]);

        $zone = Zone::factory()->create([
            'name' => ['lt' => 'Lietuva', 'en' => 'Lithuania'],
            'slug' => 'lithuania',
            'code' => 'LT',
            'currency_id' => $currency->id,
        ]);

        $this
            ->actingAs($this->adminUser)
            ->delete(ZoneResource::getUrl('edit', ['record' => $zone]))
            ->assertRedirect();

        $this->assertDatabaseMissing('zones', [
            'id' => $zone->id,
        ]);
    }
}
