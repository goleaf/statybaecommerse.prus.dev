<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CityResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);
    }

    public function test_can_view_cities_index(): void
    {
        City::factory()->count(3)->create();

        $response = $this
            ->actingAs($this->adminUser)
            ->get(route('filament.admin.resources.cities.index'));

        $response->assertOk();
    }

    public function test_can_create_city(): void
    {
        $country = Country::factory()->create();

        $cityData = [
            'name' => 'Test City',
            'code' => 'TC',
            'description' => 'Test description',
            'country_id' => $country->id,
            'is_active' => true,
            'is_capital' => false,
            'population' => 100000,
            'sort_order' => 1,
        ];

        $response = $this
            ->actingAs($this->adminUser)
            ->post(route('filament.admin.resources.cities.create'), $cityData);

        $this->assertDatabaseHas('cities', [
            'name' => 'Test City',
            'code' => 'TC',
            'country_id' => $country->id,
        ]);
    }

    public function test_can_update_city(): void
    {
        $city = City::factory()->create(['name' => 'Old Name']);
        $country = Country::factory()->create();

        $updateData = [
            'name' => 'Updated City',
            'country_id' => $country->id,
            'is_capital' => true,
        ];

        $response = $this
            ->actingAs($this->adminUser)
            ->put(route('filament.admin.resources.cities.update', ['record' => $city->id]), $updateData);

        $this->assertDatabaseHas('cities', [
            'id' => $city->id,
            'name' => 'Updated City',
            'country_id' => $country->id,
            'is_capital' => true,
        ]);
    }

    public function test_can_delete_city(): void
    {
        $city = City::factory()->create();

        $response = $this
            ->actingAs($this->adminUser)
            ->delete(route('filament.admin.resources.cities.destroy', ['record' => $city->id]));

        $this->assertSoftDeleted('cities', ['id' => $city->id]);
    }

    public function test_can_view_city(): void
    {
        $city = City::factory()->create();

        $response = $this
            ->actingAs($this->adminUser)
            ->get(route('filament.admin.resources.cities.view', ['record' => $city->id]));

        $response->assertOk();
    }
}
