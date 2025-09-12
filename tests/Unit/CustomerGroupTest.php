<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\CustomerGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerGroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_group_can_be_created(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'name' => 'VIP Customers',
            'code' => 'VIP',
            'description' => 'High-value customers with special privileges',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('customer_groups', [
            'name' => 'VIP Customers',
            'code' => 'VIP',
            'description' => 'High-value customers with special privileges',
            'is_active' => true,
        ]);
    }

    public function test_customer_group_casts_work_correctly(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 5,
            'created_at' => now(),
        ]);

        $this->assertIsBool($customerGroup->is_active);
        $this->assertIsBool($customerGroup->is_default);
        $this->assertIsInt($customerGroup->sort_order);
        $this->assertInstanceOf(\Carbon\Carbon::class, $customerGroup->created_at);
    }

    public function test_customer_group_fillable_attributes(): void
    {
        $customerGroup = new CustomerGroup();
        $fillable = $customerGroup->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('code', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('is_active', $fillable);
    }

    public function test_customer_group_scope_active(): void
    {
        $activeGroup = CustomerGroup::factory()->create(['is_active' => true]);
        $inactiveGroup = CustomerGroup::factory()->create(['is_active' => false]);

        $activeGroups = CustomerGroup::active()->get();

        $this->assertTrue($activeGroups->contains($activeGroup));
        $this->assertFalse($activeGroups->contains($inactiveGroup));
    }

    public function test_customer_group_scope_default(): void
    {
        $defaultGroup = CustomerGroup::factory()->create(['is_default' => true]);
        $nonDefaultGroup = CustomerGroup::factory()->create(['is_default' => false]);

        $defaultGroups = CustomerGroup::default()->get();

        $this->assertTrue($defaultGroups->contains($defaultGroup));
        $this->assertFalse($defaultGroups->contains($nonDefaultGroup));
    }

    public function test_customer_group_scope_ordered(): void
    {
        $group1 = CustomerGroup::factory()->create(['sort_order' => 2]);
        $group2 = CustomerGroup::factory()->create(['sort_order' => 1]);
        $group3 = CustomerGroup::factory()->create(['sort_order' => 3]);

        $orderedGroups = CustomerGroup::ordered()->get();

        $this->assertEquals($group2->id, $orderedGroups->first()->id);
        $this->assertEquals($group3->id, $orderedGroups->last()->id);
    }

    public function test_customer_group_can_have_many_users(): void
    {
        $customerGroup = CustomerGroup::factory()->create();
        $users = User::factory()->count(3)->create();

        $customerGroup->users()->attach($users->pluck('id'));

        $this->assertCount(3, $customerGroup->users);
        $this->assertInstanceOf(User::class, $customerGroup->users->first());
    }

    public function test_customer_group_can_have_discount_settings(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'discount_percentage' => 10.00,
            'discount_fixed' => 5.00,
            'discount_type' => 'percentage',
        ]);

        $this->assertEquals(10.00, $customerGroup->discount_percentage);
        $this->assertEquals(5.00, $customerGroup->discount_fixed);
        $this->assertEquals('percentage', $customerGroup->discount_type);
    }

    public function test_customer_group_can_have_shipping_settings(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'free_shipping' => true,
            'shipping_discount_percentage' => 50.00,
        ]);

        $this->assertTrue($customerGroup->free_shipping);
        $this->assertEquals(50.00, $customerGroup->shipping_discount_percentage);
    }

    public function test_customer_group_can_have_minimum_order_amount(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'minimum_order_amount' => 100.00,
        ]);

        $this->assertEquals(100.00, $customerGroup->minimum_order_amount);
    }

    public function test_customer_group_can_have_maximum_order_amount(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'maximum_order_amount' => 10000.00,
        ]);

        $this->assertEquals(10000.00, $customerGroup->maximum_order_amount);
    }

    public function test_customer_group_can_have_credit_limit(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'credit_limit' => 5000.00,
        ]);

        $this->assertEquals(5000.00, $customerGroup->credit_limit);
    }

    public function test_customer_group_can_have_payment_terms(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'payment_terms' => 'net_30',
        ]);

        $this->assertEquals('net_30', $customerGroup->payment_terms);
    }

    public function test_customer_group_can_have_tax_settings(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'tax_exempt' => true,
            'tax_rate' => 0.00,
        ]);

        $this->assertTrue($customerGroup->tax_exempt);
        $this->assertEquals(0.00, $customerGroup->tax_rate);
    }

    public function test_customer_group_can_have_priority_settings(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'priority' => 'high',
            'priority_level' => 1,
        ]);

        $this->assertEquals('high', $customerGroup->priority);
        $this->assertEquals(1, $customerGroup->priority_level);
    }

    public function test_customer_group_can_have_metadata(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'metadata' => [
                'created_by' => 'admin',
                'approval_required' => true,
                'special_notes' => 'VIP customers with special privileges',
            ],
        ]);

        $this->assertIsArray($customerGroup->metadata);
        $this->assertEquals('admin', $customerGroup->metadata['created_by']);
        $this->assertTrue($customerGroup->metadata['approval_required']);
        $this->assertEquals('VIP customers with special privileges', $customerGroup->metadata['special_notes']);
    }

    public function test_customer_group_can_have_color(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'color' => '#FF5733',
        ]);

        $this->assertEquals('#FF5733', $customerGroup->color);
    }

    public function test_customer_group_can_have_icon(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'icon' => 'heroicon-o-star',
        ]);

        $this->assertEquals('heroicon-o-star', $customerGroup->icon);
    }

    public function test_customer_group_can_have_auto_assignment_rules(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'auto_assignment_rules' => [
                'minimum_order_count' => 10,
                'minimum_order_value' => 1000.00,
                'registration_date' => '2024-01-01',
            ],
        ]);

        $this->assertIsArray($customerGroup->auto_assignment_rules);
        $this->assertEquals(10, $customerGroup->auto_assignment_rules['minimum_order_count']);
        $this->assertEquals(1000.00, $customerGroup->auto_assignment_rules['minimum_order_value']);
        $this->assertEquals('2024-01-01', $customerGroup->auto_assignment_rules['registration_date']);
    }

    public function test_customer_group_can_have_notification_settings(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'notification_settings' => [
                'email_notifications' => true,
                'sms_notifications' => false,
                'push_notifications' => true,
            ],
        ]);

        $this->assertIsArray($customerGroup->notification_settings);
        $this->assertTrue($customerGroup->notification_settings['email_notifications']);
        $this->assertFalse($customerGroup->notification_settings['sms_notifications']);
        $this->assertTrue($customerGroup->notification_settings['push_notifications']);
    }

    public function test_customer_group_can_have_access_permissions(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'access_permissions' => [
                'early_access' => true,
                'exclusive_products' => true,
                'priority_support' => true,
            ],
        ]);

        $this->assertIsArray($customerGroup->access_permissions);
        $this->assertTrue($customerGroup->access_permissions['early_access']);
        $this->assertTrue($customerGroup->access_permissions['exclusive_products']);
        $this->assertTrue($customerGroup->access_permissions['priority_support']);
    }

    public function test_customer_group_can_have_expiry_date(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'expires_at' => now()->addYear(),
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $customerGroup->expires_at);
    }

    public function test_customer_group_can_have_usage_statistics(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'usage_statistics' => [
                'total_orders' => 150,
                'total_revenue' => 25000.00,
                'average_order_value' => 166.67,
            ],
        ]);

        $this->assertIsArray($customerGroup->usage_statistics);
        $this->assertEquals(150, $customerGroup->usage_statistics['total_orders']);
        $this->assertEquals(25000.00, $customerGroup->usage_statistics['total_revenue']);
        $this->assertEquals(166.67, $customerGroup->usage_statistics['average_order_value']);
    }
}