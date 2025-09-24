<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\CustomerGroupResource;
use App\Models\CustomerGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
/**
 * CustomerGroupResourceTest
 *
 * Comprehensive test suite for CustomerGroupResource functionality including CRUD operations, filters, and relationships.
 */
use Tests\TestCase;

final class CustomerGroupResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]));
    }

    public function test_can_list_customer_groups(): void
    {
        $customerGroups = CustomerGroup::factory()->count(3)->create();

        Livewire::test(CustomerGroupResource\Pages\ListCustomerGroups::class)
            ->assertCanSeeTableRecords($customerGroups);
    }

    public function test_can_create_customer_group(): void
    {
        $customerGroupData = [
            'name' => 'Test Customer Group',
            'code' => 'TEST_GROUP',
            'description' => 'Test customer group description',
            'slug' => 'test-customer-group',
            'discount_percentage' => 10.00,
            'discount_fixed' => 5.00,
            'has_special_pricing' => true,
            'has_volume_discounts' => false,
            'can_view_prices' => true,
            'can_place_orders' => true,
            'can_view_catalog' => true,
            'can_use_coupons' => true,
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 1,
            'type' => 'regular',
        ];

        Livewire::test(CustomerGroupResource\Pages\CreateCustomerGroup::class)
            ->fillForm($customerGroupData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('customer_groups', [
            'name' => 'Test Customer Group',
            'code' => 'TEST_GROUP',
        ]);
    }

    public function test_can_edit_customer_group(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'name' => 'Original Name',
            'code' => 'ORIG',
        ]);

        Livewire::test(CustomerGroupResource\Pages\EditCustomerGroup::class, [
            'record' => $customerGroup->getRouteKey(),
        ])
            ->fillForm([
                'name' => 'Updated Name',
                'code' => 'UPDT',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $customerGroup->refresh();
        $this->assertEquals('Updated Name', $customerGroup->name);
        $this->assertEquals('UPDT', $customerGroup->code);
    }

    public function test_can_view_customer_group(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'name' => 'View Test Customer Group',
            'code' => 'VTCG',
        ]);

        Livewire::test(CustomerGroupResource\Pages\ViewCustomerGroup::class, [
            'record' => $customerGroup->getRouteKey(),
        ])
            ->assertCanSeeText('View Test Customer Group')
            ->assertCanSeeText('VTCG');
    }

    public function test_can_delete_customer_group(): void
    {
        $customerGroup = CustomerGroup::factory()->create();

        Livewire::test(CustomerGroupResource\Pages\ListCustomerGroups::class)
            ->callTableAction('delete', $customerGroup);

        $this->assertSoftDeleted('customer_groups', [
            'id' => $customerGroup->id,
        ]);
    }

    public function test_can_bulk_delete_customer_groups(): void
    {
        $customerGroups = CustomerGroup::factory()->count(3)->create();

        Livewire::test(CustomerGroupResource\Pages\ListCustomerGroups::class)
            ->callTableBulkAction('delete', $customerGroups);

        foreach ($customerGroups as $customerGroup) {
            $this->assertSoftDeleted('customer_groups', [
                'id' => $customerGroup->id,
            ]);
        }
    }

    public function test_can_filter_customer_groups_by_type(): void
    {
        CustomerGroup::factory()->create(['type' => 'regular']);
        CustomerGroup::factory()->create(['type' => 'vip']);

        Livewire::test(CustomerGroupResource\Pages\ListCustomerGroups::class)
            ->filterTable('type', 'regular')
            ->assertCanSeeTableRecords(CustomerGroup::where('type', 'regular')->get())
            ->assertCanNotSeeTableRecords(CustomerGroup::where('type', 'vip')->get());
    }

    public function test_can_filter_customer_groups_by_active_status(): void
    {
        CustomerGroup::factory()->create(['is_active' => true]);
        CustomerGroup::factory()->create(['is_active' => false]);

        Livewire::test(CustomerGroupResource\Pages\ListCustomerGroups::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords(CustomerGroup::where('is_active', true)->get());
    }

    public function test_can_filter_customer_groups_by_default_status(): void
    {
        CustomerGroup::factory()->create(['is_default' => true]);
        CustomerGroup::factory()->create(['is_default' => false]);

        Livewire::test(CustomerGroupResource\Pages\ListCustomerGroups::class)
            ->filterTable('is_default', true)
            ->assertCanSeeTableRecords(CustomerGroup::where('is_default', true)->get());
    }

    public function test_can_filter_customer_groups_by_special_pricing(): void
    {
        CustomerGroup::factory()->create(['has_special_pricing' => true]);
        CustomerGroup::factory()->create(['has_special_pricing' => false]);

        Livewire::test(CustomerGroupResource\Pages\ListCustomerGroups::class)
            ->filterTable('has_special_pricing', true)
            ->assertCanSeeTableRecords(CustomerGroup::where('has_special_pricing', true)->get());
    }

    public function test_can_filter_customer_groups_by_volume_discounts(): void
    {
        CustomerGroup::factory()->create(['has_volume_discounts' => true]);
        CustomerGroup::factory()->create(['has_volume_discounts' => false]);

        Livewire::test(CustomerGroupResource\Pages\ListCustomerGroups::class)
            ->filterTable('has_volume_discounts', true)
            ->assertCanSeeTableRecords(CustomerGroup::where('has_volume_discounts', true)->get());
    }

    public function test_can_search_customer_groups_by_name(): void
    {
        CustomerGroup::factory()->create(['name' => 'VIP Customers']);
        CustomerGroup::factory()->create(['name' => 'Regular Customers']);

        Livewire::test(CustomerGroupResource\Pages\ListCustomerGroups::class)
            ->searchTable('VIP Customers')
            ->assertCanSeeTableRecords(CustomerGroup::where('name', 'like', '%VIP Customers%')->get());
    }

    public function test_can_toggle_customer_group_active_status(): void
    {
        $customerGroup = CustomerGroup::factory()->create(['is_active' => false]);

        Livewire::test(CustomerGroupResource\Pages\ListCustomerGroups::class)
            ->callTableAction('toggle_active', $customerGroup);

        $customerGroup->refresh();
        $this->assertTrue($customerGroup->is_active);
    }

    public function test_can_set_customer_group_as_default(): void
    {
        $customerGroup = CustomerGroup::factory()->create(['is_default' => false]);

        Livewire::test(CustomerGroupResource\Pages\ListCustomerGroups::class)
            ->callTableAction('set_default', $customerGroup);

        $customerGroup->refresh();
        $this->assertTrue($customerGroup->is_default);
    }

    public function test_can_bulk_activate_customer_groups(): void
    {
        $customerGroups = CustomerGroup::factory()->count(3)->create(['is_active' => false]);

        Livewire::test(CustomerGroupResource\Pages\ListCustomerGroups::class)
            ->callTableBulkAction('activate', $customerGroups);

        foreach ($customerGroups as $customerGroup) {
            $customerGroup->refresh();
            $this->assertTrue($customerGroup->is_active);
        }
    }

    public function test_can_bulk_deactivate_customer_groups(): void
    {
        $customerGroups = CustomerGroup::factory()->count(3)->create(['is_active' => true]);

        Livewire::test(CustomerGroupResource\Pages\ListCustomerGroups::class)
            ->callTableBulkAction('deactivate', $customerGroups);

        foreach ($customerGroups as $customerGroup) {
            $customerGroup->refresh();
            $this->assertFalse($customerGroup->is_active);
        }
    }

    public function test_customer_group_validation_requires_name(): void
    {
        Livewire::test(CustomerGroupResource\Pages\CreateCustomerGroup::class)
            ->fillForm([
                'name' => '',
                'code' => 'TEST',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    }

    public function test_customer_group_validation_code_must_be_unique(): void
    {
        CustomerGroup::factory()->create(['code' => 'EXISTING']);

        Livewire::test(CustomerGroupResource\Pages\CreateCustomerGroup::class)
            ->fillForm([
                'name' => 'Test Customer Group',
                'code' => 'EXISTING',
            ])
            ->call('create')
            ->assertHasFormErrors(['code' => 'unique']);
    }

    public function test_customer_group_validation_code_alpha_dash(): void
    {
        Livewire::test(CustomerGroupResource\Pages\CreateCustomerGroup::class)
            ->fillForm([
                'name' => 'Test Customer Group',
                'code' => 'INVALID CODE',
            ])
            ->call('create')
            ->assertHasFormErrors(['code' => 'alpha_dash']);
    }

    public function test_customer_group_validation_discount_percentage_numeric(): void
    {
        Livewire::test(CustomerGroupResource\Pages\CreateCustomerGroup::class)
            ->fillForm([
                'name' => 'Test Customer Group',
                'code' => 'TEST',
                'discount_percentage' => 'invalid',
            ])
            ->call('create')
            ->assertHasFormErrors(['discount_percentage' => 'numeric']);
    }

    public function test_customer_group_validation_discount_percentage_range(): void
    {
        Livewire::test(CustomerGroupResource\Pages\CreateCustomerGroup::class)
            ->fillForm([
                'name' => 'Test Customer Group',
                'code' => 'TEST',
                'discount_percentage' => 150.00,
            ])
            ->call('create')
            ->assertHasFormErrors(['discount_percentage' => 'max']);
    }

    public function test_customer_group_validation_discount_fixed_numeric(): void
    {
        Livewire::test(CustomerGroupResource\Pages\CreateCustomerGroup::class)
            ->fillForm([
                'name' => 'Test Customer Group',
                'code' => 'TEST',
                'discount_fixed' => 'invalid',
            ])
            ->call('create')
            ->assertHasFormErrors(['discount_fixed' => 'numeric']);
    }

    public function test_customer_group_validation_discount_fixed_minimum(): void
    {
        Livewire::test(CustomerGroupResource\Pages\CreateCustomerGroup::class)
            ->fillForm([
                'name' => 'Test Customer Group',
                'code' => 'TEST',
                'discount_fixed' => -10.00,
            ])
            ->call('create')
            ->assertHasFormErrors(['discount_fixed' => 'min']);
    }

    public function test_customer_group_scope_enabled(): void
    {
        CustomerGroup::factory()->create(['is_enabled' => true]);
        CustomerGroup::factory()->create(['is_enabled' => false]);

        $enabledGroups = CustomerGroup::enabled()->get();
        $this->assertCount(1, $enabledGroups);
        $this->assertTrue($enabledGroups->first()->is_enabled);
    }

    public function test_customer_group_scope_with_discount(): void
    {
        CustomerGroup::factory()->create(['discount_percentage' => 10.00]);
        CustomerGroup::factory()->create(['discount_percentage' => 0.00]);

        $discountGroups = CustomerGroup::withDiscount()->get();
        $this->assertCount(1, $discountGroups);
        $this->assertTrue($discountGroups->first()->discount_percentage > 0);
    }

    public function test_customer_group_helper_methods(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'discount_percentage' => 15.00,
            'is_enabled' => true,
        ]);

        $this->assertEquals(15.00, $customerGroup->discount_percentage);
        $this->assertTrue($customerGroup->hasDiscountRate());
        $this->assertTrue($customerGroup->is_active);
    }

    public function test_customer_group_users_count(): void
    {
        $customerGroup = CustomerGroup::factory()->create();
        $users = User::factory()->count(3)->create();

        // Attach users to customer group
        $customerGroup->users()->attach($users);

        $this->assertEquals(3, $customerGroup->users_count);
    }

    public function test_customer_group_relationships_users(): void
    {
        $customerGroup = CustomerGroup::factory()->create();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $customerGroup->users());
    }

    public function test_customer_group_relationships_customers(): void
    {
        $customerGroup = CustomerGroup::factory()->create();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $customerGroup->customers());
    }

    public function test_customer_group_relationships_discounts(): void
    {
        $customerGroup = CustomerGroup::factory()->create();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $customerGroup->discounts());
    }

    public function test_customer_group_relationships_price_lists(): void
    {
        $customerGroup = CustomerGroup::factory()->create();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $customerGroup->priceLists());
    }

    public function test_customer_group_metadata_get(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'metadata' => ['key1' => 'value1', 'key2' => 'value2'],
        ]);

        $this->assertEquals('value1', $customerGroup->getMetadata('key1'));
        $this->assertEquals('default', $customerGroup->getMetadata('nonexistent', 'default'));
    }

    public function test_customer_group_metadata_set(): void
    {
        $customerGroup = CustomerGroup::factory()->create();

        $customerGroup->setMetadata('new_key', 'new_value');
        $customerGroup->save();

        $this->assertEquals('new_value', $customerGroup->getMetadata('new_key'));
    }

    public function test_customer_group_type_regular(): void
    {
        $customerGroup = CustomerGroup::factory()->create(['type' => 'regular']);
        $this->assertEquals('regular', $customerGroup->type);
    }

    public function test_customer_group_type_vip(): void
    {
        $customerGroup = CustomerGroup::factory()->create(['type' => 'vip']);
        $this->assertEquals('vip', $customerGroup->type);
    }

    public function test_customer_group_type_wholesale(): void
    {
        $customerGroup = CustomerGroup::factory()->create(['type' => 'wholesale']);
        $this->assertEquals('wholesale', $customerGroup->type);
    }

    public function test_customer_group_type_retail(): void
    {
        $customerGroup = CustomerGroup::factory()->create(['type' => 'retail']);
        $this->assertEquals('retail', $customerGroup->type);
    }

    public function test_customer_group_type_corporate(): void
    {
        $customerGroup = CustomerGroup::factory()->create(['type' => 'corporate']);
        $this->assertEquals('corporate', $customerGroup->type);
    }

    public function test_customer_group_permissions(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'can_view_prices' => true,
            'can_place_orders' => true,
            'can_view_catalog' => false,
            'can_use_coupons' => true,
        ]);

        $this->assertTrue($customerGroup->can_view_prices);
        $this->assertTrue($customerGroup->can_place_orders);
        $this->assertFalse($customerGroup->can_view_catalog);
        $this->assertTrue($customerGroup->can_use_coupons);
    }

    public function test_customer_group_special_pricing(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'has_special_pricing' => true,
            'has_volume_discounts' => false,
        ]);

        $this->assertTrue($customerGroup->has_special_pricing);
        $this->assertFalse($customerGroup->has_volume_discounts);
    }

    public function test_customer_group_discount_calculation(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'discount_percentage' => 10.00,
            'discount_fixed' => 5.00,
        ]);

        $this->assertEquals(10.00, $customerGroup->discount_percentage);
        $this->assertEquals(5.00, $customerGroup->discount_fixed);
    }

    public function test_customer_group_sort_order(): void
    {
        CustomerGroup::factory()->create(['sort_order' => 3]);
        CustomerGroup::factory()->create(['sort_order' => 1]);
        CustomerGroup::factory()->create(['sort_order' => 2]);

        $customerGroups = CustomerGroup::orderBy('sort_order')->get();
        $this->assertEquals(1, $customerGroups->first()->sort_order);
        $this->assertEquals(3, $customerGroups->last()->sort_order);
    }

    public function test_customer_group_slug(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'slug' => 'test-customer-group',
        ]);

        $this->assertEquals('test-customer-group', $customerGroup->slug);
    }

    public function test_customer_group_description(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'description' => 'Test customer group description',
        ]);

        $this->assertEquals('Test customer group description', $customerGroup->description);
    }

    public function test_customer_group_conditions(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'conditions' => ['min_order_amount' => 100, 'max_discount' => 50],
        ]);

        $this->assertIsArray($customerGroup->conditions);
        $this->assertEquals(100, $customerGroup->conditions['min_order_amount']);
        $this->assertEquals(50, $customerGroup->conditions['max_discount']);
    }

    public function test_customer_group_is_active_attribute(): void
    {
        $customerGroup = CustomerGroup::factory()->create(['is_enabled' => true]);
        $this->assertTrue($customerGroup->is_active);

        $customerGroup->is_active = false;
        $this->assertFalse($customerGroup->is_active);
    }

    public function test_customer_group_set_is_active_attribute(): void
    {
        $customerGroup = CustomerGroup::factory()->create(['is_enabled' => true]);

        $customerGroup->is_active = false;
        $customerGroup->save();

        $customerGroup->refresh();
        $this->assertFalse($customerGroup->is_enabled);
    }
}
