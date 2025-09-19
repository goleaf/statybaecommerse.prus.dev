<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\CustomerManagementResource\Pages\CreateCustomer;
use App\Filament\Resources\CustomerManagementResource\Pages\EditCustomer;
use App\Filament\Resources\CustomerManagementResource\Pages\ListCustomers;
use App\Filament\Resources\CustomerManagementResource\Pages\ViewCustomer;
use App\Filament\Resources\CustomerManagementResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * CustomerManagementResource Test
 *
 * Comprehensive test suite for CustomerManagementResource functionality
 */
class CustomerManagementResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_customers(): void
    {
        $customers = User::factory()->count(5)->create();

        Livewire::test(ListCustomers::class)
            ->assertCanSeeTableRecords($customers);
    }

    public function test_can_create_customer(): void
    {
        $newCustomerData = User::factory()->make();

        Livewire::test(CreateCustomer::class)
            ->fillForm([
                'name' => $newCustomerData->name,
                'email' => $newCustomerData->email,
                'phone' => $newCustomerData->phone,
                'is_active' => true,
                'is_verified' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas('users', [
            'name' => $newCustomerData->name,
            'email' => $newCustomerData->email,
        ]);
    }

    public function test_can_edit_customer(): void
    {
        $customer = User::factory()->create();
        $updatedData = User::factory()->make();

        Livewire::test(EditCustomer::class, ['record' => $customer->id])
            ->fillForm([
                'name' => $updatedData->name,
                'email' => $updatedData->email,
                'phone' => $updatedData->phone,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'name' => $updatedData->name,
            'email' => $updatedData->email,
        ]);
    }

    public function test_can_view_customer(): void
    {
        $customer = User::factory()->create();

        Livewire::test(ViewCustomer::class, ['record' => $customer->id])
            ->assertOk();
    }

    public function test_can_delete_customer(): void
    {
        $customer = User::factory()->create();

        Livewire::test(EditCustomer::class, ['record' => $customer->id])
            ->callAction('delete')
            ->assertNotified();

        $this->assertSoftDeleted('users', [
            'id' => $customer->id,
        ]);
    }

    public function test_can_verify_email(): void
    {
        $customer = User::factory()->create([
            'email_verified_at' => null,
        ]);

        Livewire::test(ListCustomers::class)
            ->callTableAction('verify_email', $customer)
            ->assertNotified();

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'email_verified_at' => now(),
        ]);
    }

    public function test_can_toggle_active_status(): void
    {
        $customer = User::factory()->create(['is_active' => false]);

        Livewire::test(ListCustomers::class)
            ->callTableAction('toggle_active', $customer)
            ->assertNotified();

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'is_active' => true,
        ]);
    }

    public function test_can_bulk_verify_emails(): void
    {
        $customers = User::factory()->count(3)->create([
            'email_verified_at' => null,
        ]);

        Livewire::test(ListCustomers::class)
            ->callTableBulkAction('verify_emails', $customers)
            ->assertNotified();

        foreach ($customers as $customer) {
            $this->assertDatabaseHas('users', [
                'id' => $customer->id,
                'email_verified_at' => now(),
            ]);
        }
    }

    public function test_can_bulk_activate_customers(): void
    {
        $customers = User::factory()->count(3)->create(['is_active' => false]);

        Livewire::test(ListCustomers::class)
            ->callTableBulkAction('activate', $customers)
            ->assertNotified();

        foreach ($customers as $customer) {
            $this->assertDatabaseHas('users', [
                'id' => $customer->id,
                'is_active' => true,
            ]);
        }
    }

    public function test_can_bulk_deactivate_customers(): void
    {
        $customers = User::factory()->count(3)->create(['is_active' => true]);

        Livewire::test(ListCustomers::class)
            ->callTableBulkAction('deactivate', $customers)
            ->assertNotified();

        foreach ($customers as $customer) {
            $this->assertDatabaseHas('users', [
                'id' => $customer->id,
                'is_active' => false,
            ]);
        }
    }

    public function test_can_filter_by_customer_group(): void
    {
        $customerGroup = \App\Models\CustomerGroup::factory()->create();
        $customers = User::factory()->count(3)->create([
            'customer_group_id' => $customerGroup->id,
        ]);
        $otherCustomers = User::factory()->count(2)->create();

        Livewire::test(ListCustomers::class)
            ->filterTable('customer_group_id', $customerGroup->id)
            ->assertCanSeeTableRecords($customers)
            ->assertCanNotSeeTableRecords($otherCustomers);
    }

    public function test_can_filter_by_email_verification_status(): void
    {
        $verifiedCustomers = User::factory()->count(2)->create([
            'email_verified_at' => now(),
        ]);
        $unverifiedCustomers = User::factory()->count(3)->create([
            'email_verified_at' => null,
        ]);

        Livewire::test(ListCustomers::class)
            ->filterTable('email_verified_at', true)
            ->assertCanSeeTableRecords($verifiedCustomers)
            ->assertCanNotSeeTableRecords($unverifiedCustomers);
    }

    public function test_can_filter_by_active_status(): void
    {
        $activeCustomers = User::factory()->count(2)->create(['is_active' => true]);
        $inactiveCustomers = User::factory()->count(3)->create(['is_active' => false]);

        Livewire::test(ListCustomers::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords($activeCustomers)
            ->assertCanNotSeeTableRecords($inactiveCustomers);
    }

    public function test_can_filter_by_creation_date(): void
    {
        $recentCustomers = User::factory()->count(2)->create([
            'created_at' => now()->subDays(5),
        ]);
        $oldCustomers = User::factory()->count(3)->create([
            'created_at' => now()->subDays(30),
        ]);

        Livewire::test(ListCustomers::class)
            ->filterTable('created_at', [
                'created_from' => now()->subDays(10)->format('Y-m-d'),
            ])
            ->assertCanSeeTableRecords($recentCustomers)
            ->assertCanNotSeeTableRecords($oldCustomers);
    }

    public function test_can_search_customers_by_name(): void
    {
        $customer1 = User::factory()->create(['name' => 'John Doe']);
        $customer2 = User::factory()->create(['name' => 'Jane Smith']);
        $customer3 = User::factory()->create(['name' => 'Bob Johnson']);

        Livewire::test(ListCustomers::class)
            ->searchTable('John')
            ->assertCanSeeTableRecords([$customer1])
            ->assertCanNotSeeTableRecords([$customer2, $customer3]);
    }

    public function test_can_search_customers_by_email(): void
    {
        $customer1 = User::factory()->create(['email' => 'john@example.com']);
        $customer2 = User::factory()->create(['email' => 'jane@example.com']);
        $customer3 = User::factory()->create(['email' => 'bob@example.com']);

        Livewire::test(ListCustomers::class)
            ->searchTable('john@example.com')
            ->assertCanSeeTableRecords([$customer1])
            ->assertCanNotSeeTableRecords([$customer2, $customer3]);
    }

    public function test_can_sort_customers_by_name(): void
    {
        $customer1 = User::factory()->create(['name' => 'Charlie']);
        $customer2 = User::factory()->create(['name' => 'Alice']);
        $customer3 = User::factory()->create(['name' => 'Bob']);

        Livewire::test(ListCustomers::class)
            ->sortTable('name')
            ->assertCanSeeTableRecordsInOrder([$customer2, $customer3, $customer1]);
    }

    public function test_can_sort_customers_by_created_at(): void
    {
        $customer1 = User::factory()->create(['created_at' => now()->subDays(3)]);
        $customer2 = User::factory()->create(['created_at' => now()->subDays(1)]);
        $customer3 = User::factory()->create(['created_at' => now()->subDays(2)]);

        Livewire::test(ListCustomers::class)
            ->sortTable('created_at', 'desc')
            ->assertCanSeeTableRecordsInOrder([$customer2, $customer3, $customer1]);
    }

    public function test_form_validation_requires_name(): void
    {
        Livewire::test(CreateCustomer::class)
            ->fillForm([
                'name' => '',
                'email' => 'test@example.com',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    }

    public function test_form_validation_requires_valid_email(): void
    {
        Livewire::test(CreateCustomer::class)
            ->fillForm([
                'name' => 'Test User',
                'email' => 'invalid-email',
            ])
            ->call('create')
            ->assertHasFormErrors(['email' => 'email']);
    }

    public function test_form_validation_requires_unique_email(): void
    {
        $existingCustomer = User::factory()->create(['email' => 'test@example.com']);

        Livewire::test(CreateCustomer::class)
            ->fillForm([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ])
            ->call('create')
            ->assertHasFormErrors(['email' => 'unique']);
    }

    public function test_can_handle_empty_database(): void
    {
        Livewire::test(ListCustomers::class)
            ->assertOk()
            ->assertCanNotSeeTableRecords([]);
    }

    public function test_resource_has_correct_navigation_label(): void
    {
        $this->assertEquals(__('customers.title'), CustomerManagementResource::getNavigationLabel());
    }

    public function test_resource_has_correct_navigation_group(): void
    {
        $this->assertEquals('Customers', CustomerManagementResource::getNavigationGroup());
    }

    public function test_resource_has_correct_model_label(): void
    {
        $this->assertEquals(__('customers.single'), CustomerManagementResource::getModelLabel());
    }

    public function test_resource_has_correct_plural_model_label(): void
    {
        $this->assertEquals(__('customers.plural'), CustomerManagementResource::getPluralModelLabel());
    }

    public function test_resource_uses_correct_model(): void
    {
        $this->assertEquals(User::class, CustomerManagementResource::getModel());
    }

    public function test_resource_has_required_pages(): void
    {
        $pages = CustomerManagementResource::getPages();

        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayHasKey('create', $pages);
        $this->assertArrayHasKey('view', $pages);
        $this->assertArrayHasKey('edit', $pages);
    }

    public function test_resource_has_required_relations(): void
    {
        $relations = CustomerManagementResource::getRelations();

        $this->assertIsArray($relations);
    }
}
