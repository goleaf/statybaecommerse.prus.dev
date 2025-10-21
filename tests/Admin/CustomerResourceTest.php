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
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

final class CustomerResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureTestTables();

        $this->resolveAdminPanel();

        $this->admin = User::factory()->admin()->create([
            'email' => 'admin@example.test',
        ]);

        $this->actingAs($this->admin);
    }

    private function ensureTestTables(): void
    {
        Schema::disableForeignKeyConstraints();

        if (! Schema::hasTable('countries')) {
            Schema::create('countries', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('name_official')->nullable();
                $table->string('description')->nullable();
                $table->string('cca2', 2)->nullable();
                $table->string('cca3', 3)->nullable();
                $table->string('ccn3', 3)->nullable();
                $table->string('code')->nullable();
                $table->string('iso_code')->nullable();
                $table->string('currency_code', 3)->nullable();
                $table->string('currency_symbol', 5)->nullable();
                $table->string('phone_code', 10)->nullable();
                $table->string('phone_calling_code', 10)->nullable();
                $table->string('flag')->nullable();
                $table->string('svg_flag')->nullable();
                $table->string('region')->nullable();
                $table->string('subregion')->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->json('currencies')->nullable();
                $table->json('languages')->nullable();
                $table->json('timezones')->nullable();
                $table->string('timezone')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_enabled')->default(true);
                $table->boolean('is_eu_member')->default(false);
                $table->boolean('requires_vat')->default(false);
                $table->decimal('vat_rate', 5, 2)->nullable();
                $table->json('metadata')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('companies')) {
            Schema::create('companies', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('address')->nullable();
                $table->string('website')->nullable();
                $table->string('industry')->nullable();
                $table->string('size')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('cities')) {
            Schema::create('cities', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('code')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_enabled')->default(true);
                $table->boolean('is_default')->default(false);
                $table->boolean('is_capital')->default(false);
                $table->unsignedBigInteger('country_id')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedSmallInteger('level')->default(0);
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->unsignedBigInteger('population')->nullable();
                $table->json('postal_codes')->nullable();
                $table->json('metadata')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('phone')->nullable();
                $table->string('address')->nullable();
                $table->unsignedBigInteger('country_id')->nullable();
                $table->unsignedBigInteger('city_id')->nullable();
                $table->string('postal_code')->nullable();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->boolean('is_active')->default(true);
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->string('status')->default('pending');
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            Schema::table('orders', function (Blueprint $table): void {
                if (! Schema::hasColumn('orders', 'customer_id')) {
                    $table->unsignedBigInteger('customer_id')->nullable()->after('user_id');
                }

                if (! Schema::hasColumn('orders', 'status')) {
                    $table->string('status')->default('pending');
                }
            });
        }

        Schema::enableForeignKeyConstraints();
    }

    public function test_list_page_displays_customers_and_supports_filters(): void
    {
        $country = Country::factory()->create(['name' => 'Lithuania']);
        $city = $this->createCity($country, 'Vilnius');
        $company = Company::factory()->create([
            'name'      => 'Acme Builders',
            'is_active' => true,
        ]);

        $otherCountry = Country::factory()->create(['name' => 'Latvia']);
        $otherCity = $this->createCity($otherCountry, 'Riga');
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
        $city = $this->createCity($country, 'Kaunas');
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
        $initialCity = $this->createCity($country, 'Vilnius');
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

        $updatedCity = $this->createCity($country, 'Kaunas');
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
        $city = $this->createCity($country, 'Klaipeda');
        $company = Company::factory()->create(['is_active' => true]);

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
        $city = $this->createCity($country, 'Panevezys');
        $company = Company::factory()->create(['is_active' => true]);

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
            ->callTableBulkAction('deactivate', $activeCustomers->pluck('id')->all())
            ->assertHasNoTableBulkActionErrors();

        foreach ($activeCustomers as $customer) {
            $this->assertDatabaseHas('customers', [
                'id'        => $customer->id,
                'is_active' => false,
            ]);
        }

        $component
            ->callTableBulkAction('activate', $inactiveCustomers->pluck('id')->all())
            ->assertHasNoTableBulkActionErrors();

        foreach ($inactiveCustomers as $customer) {
            $this->assertDatabaseHas('customers', [
                'id'        => $customer->id,
                'is_active' => true,
            ]);
        }
    }

    private function createCity(Country $country, string $name): City
    {
        return City::query()->create([
            'name'         => $name,
            'slug'         => Str::slug($name . '-' . Str::random(5)),
            'code'         => strtoupper(Str::random(2)) . '-' . Str::random(3),
            'is_enabled'   => true,
            'is_default'   => false,
            'is_capital'   => false,
            'country_id'   => $country->id,
            'parent_id'    => null,
            'level'        => 0,
            'latitude'     => null,
            'longitude'    => null,
            'population'   => null,
            'postal_codes' => ['00000'],
            'sort_order'   => 0,
            'metadata'     => [],
            'is_active'    => true,
        ]);
    }
}
