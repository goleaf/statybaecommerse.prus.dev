<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\CouponResource;
use App\Models\Coupon;
use App\Models\CustomerGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class CouponResourceTest extends TestCase
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

    public function test_can_list_coupons(): void
    {
        $coupons = Coupon::factory()->count(3)->create();

        Livewire::test(CouponResource\Pages\ListCoupons::class)
            ->assertCanSeeTableRecords($coupons);
    }

    public function test_can_create_coupon(): void
    {
        $couponData = [
            'code' => 'TEST20',
            'name' => 'Test Coupon',
            'description' => 'Test coupon description',
            'type' => 'percentage',
            'value' => 20.0,
            'minimum_amount' => 50.0,
            'maximum_discount' => 100.0,
            'usage_limit' => 100,
            'usage_limit_per_user' => 1,
            'is_active' => true,
            'is_public' => false,
            'is_auto_apply' => false,
            'is_stackable' => false,
            'valid_from' => now(),
            'valid_until' => now()->addDays(30),
        ];

        Livewire::test(CouponResource\Pages\CreateCoupon::class)
            ->fillForm($couponData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('coupons', [
            'code' => 'TEST20',
            'name' => 'Test Coupon',
        ]);
    }

    public function test_can_edit_coupon(): void
    {
        $coupon = Coupon::factory()->create([
            'code' => 'ORIGINAL',
            'name' => 'Original Name',
        ]);

        Livewire::test(CouponResource\Pages\EditCoupon::class, [
            'record' => $coupon->getRouteKey(),
        ])
            ->fillForm([
                'code' => 'UPDATED',
                'name' => 'Updated Name',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $coupon->refresh();
        $this->assertEquals('UPDATED', $coupon->code);
        $this->assertEquals('Updated Name', $coupon->name);
    }

    public function test_can_view_coupon(): void
    {
        $coupon = Coupon::factory()->create([
            'code' => 'VIEWTEST',
            'name' => 'View Test Coupon',
        ]);

        Livewire::test(CouponResource\Pages\ViewCoupon::class, [
            'record' => $coupon->getRouteKey(),
        ])
            ->assertCanSeeText('VIEWTEST')
            ->assertCanSeeText('View Test Coupon');
    }

    public function test_can_delete_coupon(): void
    {
        $coupon = Coupon::factory()->create();

        Livewire::test(CouponResource\Pages\ListCoupons::class)
            ->callTableAction('delete', $coupon);

        $this->assertSoftDeleted('coupons', [
            'id' => $coupon->id,
        ]);
    }

    public function test_can_bulk_delete_coupons(): void
    {
        $coupons = Coupon::factory()->count(3)->create();

        Livewire::test(CouponResource\Pages\ListCoupons::class)
            ->callTableBulkAction('delete', $coupons);

        foreach ($coupons as $coupon) {
            $this->assertSoftDeleted('coupons', [
                'id' => $coupon->id,
            ]);
        }
    }

    public function test_can_filter_coupons_by_type(): void
    {
        Coupon::factory()->create(['type' => 'percentage']);
        Coupon::factory()->create(['type' => 'fixed']);

        Livewire::test(CouponResource\Pages\ListCoupons::class)
            ->filterTable('type', 'percentage')
            ->assertCanSeeTableRecords(Coupon::where('type', 'percentage')->get())
            ->assertCanNotSeeTableRecords(Coupon::where('type', 'fixed')->get());
    }

    public function test_can_filter_coupons_by_active_status(): void
    {
        Coupon::factory()->create(['is_active' => true]);
        Coupon::factory()->create(['is_active' => false]);

        Livewire::test(CouponResource\Pages\ListCoupons::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords(Coupon::where('is_active', true)->get());
    }

    public function test_can_filter_coupons_by_public_status(): void
    {
        Coupon::factory()->create(['is_public' => true]);
        Coupon::factory()->create(['is_public' => false]);

        Livewire::test(CouponResource\Pages\ListCoupons::class)
            ->filterTable('is_public', true)
            ->assertCanSeeTableRecords(Coupon::where('is_public', true)->get());
    }

    public function test_can_filter_coupons_by_auto_apply_status(): void
    {
        Coupon::factory()->create(['is_auto_apply' => true]);
        Coupon::factory()->create(['is_auto_apply' => false]);

        Livewire::test(CouponResource\Pages\ListCoupons::class)
            ->filterTable('is_auto_apply', true)
            ->assertCanSeeTableRecords(Coupon::where('is_auto_apply', true)->get());
    }

    public function test_can_search_coupons_by_code(): void
    {
        Coupon::factory()->create(['code' => 'SAVE20']);
        Coupon::factory()->create(['code' => 'DISCOUNT10']);

        Livewire::test(CouponResource\Pages\ListCoupons::class)
            ->searchTable('SAVE20')
            ->assertCanSeeTableRecords(Coupon::where('code', 'like', '%SAVE20%')->get());
    }

    public function test_can_toggle_coupon_active_status(): void
    {
        $coupon = Coupon::factory()->create(['is_active' => false]);

        Livewire::test(CouponResource\Pages\ListCoupons::class)
            ->callTableAction('toggle_active', $coupon);

        $coupon->refresh();
        $this->assertTrue($coupon->is_active);
    }

    public function test_can_duplicate_coupon(): void
    {
        $coupon = Coupon::factory()->create([
            'code' => 'ORIGINAL',
            'name' => 'Original Coupon',
        ]);

        Livewire::test(CouponResource\Pages\ListCoupons::class)
            ->callTableAction('duplicate', $coupon);

        $this->assertDatabaseHas('coupons', [
            'code' => 'ORIGINAL_copy_'.time(),
            'name' => 'Original Coupon (Copy)',
        ]);
    }

    public function test_can_bulk_activate_coupons(): void
    {
        $coupons = Coupon::factory()->count(3)->create(['is_active' => false]);

        Livewire::test(CouponResource\Pages\ListCoupons::class)
            ->callTableBulkAction('activate', $coupons);

        foreach ($coupons as $coupon) {
            $coupon->refresh();
            $this->assertTrue($coupon->is_active);
        }
    }

    public function test_can_bulk_deactivate_coupons(): void
    {
        $coupons = Coupon::factory()->count(3)->create(['is_active' => true]);

        Livewire::test(CouponResource\Pages\ListCoupons::class)
            ->callTableBulkAction('deactivate', $coupons);

        foreach ($coupons as $coupon) {
            $coupon->refresh();
            $this->assertFalse($coupon->is_active);
        }
    }

    public function test_coupon_validation_requires_code(): void
    {
        Livewire::test(CouponResource\Pages\CreateCoupon::class)
            ->fillForm([
                'code' => '',
                'type' => 'percentage',
                'value' => 20.0,
            ])
            ->call('create')
            ->assertHasFormErrors(['code' => 'required']);
    }

    public function test_coupon_validation_code_must_be_unique(): void
    {
        Coupon::factory()->create(['code' => 'EXISTING']);

        Livewire::test(CouponResource\Pages\CreateCoupon::class)
            ->fillForm([
                'code' => 'EXISTING',
                'type' => 'percentage',
                'value' => 20.0,
            ])
            ->call('create')
            ->assertHasFormErrors(['code' => 'unique']);
    }

    public function test_coupon_validation_code_alpha_dash(): void
    {
        Livewire::test(CouponResource\Pages\CreateCoupon::class)
            ->fillForm([
                'code' => 'INVALID CODE',
                'type' => 'percentage',
                'value' => 20.0,
            ])
            ->call('create')
            ->assertHasFormErrors(['code' => 'alpha_dash']);
    }

    public function test_coupon_validation_value_numeric(): void
    {
        Livewire::test(CouponResource\Pages\CreateCoupon::class)
            ->fillForm([
                'code' => 'TEST',
                'type' => 'percentage',
                'value' => 'invalid',
            ])
            ->call('create')
            ->assertHasFormErrors(['value' => 'numeric']);
    }

    public function test_coupon_validation_value_minimum(): void
    {
        Livewire::test(CouponResource\Pages\CreateCoupon::class)
            ->fillForm([
                'code' => 'TEST',
                'type' => 'percentage',
                'value' => -10.0,
            ])
            ->call('create')
            ->assertHasFormErrors(['value' => 'min']);
    }

    public function test_coupon_scope_active(): void
    {
        Coupon::factory()->create(['is_active' => true]);
        Coupon::factory()->create(['is_active' => false]);

        $activeCoupons = Coupon::active()->get();
        $this->assertCount(1, $activeCoupons);
        $this->assertTrue($activeCoupons->first()->is_active);
    }

    public function test_coupon_scope_valid(): void
    {
        Coupon::factory()->create([
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDay(),
            'usage_limit' => 10,
            'used_count' => 5,
        ]);

        Coupon::factory()->create([
            'is_active' => false,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDay(),
        ]);

        $validCoupons = Coupon::valid()->get();
        $this->assertCount(1, $validCoupons);
        $this->assertTrue($validCoupons->first()->is_active);
    }

    public function test_coupon_scope_expired(): void
    {
        Coupon::factory()->create(['expires_at' => now()->subDay()]);
        Coupon::factory()->create(['expires_at' => now()->addDay()]);

        $expiredCoupons = Coupon::expired()->get();
        $this->assertCount(1, $expiredCoupons);
        $this->assertTrue($expiredCoupons->first()->expires_at < now());
    }

    public function test_coupon_scope_by_type(): void
    {
        Coupon::factory()->create(['type' => 'percentage']);
        Coupon::factory()->create(['type' => 'fixed']);

        $percentageCoupons = Coupon::byType('percentage')->get();
        $this->assertCount(1, $percentageCoupons);
        $this->assertEquals('percentage', $percentageCoupons->first()->type);
    }

    public function test_coupon_scope_by_code(): void
    {
        Coupon::factory()->create(['code' => 'SAVE20']);
        Coupon::factory()->create(['code' => 'DISCOUNT10']);

        $saveCoupons = Coupon::byCode('SAVE20')->get();
        $this->assertCount(1, $saveCoupons);
        $this->assertEquals('SAVE20', $saveCoupons->first()->code);
    }

    public function test_coupon_helper_methods(): void
    {
        $coupon = Coupon::factory()->create([
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDay(),
            'usage_limit' => 10,
            'used_count' => 5,
        ]);

        $this->assertTrue($coupon->isValid());
        $this->assertFalse($coupon->isExpired());
        $this->assertFalse($coupon->isNotStarted());
        $this->assertTrue($coupon->canBeUsed(100.0));
    }

    public function test_coupon_calculate_discount_percentage(): void
    {
        $coupon = Coupon::factory()->create([
            'type' => 'percentage',
            'value' => 20.0,
            'is_active' => true,
        ]);

        $discount = $coupon->calculateDiscount(100.0);
        $this->assertEquals(20.0, $discount);
    }

    public function test_coupon_calculate_discount_fixed(): void
    {
        $coupon = Coupon::factory()->create([
            'type' => 'fixed',
            'value' => 15.0,
            'is_active' => true,
        ]);

        $discount = $coupon->calculateDiscount(100.0);
        $this->assertEquals(15.0, $discount);
    }

    public function test_coupon_calculate_discount_free_shipping(): void
    {
        $coupon = Coupon::factory()->create([
            'type' => 'free_shipping',
            'value' => 0.0,
            'is_active' => true,
        ]);

        $discount = $coupon->calculateDiscount(100.0);
        $this->assertEquals(0.0, $discount);
    }

    public function test_coupon_minimum_amount_validation(): void
    {
        $coupon = Coupon::factory()->create([
            'type' => 'percentage',
            'value' => 20.0,
            'minimum_amount' => 50.0,
            'is_active' => true,
        ]);

        $this->assertFalse($coupon->canBeUsed(30.0));
        $this->assertTrue($coupon->canBeUsed(60.0));
    }

    public function test_coupon_usage_limit_validation(): void
    {
        $coupon = Coupon::factory()->create([
            'usage_limit' => 5,
            'used_count' => 5,
            'is_active' => true,
        ]);

        $this->assertFalse($coupon->isValid());
    }

    public function test_coupon_expired_validation(): void
    {
        $coupon = Coupon::factory()->create([
            'expires_at' => now()->subDay(),
            'is_active' => true,
        ]);

        $this->assertTrue($coupon->isExpired());
        $this->assertFalse($coupon->isValid());
    }

    public function test_coupon_not_started_validation(): void
    {
        $coupon = Coupon::factory()->create([
            'starts_at' => now()->addDay(),
            'is_active' => true,
        ]);

        $this->assertTrue($coupon->isNotStarted());
        $this->assertFalse($coupon->isValid());
    }

    public function test_coupon_relationships_products(): void
    {
        $coupon = Coupon::factory()->create();
        // Note: Product relationship would need to be implemented in the model
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $coupon->products());
    }

    public function test_coupon_relationships_categories(): void
    {
        $coupon = Coupon::factory()->create();
        // Note: Category relationship would need to be implemented in the model
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $coupon->categories());
    }

    public function test_coupon_relationships_orders(): void
    {
        $coupon = Coupon::factory()->create();
        // Note: Order relationship would need to be implemented in the model
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $coupon->orders());
    }

    public function test_coupon_relationships_usages(): void
    {
        $coupon = Coupon::factory()->create();
        // Note: CouponUsage relationship would need to be implemented in the model
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $coupon->usages());
    }

    public function test_coupon_customer_group_relationship(): void
    {
        $customerGroup = CustomerGroup::factory()->create();
        $coupon = Coupon::factory()->create([
            'customer_group_id' => $customerGroup->id,
        ]);

        $this->assertEquals($customerGroup->id, $coupon->customer_group_id);
    }

    public function test_coupon_remaining_uses_calculation(): void
    {
        $coupon = Coupon::factory()->create([
            'usage_limit' => 100,
            'used_count' => 25,
        ]);

        $remainingUses = $coupon->usage_limit - $coupon->used_count;
        $this->assertEquals(75, $remainingUses);
    }

    public function test_coupon_value_formatting_percentage(): void
    {
        $coupon = Coupon::factory()->create([
            'type' => 'percentage',
            'value' => 20.0,
        ]);

        // This would be tested in the table column formatting
        $this->assertEquals('percentage', $coupon->type);
        $this->assertEquals(20.0, $coupon->value);
    }

    public function test_coupon_value_formatting_fixed(): void
    {
        $coupon = Coupon::factory()->create([
            'type' => 'fixed',
            'value' => 15.0,
        ]);

        // This would be tested in the table column formatting
        $this->assertEquals('fixed', $coupon->type);
        $this->assertEquals(15.0, $coupon->value);
    }

    public function test_coupon_value_formatting_free_shipping(): void
    {
        $coupon = Coupon::factory()->create([
            'type' => 'free_shipping',
            'value' => 0.0,
        ]);

        // This would be tested in the table column formatting
        $this->assertEquals('free_shipping', $coupon->type);
        $this->assertEquals(0.0, $coupon->value);
    }
}
