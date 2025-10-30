<?php

declare(strict_types=1);

namespace Tests\Admin;

use App\Filament\Resources\CustomerResource\Pages\CreateCustomer;
use App\Filament\Resources\CustomerResource\Pages\EditCustomer;
use App\Filament\Resources\CustomerResource\Pages\ListCustomers;
use App\Models\City;
use App\Models\Company;
use App\Models\Country;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

final class CustomerResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure the Filament admin panel and routes are registered before exercising Livewire components.
        $this->resolveAdminPanel();

        // Seed a deterministic admin user so policies and audit columns resolve consistently across test runs.
        $this->admin = User::factory()->admin()->create([
            'email' => 'admin@example.test',
        ]);

        // Grant the explicit customer permissions required by the policy layer to interact with the resource during tests.
        $this->grantCustomerPermissions($this->admin);

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_customers_and_supports_filters(): void
    {
        $country = Country::factory()->create(['name' => 'Lithuania']);
        $city = City::factory()->forCountry($country)->create([
            'name' => 'Vilnius',
        ]);
        $company = Company::factory()->create([
            'name'      => 'Acme Builders',
            'is_active' => true,
        ]);

        $otherCountry = Country::factory()->create(['name' => 'Latvia']);
        $otherCity = City::factory()->forCountry($otherCountry)->create([
            'name' => 'Riga',
        ]);
        $otherCompany = Company::factory()->create([
            'name'      => 'Baltic Logistics',
            'is_active' => true,
        ]);

        $primaryCustomer = Customer::factory()->create([
            'name'       => 'Active Customer',
            'email'      => 'active@example.test',
            'phone'      => '+37060000001',
            'country_id' => $country->id,
            'city_id'    => $city->id,
            'company_id' => $company->id,
        ]);

        $secondaryCustomer = Customer::factory()->create([
            'name'       => 'Second Customer',
            'email'      => 'second@example.test',
            'phone'      => '+37160000002',
            'country_id' => $otherCountry->id,
            'city_id'    => $otherCity->id,
            'company_id' => $otherCompany->id,
        ]);

        Livewire::actingAs($this->admin)
            ->test(ListCustomers::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$primaryCustomer, $secondaryCustomer])
            ->filterTable('country_id', $country->id)
            ->assertCanSeeTableRecords([$primaryCustomer])
            ->assertCanNotSeeTableRecords([$secondaryCustomer])
            ->filterTable('city_id', $city->id)
            ->assertCanSeeTableRecords([$primaryCustomer])
            ->filterTable('company_id', $company->id)
            ->assertCanSeeTableRecords([$primaryCustomer]);
    }

    /**
     * Ensure the inline chart column is present on the customer listing.
     */
    public function test_list_page_includes_orders_sparkline_column(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ListCustomers::class)
            ->call('loadTable')
            ->assertTableColumnExists('orders_sparkline');
    }

    public function test_can_create_customer_via_filament_form(): void
    {
        $country = Country::factory()->create(['name' => 'Lithuania']);
        $city = City::factory()->forCountry($country)->create([
            'name' => 'Kaunas',
        ]);
        $company = Company::factory()->create([
            'name'      => 'Statyba UAB',
            'is_active' => true,
        ]);

        Livewire::actingAs($this->admin)
            ->test(CreateCustomer::class)
            ->fillForm([
                'name'        => 'Jane Doe',
                'email'       => 'jane@example.test',
                'phone'       => '+37060000003',
                'address'     => 'Konstitucijos pr. 1',
                'country_id'  => $country->id,
                'city_id'     => $city->id,
                'postal_code' => 'LT-01103',
                'company_id'  => $company->id,
                'description' => 'Test customer profile',
                'is_active'   => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('customers', [
            'email'      => 'jane@example.test',
            'name'       => 'Jane Doe',
            'country_id' => $country->id,
            'city_id'    => $city->id,
            'company_id' => $company->id,
            'is_active'  => true,
        ]);
    }

    public function test_can_edit_customer_via_filament_form(): void
    {
        $country = Country::factory()->create(['name' => 'Lithuania']);
        $initialCity = City::factory()->forCountry($country)->create([
            'name' => 'Vilnius',
        ]);
        $initialCompany = Company::factory()->create([
            'name'      => 'Original Co',
            'is_active' => true,
        ]);

        $customer = Customer::factory()->create([
            'name'       => 'Initial Customer',
            'email'      => 'initial@example.test',
            'phone'      => '+37060000004',
            'country_id' => $country->id,
            'city_id'    => $initialCity->id,
            'company_id' => $initialCompany->id,
            'is_active'  => true,
        ]);

        $updatedCity = City::factory()->forCountry($country)->create([
            'name' => 'Kaunas',
        ]);
        $updatedCompany = Company::factory()->create([
            'name'      => 'Updated Co',
            'is_active' => true,
        ]);

        Livewire::actingAs($this->admin)
            ->test(EditCustomer::class, ['record' => $customer->getKey()])
            ->fillForm([
                'name'       => 'Updated Customer',
                'email'      => 'updated@example.test',
                'phone'      => '+37060000005',
                'city_id'    => $updatedCity->id,
                'company_id' => $updatedCompany->id,
                'is_active'  => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('customers', [
            'id'         => $customer->id,
            'name'       => 'Updated Customer',
            'email'      => 'updated@example.test',
            'city_id'    => $updatedCity->id,
            'company_id' => $updatedCompany->id,
            'is_active'  => false,
        ]);
    }

    public function test_can_toggle_active_status_from_table_action(): void
    {
        $country = Country::factory()->create();
        $city = City::factory()->forCountry($country)->create([
            'name' => 'Klaipeda',
        ]);
        $company = Company::factory()->create([
            'is_active' => true,
        ]);

        $customer = Customer::factory()->create([
            'country_id' => $country->id,
            'city_id'    => $city->id,
            'company_id' => $company->id,
            'is_active'  => true,
        ]);

        Livewire::actingAs($this->admin)
            ->test(ListCustomers::class)
            ->call('loadTable')
            ->callTableAction('toggle_active', $customer)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('customers', [
            'id'        => $customer->id,
            'is_active' => false,
        ]);
    }

    public function test_can_bulk_activate_and_deactivate_customers(): void
    {
        $country = Country::factory()->create();
        $city = City::factory()->forCountry($country)->create([
            'name' => 'Panevezys',
        ]);
        $company = Company::factory()->create([
            'is_active' => true,
        ]);

        $activeCustomers = Customer::factory()->count(2)->create([
            'country_id' => $country->id,
            'city_id'    => $city->id,
            'company_id' => $company->id,
            'is_active'  => true,
        ]);

        $inactiveCustomers = Customer::factory()->count(2)->create([
            'country_id' => $country->id,
            'city_id'    => $city->id,
            'company_id' => $company->id,
            'is_active'  => false,
        ]);

        $component = Livewire::actingAs($this->admin)->test(ListCustomers::class);

        $component
            ->call('loadTable')
            ->callTableBulkAction('deactivate', $activeCustomers)
            ->assertHasNoTableBulkActionErrors();

        foreach ($activeCustomers as $customer) {
            $this->assertDatabaseHas('customers', [
                'id'        => $customer->id,
                'is_active' => false,
            ]);
        }

        $component
            ->callTableBulkAction('activate', $inactiveCustomers)
            ->assertHasNoTableBulkActionErrors();

        foreach ($inactiveCustomers as $customer) {
            $this->assertDatabaseHas('customers', [
                'id'        => $customer->id,
                'is_active' => true,
            ]);
        }
    }

    /**
     * Provision the set of permissions expected by the CustomerPolicy during Filament interactions.
     */
    private function grantCustomerPermissions(User $user): void
    {
        $permissions = [
            'view_customers',
            'create_customers',
            'edit_customers',
            'delete_customers',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $user->syncPermissions($permissions);
    }
}
