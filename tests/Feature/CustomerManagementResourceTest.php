<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\CustomerManagementResource\Pages\CreateCustomer;
use App\Filament\Resources\CustomerManagementResource\Pages\EditCustomer;
use App\Filament\Resources\CustomerManagementResource\Pages\ListCustomers;
use App\Filament\Resources\CustomerManagementResource\Pages\ViewCustomer;
use App\Models\CartItem;
use App\Models\CustomerGroup;
use App\Models\DiscountRedemption;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

final class CustomerManagementResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($adminUser);
    }

    public function test_can_load_customer_list_page(): void
    {
        $customers = User::factory()->count(5)->create();

        Livewire::test(ListCustomers::class)
            ->assertOk()
            ->assertCanSeeTableRecords($customers);
    }

    public function test_can_create_customer(): void
    {
        $customerGroup = CustomerGroup::factory()->create();
        $newCustomerData = User::factory()->make([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+37060000000',
            'is_active' => true,
            'is_verified' => false,
        ]);

        Livewire::test(CreateCustomer::class)
            ->fillForm([
                'name' => $newCustomerData->name,
                'email' => $newCustomerData->email,
                'phone' => $newCustomerData->phone,
                'is_active' => true,
                'is_verified' => false,
                'customer_group_id' => $customerGroup->id,
                'preferred_language' => 'lt',
                'preferred_currency' => 'EUR',
                'newsletter_subscription' => false,
                'sms_notifications' => false,
            ])
            ->call('create')
            ->assertNotified();

        $this->assertDatabaseHas('users', [
            'name' => $newCustomerData->name,
            'email' => $newCustomerData->email,
            'phone' => $newCustomerData->phone,
            'is_active' => true,
            'is_verified' => false,
        ]);
    }

    public function test_can_edit_customer(): void
    {
        $customer = User::factory()->create([
            'is_active' => true,
            'is_verified' => false,
        ]);

        Livewire::test(EditCustomer::class, [
            'record' => $customer->id,
        ])
            ->fillForm([
                'is_active' => false,
                'is_verified' => true,
                'preferred_language' => 'en',
                'newsletter_subscription' => true,
            ])
            ->call('save')
            ->assertNotified();

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'is_active' => false,
            'is_verified' => true,
        ]);
    }

    public function test_can_view_customer(): void
    {
        $customer = User::factory()->create();

        Livewire::test(ViewCustomer::class, [
            'record' => $customer->id,
        ])
            ->assertOk();
    }

    public function test_can_filter_customers_by_customer_group(): void
    {
        $customerGroup1 = CustomerGroup::factory()->create(['name' => 'VIP']);
        $customerGroup2 = CustomerGroup::factory()->create(['name' => 'Regular']);

        $customer1 = User::factory()->create(['customer_group_id' => $customerGroup1->id]);
        $customer2 = User::factory()->create(['customer_group_id' => $customerGroup2->id]);

        Livewire::test(ListCustomers::class)
            ->filterTable('customer_group_id', $customerGroup1->id)
            ->assertCanSeeTableRecords([$customer1])
            ->assertCanNotSeeTableRecords([$customer2]);
    }

    public function test_can_filter_customers_by_email_verification_status(): void
    {
        $verifiedCustomer = User::factory()->create(['email_verified_at' => now()]);
        $unverifiedCustomer = User::factory()->create(['email_verified_at' => null]);

        Livewire::test(ListCustomers::class)
            ->filterTable('email_verified_at', '1')
            ->assertCanSeeTableRecords([$verifiedCustomer])
            ->assertCanNotSeeTableRecords([$unverifiedCustomer]);
    }

    public function test_can_filter_customers_by_active_status(): void
    {
        $activeCustomer = User::factory()->create(['is_active' => true]);
        $inactiveCustomer = User::factory()->create(['is_active' => false]);

        Livewire::test(ListCustomers::class)
            ->filterTable('is_active', '1')
            ->assertCanSeeTableRecords([$activeCustomer])
            ->assertCanNotSeeTableRecords([$inactiveCustomer]);
    }

    public function test_can_verify_customer_email(): void
    {
        $customer = User::factory()->create(['email_verified_at' => null]);

        Livewire::test(ListCustomers::class)
            ->callTableAction('verify_email', $customer);

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
        ]);

        $customer->refresh();
        $this->assertNotNull($customer->email_verified_at);
    }

    public function test_can_toggle_customer_active_status(): void
    {
        $customer = User::factory()->create(['is_active' => true]);

        Livewire::test(ListCustomers::class)
            ->callTableAction('toggle_active', $customer);

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'is_active' => false,
        ]);
    }

    public function test_can_bulk_verify_customer_emails(): void
    {
        $customers = User::factory()->count(3)->create(['email_verified_at' => null]);

        Livewire::test(ListCustomers::class)
            ->callTableBulkAction('verify_emails', $customers);

        foreach ($customers as $customer) {
            $customer->refresh();
            $this->assertNotNull($customer->email_verified_at);
        }
    }

    public function test_can_bulk_activate_customers(): void
    {
        $customers = User::factory()->count(3)->create(['is_active' => false]);

        Livewire::test(ListCustomers::class)
            ->callTableBulkAction('activate', $customers);

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
            ->callTableBulkAction('deactivate', $customers);

        foreach ($customers as $customer) {
            $this->assertDatabaseHas('users', [
                'id' => $customer->id,
                'is_active' => false,
            ]);
        }
    }

    public function test_customer_form_validation(): void
    {
        Livewire::test(CreateCustomer::class)
            ->fillForm([
                'name' => '',
                'email' => 'invalid-email',
            ])
            ->call('create')
            ->assertHasFormErrors(['name', 'email']);
    }

    public function test_customer_relationships(): void
    {
        $customerGroup = CustomerGroup::factory()->create();
        $customer = User::factory()->create(['customer_group_id' => $customerGroup->id]);

        // Create related records
        $order = Order::factory()->create(['user_id' => $customer->id]);
        $review = Review::factory()->create(['user_id' => $customer->id]);
        $cartItem = CartItem::factory()->create(['user_id' => $customer->id]);
        $discountRedemption = DiscountRedemption::factory()->create(['user_id' => $customer->id]);

        // Test relationships
        $this->assertEquals($customerGroup->id, $customer->customerGroup->id);
        $this->assertTrue($customer->orders->contains($order));
        $this->assertTrue($customer->reviews->contains($review));
        $this->assertTrue($customer->cartItems->contains($cartItem));
        $this->assertTrue($customer->discountRedemptions->contains($discountRedemption));
    }

    public function test_customer_search_functionality(): void
    {
        $customer1 = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        $customer2 = User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

        Livewire::test(ListCustomers::class)
            ->searchTable('John')
            ->assertCanSeeTableRecords([$customer1])
            ->assertCanNotSeeTableRecords([$customer2]);
    }

    public function test_customer_sorting_functionality(): void
    {
        $customer1 = User::factory()->create(['name' => 'Alice']);
        $customer2 = User::factory()->create(['name' => 'Bob']);

        Livewire::test(ListCustomers::class)
            ->sortTable('name', 'asc')
            ->assertCanSeeTableRecords([$customer1, $customer2]);
    }
}
