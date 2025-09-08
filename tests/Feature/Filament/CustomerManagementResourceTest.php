<?php declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\CustomerManagementResource;
use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class CustomerManagementResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($this->admin);
    }

    public function test_can_render_customer_management_index(): void
    {
        $this->get(CustomerManagementResource::getUrl('index'))
            ->assertOk();
    }

    public function test_can_list_customers_excluding_admins(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        $admin = User::factory()->create(['is_admin' => true]);

        Livewire::test(CustomerManagementResource\Pages\ListCustomerManagement::class)
            ->assertCanSeeTableRecords([$customer])
            ->assertCanNotSeeTableRecords([$admin]);
    }

    public function test_can_create_customer(): void
    {
        $newData = User::factory()->make(['is_admin' => false]);

        Livewire::test(CustomerManagementResource\Pages\CreateCustomerManagement::class)
            ->fillForm([
                'name' => $newData->name,
                'email' => $newData->email,
                'preferred_locale' => 'lt',
                'is_active' => true,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'name' => $newData->name,
            'email' => $newData->email,
            'is_admin' => false,
            'is_active' => true,
        ]);
    }

    public function test_can_view_customer_details(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        Order::factory()->count(3)->create(['user_id' => $customer->id]);

        $this->get(CustomerManagementResource::getUrl('view', ['record' => $customer]))
            ->assertOk();
    }

    public function test_can_edit_customer(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        $newData = User::factory()->make();

        Livewire::test(CustomerManagementResource\Pages\EditCustomerManagement::class, ['record' => $customer->getRouteKey()])
            ->fillForm([
                'name' => $newData->name,
                'preferred_locale' => 'en',
                'is_active' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($customer->refresh())
            ->name->toBe($newData->name)
            ->preferred_locale->toBe('en')
            ->is_active->toBeFalse();
    }

    public function test_can_filter_customers_by_verification_status(): void
    {
        $verifiedCustomer = User::factory()->create([
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);
        $unverifiedCustomer = User::factory()->create([
            'is_admin' => false,
            'email_verified_at' => null,
        ]);

        Livewire::test(CustomerManagementResource\Pages\ListCustomerManagement::class)
            ->filterTable('email_verified_at', true)
            ->assertCanSeeTableRecords([$verifiedCustomer])
            ->assertCanNotSeeTableRecords([$unverifiedCustomer]);
    }

    public function test_can_filter_customers_with_orders(): void
    {
        $customerWithOrders = User::factory()->create(['is_admin' => false]);
        $customerWithoutOrders = User::factory()->create(['is_admin' => false]);
        
        Order::factory()->create(['user_id' => $customerWithOrders->id]);

        Livewire::test(CustomerManagementResource\Pages\ListCustomerManagement::class)
            ->filterTable('has_orders')
            ->assertCanSeeTableRecords([$customerWithOrders])
            ->assertCanNotSeeTableRecords([$customerWithoutOrders]);
    }

    public function test_can_bulk_activate_customers(): void
    {
        $customers = User::factory()->count(3)->create([
            'is_admin' => false,
            'is_active' => false,
        ]);

        Livewire::test(CustomerManagementResource\Pages\ListCustomerManagement::class)
            ->selectTableRecords($customers)
            ->callTableBulkAction('activate_accounts');

        foreach ($customers as $customer) {
            expect($customer->refresh()->is_active)->toBeTrue();
        }
    }

    public function test_customer_order_statistics_display_correctly(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        
        Order::factory()->count(2)->create([
            'user_id' => $customer->id,
            'total' => 100.00,
        ]);
        
        Order::factory()->create([
            'user_id' => $customer->id,
            'total' => 200.00,
        ]);

        Livewire::test(CustomerManagementResource\Pages\ListCustomerManagement::class)
            ->assertCanSeeTableRecords([$customer])
            ->assertTableColumnStateSet('orders_count', '3', $customer)
            ->assertTableColumnStateSet('orders_sum_total', '400', $customer);
    }

    public function test_can_search_customers_by_name_and_email(): void
    {
        $customer1 = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_admin' => false,
        ]);
        
        $customer2 = User::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'is_admin' => false,
        ]);

        Livewire::test(CustomerManagementResource\Pages\ListCustomerManagement::class)
            ->searchTable('john')
            ->assertCanSeeTableRecords([$customer1])
            ->assertCanNotSeeTableRecords([$customer2]);
    }
}
