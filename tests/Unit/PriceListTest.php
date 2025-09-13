<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\PriceList;
use App\Models\CustomerGroup;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriceListTest extends TestCase
{
    use RefreshDatabase;

    public function test_price_list_can_be_created(): void
    {
        $priceList = PriceList::factory()->create([
            'name' => 'VIP Customer Prices',
            'code' => 'VIP-PRICES',
            'description' => 'Special pricing for VIP customers',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('price_lists', [
            'name' => 'VIP Customer Prices',
            'code' => 'VIP-PRICES',
            'description' => 'Special pricing for VIP customers',
            'is_active' => true,
        ]);
    }

    public function test_price_list_casts_work_correctly(): void
    {
        $priceList = PriceList::factory()->create([
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 5,
            'created_at' => now(),
        ]);

        $this->assertIsBool($priceList->is_active);
        $this->assertIsBool($priceList->is_default);
        $this->assertIsInt($priceList->sort_order);
        $this->assertInstanceOf(\Carbon\Carbon::class, $priceList->created_at);
    }

    public function test_price_list_fillable_attributes(): void
    {
        $priceList = new PriceList();
        $fillable = $priceList->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('code', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('is_active', $fillable);
    }

    public function test_price_list_scope_active(): void
    {
        $activePriceList = PriceList::factory()->create(['is_active' => true]);
        $inactivePriceList = PriceList::factory()->create(['is_active' => false]);

        $activePriceLists = PriceList::active()->get();

        $this->assertTrue($activePriceLists->contains($activePriceList));
        $this->assertFalse($activePriceLists->contains($inactivePriceList));
    }

    public function test_price_list_scope_default(): void
    {
        $defaultPriceList = PriceList::factory()->create(['is_default' => true]);
        $nonDefaultPriceList = PriceList::factory()->create(['is_default' => false]);

        $defaultPriceLists = PriceList::default()->get();

        $this->assertTrue($defaultPriceLists->contains($defaultPriceList));
        $this->assertFalse($defaultPriceLists->contains($nonDefaultPriceList));
    }

    public function test_price_list_scope_ordered(): void
    {
        $priceList1 = PriceList::factory()->create(['sort_order' => 2]);
        $priceList2 = PriceList::factory()->create(['sort_order' => 1]);
        $priceList3 = PriceList::factory()->create(['sort_order' => 3]);

        $orderedPriceLists = PriceList::ordered()->get();

        $this->assertEquals($priceList2->id, $orderedPriceLists->first()->id);
        $this->assertEquals($priceList3->id, $orderedPriceLists->last()->id);
    }

    public function test_price_list_can_have_customer_groups(): void
    {
        $priceList = PriceList::factory()->create();
        $customerGroups = CustomerGroup::factory()->count(3)->create();

        $priceList->customerGroups()->attach($customerGroups->pluck('id'));

        $this->assertCount(3, $priceList->customerGroups);
        $this->assertInstanceOf(CustomerGroup::class, $priceList->customerGroups->first());
    }

    public function test_price_list_can_have_products(): void
    {
        $priceList = PriceList::factory()->create();
        $products = Product::factory()->count(3)->create();

        $priceList->products()->attach($products->pluck('id'));

        $this->assertCount(3, $priceList->products);
        $this->assertInstanceOf(Product::class, $priceList->products->first());
    }

    public function test_price_list_can_have_validity_period(): void
    {
        $priceList = PriceList::factory()->create([
            'valid_from' => now(),
            'valid_until' => now()->addYear(),
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $priceList->valid_from);
        $this->assertInstanceOf(\Carbon\Carbon::class, $priceList->valid_until);
    }

    public function test_price_list_can_have_currency(): void
    {
        $priceList = PriceList::factory()->create([
            'currency' => 'EUR',
        ]);

        $this->assertEquals('EUR', $priceList->currency);
    }

    public function test_price_list_can_have_discount_settings(): void
    {
        $priceList = PriceList::factory()->create([
            'discount_percentage' => 10.00,
            'discount_fixed' => 5.00,
            'discount_type' => 'percentage',
        ]);

        $this->assertEquals(10.00, $priceList->discount_percentage);
        $this->assertEquals(5.00, $priceList->discount_fixed);
        $this->assertEquals('percentage', $priceList->discount_type);
    }

    public function test_price_list_can_have_minimum_order_amount(): void
    {
        $priceList = PriceList::factory()->create([
            'minimum_order_amount' => 100.00,
        ]);

        $this->assertEquals(100.00, $priceList->minimum_order_amount);
    }

    public function test_price_list_can_have_maximum_order_amount(): void
    {
        $priceList = PriceList::factory()->create([
            'maximum_order_amount' => 10000.00,
        ]);

        $this->assertEquals(10000.00, $priceList->maximum_order_amount);
    }

    public function test_price_list_can_have_priority(): void
    {
        $priceList = PriceList::factory()->create([
            'priority' => 'high',
        ]);

        $this->assertEquals('high', $priceList->priority);
    }

    public function test_price_list_can_have_category(): void
    {
        $priceList = PriceList::factory()->create([
            'category' => 'wholesale',
        ]);

        $this->assertEquals('wholesale', $priceList->category);
    }

    public function test_price_list_can_have_conditions(): void
    {
        $priceList = PriceList::factory()->create([
            'conditions' => [
                'minimum_quantity' => 10,
                'customer_type' => 'wholesale',
                'payment_terms' => 'net_30',
            ],
        ]);

        $this->assertIsArray($priceList->conditions);
        $this->assertEquals(10, $priceList->conditions['minimum_quantity']);
        $this->assertEquals('wholesale', $priceList->conditions['customer_type']);
        $this->assertEquals('net_30', $priceList->conditions['payment_terms']);
    }

    public function test_price_list_can_have_metadata(): void
    {
        $priceList = PriceList::factory()->create([
            'metadata' => [
                'created_by' => 'admin',
                'approval_status' => 'approved',
                'special_notes' => 'Special pricing for VIP customers',
                'tags' => ['vip', 'wholesale', 'special'],
            ],
        ]);

        $this->assertIsArray($priceList->metadata);
        $this->assertEquals('admin', $priceList->metadata['created_by']);
        $this->assertEquals('approved', $priceList->metadata['approval_status']);
        $this->assertEquals('Special pricing for VIP customers', $priceList->metadata['special_notes']);
        $this->assertIsArray($priceList->metadata['tags']);
    }

    public function test_price_list_can_have_approval_settings(): void
    {
        $priceList = PriceList::factory()->create([
            'requires_approval' => true,
            'approval_notes' => 'Requires manager approval',
        ]);

        $this->assertTrue($priceList->requires_approval);
        $this->assertEquals('Requires manager approval', $priceList->approval_notes);
    }

    public function test_price_list_can_have_usage_limits(): void
    {
        $priceList = PriceList::factory()->create([
            'usage_limit' => 1000,
            'usage_count' => 150,
        ]);

        $this->assertEquals(1000, $priceList->usage_limit);
        $this->assertEquals(150, $priceList->usage_count);
    }

    public function test_price_list_can_have_auto_assignment_rules(): void
    {
        $priceList = PriceList::factory()->create([
            'auto_assignment_rules' => [
                'customer_group' => 'vip',
                'minimum_order_value' => 500.00,
                'registration_date' => '2024-01-01',
            ],
        ]);

        $this->assertIsArray($priceList->auto_assignment_rules);
        $this->assertEquals('vip', $priceList->auto_assignment_rules['customer_group']);
        $this->assertEquals(500.00, $priceList->auto_assignment_rules['minimum_order_value']);
        $this->assertEquals('2024-01-01', $priceList->auto_assignment_rules['registration_date']);
    }

    public function test_price_list_can_have_notification_settings(): void
    {
        $priceList = PriceList::factory()->create([
            'notification_settings' => [
                'email_notifications' => true,
                'sms_notifications' => false,
                'push_notifications' => true,
            ],
        ]);

        $this->assertIsArray($priceList->notification_settings);
        $this->assertTrue($priceList->notification_settings['email_notifications']);
        $this->assertFalse($priceList->notification_settings['sms_notifications']);
        $this->assertTrue($priceList->notification_settings['push_notifications']);
    }

    public function test_price_list_can_have_audit_trail(): void
    {
        $priceList = PriceList::factory()->create([
            'audit_trail' => [
                'created_by' => 'admin',
                'created_at' => now(),
                'last_modified_by' => 'manager',
                'last_modified_at' => now(),
            ],
        ]);

        $this->assertIsArray($priceList->audit_trail);
        $this->assertEquals('admin', $priceList->audit_trail['created_by']);
        $this->assertInstanceOf(\Carbon\Carbon::class, $priceList->audit_trail['created_at']);
        $this->assertEquals('manager', $priceList->audit_trail['last_modified_by']);
        $this->assertInstanceOf(\Carbon\Carbon::class, $priceList->audit_trail['last_modified_at']);
    }

    public function test_price_list_can_have_performance_metrics(): void
    {
        $priceList = PriceList::factory()->create([
            'performance_metrics' => [
                'total_orders' => 250,
                'total_revenue' => 75000.00,
                'average_order_value' => 300.00,
                'conversion_rate' => 15.5,
            ],
        ]);

        $this->assertIsArray($priceList->performance_metrics);
        $this->assertEquals(250, $priceList->performance_metrics['total_orders']);
        $this->assertEquals(75000.00, $priceList->performance_metrics['total_revenue']);
        $this->assertEquals(300.00, $priceList->performance_metrics['average_order_value']);
        $this->assertEquals(15.5, $priceList->performance_metrics['conversion_rate']);
    }

    public function test_price_list_can_have_expiry_settings(): void
    {
        $priceList = PriceList::factory()->create([
            'expires_at' => now()->addYear(),
            'auto_renew' => true,
            'renewal_period' => 'yearly',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $priceList->expires_at);
        $this->assertTrue($priceList->auto_renew);
        $this->assertEquals('yearly', $priceList->renewal_period);
    }
}
