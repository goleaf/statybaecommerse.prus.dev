<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\City;
use App\Models\Company;
use App\Models\Country;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CustomerResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Boot the Filament admin panel for HTTP route + navigation resolution.
        $this->resolveAdminPanel();

        // Minimal authenticated user – create directly to avoid schema drift in older SQLite snapshots.
        $email = 'admin-' . uniqid('', true) . '@example.test';
        $columns = [
            'name'              => 'Admin User',
            'email'             => $email,
            'email_verified_at' => now(),
            'password'          => bcrypt('password'),
            'preferred_locale'  => 'en',
            'is_admin'          => true,
            'remember_token'    => str()->random(10),
            'created_at'        => now(),
            'updated_at'        => now(),
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'is_active')) {
            $columns['is_active'] = true;
        }

        \Illuminate\Support\Facades\DB::table('users')->insert($columns);
        $this->admin = User::query()->where('email', $email)->firstOrFail();
        $this->actingAs($this->admin);

        // Grant explicit permissions expected by the CustomerPolicy.
        \Spatie\Permission\Models\Permission::findOrCreate('view_customers');
        \Spatie\Permission\Models\Permission::findOrCreate('create_customers');
        \Spatie\Permission\Models\Permission::findOrCreate('edit_customers');
        \Spatie\Permission\Models\Permission::findOrCreate('delete_customers');
        $this->admin->givePermissionTo([
            'view_customers',
            'create_customers',
            'edit_customers',
            'delete_customers',
        ]);
    }

    public function test_index_page_loads(): void
    {
        $this->get('/admin/customers')->assertOk();
    }

    public function test_create_page_renders_without_type_errors(): void
    {
        $response = $this->get('/admin/customers/create');

        $response
            ->assertOk()
            ->assertDontSee('TypeError')
            ->assertDontSee('must not be accessed before initialization')
            ->assertDontSee('Dynamic property')
            ->assertDontSee('Undefined property');
    }

    public function test_can_store_customer_via_http(): void
    {
        $country = Country::factory()->create(['name' => 'Lithuania']);
        $city = City::factory()->create([
            'name'       => 'Vilnius',
            'country_id' => $country->id,
        ]);
        $company = Company::factory()->create(['name' => 'Acme Builders']);

        $payload = [
            'name'        => 'John Doe',
            'email'       => 'john@example.test',
            'phone'       => '+37060000001',
            'address'     => 'Konstitucijos pr. 1',
            'country_id'  => $country->id,
            'city_id'     => $city->id,
            'postal_code' => 'LT-01103',
            'company_id'  => $company->id,
            'description' => 'Test customer profile',
            'is_active'   => true,
        ];

        $this->post('/admin/customers', $payload)->assertStatus(302);

        $this->assertDatabaseHas('customers', [
            'email'      => 'john@example.test',
            'name'       => 'John Doe',
            'country_id' => $country->id,
            'city_id'    => $city->id,
            'company_id' => $company->id,
            'is_active'  => true,
        ]);
    }

    public function test_edit_page_renders_without_type_errors(): void
    {
        $country = Country::factory()->create(['name' => 'Lithuania']);
        $city = City::factory()->create([
            'name'       => 'Kaunas',
            'country_id' => $country->id,
        ]);
        $company = Company::factory()->create(['name' => 'Statyba UAB']);

        $customer = Customer::factory()->create([
            'country_id' => $country->id,
            'city_id'    => $city->id,
            'company_id' => $company->id,
        ]);

        $response = $this->get('/admin/customers/' . $customer->getKey() . '/edit');

        $response
            ->assertOk()
            ->assertDontSee('TypeError')
            ->assertDontSee('must not be accessed before initialization')
            ->assertDontSee('Dynamic property')
            ->assertDontSee('Undefined property');
    }

    public function test_can_update_customer_via_http(): void
    {
        $country = Country::factory()->create(['name' => 'Lithuania']);
        $city1 = City::factory()->create(['name' => 'Vilnius', 'country_id' => $country->id]);
        $city2 = City::factory()->create(['name' => 'Kaunas', 'country_id' => $country->id]);
        $company1 = Company::factory()->create(['name' => 'Original Co']);
        $company2 = Company::factory()->create(['name' => 'Updated Co']);

        $customer = Customer::factory()->create([
            'name'       => 'Initial',
            'email'      => 'initial@example.test',
            'country_id' => $country->id,
            'city_id'    => $city1->id,
            'company_id' => $company1->id,
            'is_active'  => true,
        ]);

        $update = [
            'name'       => 'Updated Name',
            'email'      => 'updated@example.test',
            'phone'      => '+37060000005',
            'city_id'    => $city2->id,
            'company_id' => $company2->id,
            'is_active'  => false,
        ];

        $this->put('/admin/customers/' . $customer->getKey(), $update)->assertStatus(302);

        $this->assertDatabaseHas('customers', [
            'id'         => $customer->id,
            'name'       => 'Updated Name',
            'email'      => 'updated@example.test',
            'city_id'    => $city2->id,
            'company_id' => $company2->id,
            'is_active'  => false,
        ]);
    }

    public function test_can_delete_customer_via_http(): void
    {
        $customer = Customer::factory()->create();

        $this->delete('/admin/customers/' . $customer->getKey())->assertStatus(302);

        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }
}
