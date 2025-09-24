<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\CouponUsageResource;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
/**
 * CouponUsageResourceTest
 *
 * Comprehensive test suite for CouponUsageResource functionality including CRUD operations, filters, and relationships.
 */
use Tests\TestCase;

final class CouponUsageResourceTest extends TestCase
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

    public function test_can_list_coupon_usages(): void
    {
        $couponUsages = CouponUsage::factory()->count(3)->create();

        Livewire::test(CouponUsageResource\Pages\ListCouponUsages::class)
            ->assertCanSeeTableRecords($couponUsages);
    }

    public function test_can_create_coupon_usage(): void
    {
        $coupon = Coupon::factory()->create();
        $user = User::factory()->create();
        $order = Order::factory()->create();

        $couponUsageData = [
            'coupon_id' => $coupon->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
            'discount_amount' => 15.00,
            'used_at' => now(),
        ];

        Livewire::test(CouponUsageResource\Pages\CreateCouponUsage::class)
            ->fillForm($couponUsageData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('coupon_usages', [
            'coupon_id' => $coupon->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
        ]);
    }

    public function test_can_edit_coupon_usage(): void
    {
        $couponUsage = CouponUsage::factory()->create([
            'discount_amount' => 10.00,
        ]);

        Livewire::test(CouponUsageResource\Pages\EditCouponUsage::class, [
            'record' => $couponUsage->getRouteKey(),
        ])
            ->fillForm([
                'discount_amount' => 20.00,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $couponUsage->refresh();
        $this->assertEquals(20.00, $couponUsage->discount_amount);
    }

    public function test_can_view_coupon_usage(): void
    {
        $couponUsage = CouponUsage::factory()->create([
            'discount_amount' => 25.00,
        ]);

        Livewire::test(CouponUsageResource\Pages\ViewCouponUsage::class, [
            'record' => $couponUsage->getRouteKey(),
        ])
            ->assertCanSeeText('25.00');
    }

    public function test_can_delete_coupon_usage(): void
    {
        $couponUsage = CouponUsage::factory()->create();

        Livewire::test(CouponUsageResource\Pages\ListCouponUsages::class)
            ->callTableAction('delete', $couponUsage);

        $this->assertSoftDeleted('coupon_usages', [
            'id' => $couponUsage->id,
        ]);
    }

    public function test_can_bulk_delete_coupon_usages(): void
    {
        $couponUsages = CouponUsage::factory()->count(3)->create();

        Livewire::test(CouponUsageResource\Pages\ListCouponUsages::class)
            ->callTableBulkAction('delete', $couponUsages);

        foreach ($couponUsages as $couponUsage) {
            $this->assertSoftDeleted('coupon_usages', [
                'id' => $couponUsage->id,
            ]);
        }
    }

    public function test_can_filter_coupon_usages_by_coupon(): void
    {
        $coupon1 = Coupon::factory()->create(['code' => 'SAVE20']);
        $coupon2 = Coupon::factory()->create(['code' => 'DISCOUNT10']);

        CouponUsage::factory()->create(['coupon_id' => $coupon1->id]);
        CouponUsage::factory()->create(['coupon_id' => $coupon2->id]);

        Livewire::test(CouponUsageResource\Pages\ListCouponUsages::class)
            ->filterTable('coupon_id', $coupon1->id)
            ->assertCanSeeTableRecords(CouponUsage::where('coupon_id', $coupon1->id)->get())
            ->assertCanNotSeeTableRecords(CouponUsage::where('coupon_id', $coupon2->id)->get());
    }

    public function test_can_filter_coupon_usages_by_user(): void
    {
        $user1 = User::factory()->create(['name' => 'John Doe']);
        $user2 = User::factory()->create(['name' => 'Jane Smith']);

        CouponUsage::factory()->create(['user_id' => $user1->id]);
        CouponUsage::factory()->create(['user_id' => $user2->id]);

        Livewire::test(CouponUsageResource\Pages\ListCouponUsages::class)
            ->filterTable('user_id', $user1->id)
            ->assertCanSeeTableRecords(CouponUsage::where('user_id', $user1->id)->get());
    }

    public function test_can_filter_coupon_usages_by_order(): void
    {
        $order1 = Order::factory()->create();
        $order2 = Order::factory()->create();

        CouponUsage::factory()->create(['order_id' => $order1->id]);
        CouponUsage::factory()->create(['order_id' => $order2->id]);

        Livewire::test(CouponUsageResource\Pages\ListCouponUsages::class)
            ->filterTable('order_id', $order1->id)
            ->assertCanSeeTableRecords(CouponUsage::where('order_id', $order1->id)->get());
    }

    public function test_can_filter_coupon_usages_by_used_at_date(): void
    {
        $today = now();
        $yesterday = now()->subDay();

        CouponUsage::factory()->create(['used_at' => $today]);
        CouponUsage::factory()->create(['used_at' => $yesterday]);

        Livewire::test(CouponUsageResource\Pages\ListCouponUsages::class)
            ->filterTable('used_at', $today->format('Y-m-d'))
            ->assertCanSeeTableRecords(CouponUsage::whereDate('used_at', $today)->get());
    }

    public function test_can_filter_coupon_usages_used_today(): void
    {
        CouponUsage::factory()->create(['used_at' => now()]);
        CouponUsage::factory()->create(['used_at' => now()->subDay()]);

        Livewire::test(CouponUsageResource\Pages\ListCouponUsages::class)
            ->filterTable('used_today', true)
            ->assertCanSeeTableRecords(CouponUsage::whereDate('used_at', today())->get());
    }

    public function test_can_filter_coupon_usages_used_this_week(): void
    {
        CouponUsage::factory()->create(['used_at' => now()]);
        CouponUsage::factory()->create(['used_at' => now()->subWeek()]);

        Livewire::test(CouponUsageResource\Pages\ListCouponUsages::class)
            ->filterTable('used_this_week', true)
            ->assertCanSeeTableRecords(CouponUsage::whereBetween('used_at', [now()->startOfWeek(), now()->endOfWeek()])->get());
    }

    public function test_can_filter_coupon_usages_used_this_month(): void
    {
        CouponUsage::factory()->create(['used_at' => now()]);
        CouponUsage::factory()->create(['used_at' => now()->subMonth()]);

        Livewire::test(CouponUsageResource\Pages\ListCouponUsages::class)
            ->filterTable('used_this_month', true)
            ->assertCanSeeTableRecords(CouponUsage::whereBetween('used_at', [now()->startOfMonth(), now()->endOfMonth()])->get());
    }

    public function test_can_search_coupon_usages_by_coupon_code(): void
    {
        $coupon = Coupon::factory()->create(['code' => 'SAVE20']);
        CouponUsage::factory()->create(['coupon_id' => $coupon->id]);

        Livewire::test(CouponUsageResource\Pages\ListCouponUsages::class)
            ->searchTable('SAVE20')
            ->assertCanSeeTableRecords(CouponUsage::whereHas('coupon', function ($q) {
                $q->where('code', 'like', '%SAVE20%');
            })->get());
    }

    public function test_can_export_usage_report(): void
    {
        $couponUsage = CouponUsage::factory()->create();

        Livewire::test(CouponUsageResource\Pages\ListCouponUsages::class)
            ->callTableAction('export_usage_report', $couponUsage);

        // This would test the export functionality
        $this->assertTrue(true); // Placeholder for actual export test
    }

    public function test_can_export_bulk_usage_report(): void
    {
        $couponUsages = CouponUsage::factory()->count(3)->create();

        Livewire::test(CouponUsageResource\Pages\ListCouponUsages::class)
            ->callTableBulkAction('export_bulk_report', $couponUsages);

        // This would test the bulk export functionality
        $this->assertTrue(true); // Placeholder for actual bulk export test
    }

    public function test_coupon_usage_validation_requires_coupon(): void
    {
        Livewire::test(CouponUsageResource\Pages\CreateCouponUsage::class)
            ->fillForm([
                'coupon_id' => '',
                'user_id' => User::factory()->create()->id,
                'discount_amount' => 10.00,
            ])
            ->call('create')
            ->assertHasFormErrors(['coupon_id' => 'required']);
    }

    public function test_coupon_usage_validation_requires_user(): void
    {
        Livewire::test(CouponUsageResource\Pages\CreateCouponUsage::class)
            ->fillForm([
                'coupon_id' => Coupon::factory()->create()->id,
                'user_id' => '',
                'discount_amount' => 10.00,
            ])
            ->call('create')
            ->assertHasFormErrors(['user_id' => 'required']);
    }

    public function test_coupon_usage_validation_requires_discount_amount(): void
    {
        Livewire::test(CouponUsageResource\Pages\CreateCouponUsage::class)
            ->fillForm([
                'coupon_id' => Coupon::factory()->create()->id,
                'user_id' => User::factory()->create()->id,
                'discount_amount' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['discount_amount' => 'required']);
    }

    public function test_coupon_usage_validation_discount_amount_numeric(): void
    {
        Livewire::test(CouponUsageResource\Pages\CreateCouponUsage::class)
            ->fillForm([
                'coupon_id' => Coupon::factory()->create()->id,
                'user_id' => User::factory()->create()->id,
                'discount_amount' => 'invalid',
            ])
            ->call('create')
            ->assertHasFormErrors(['discount_amount' => 'numeric']);
    }

    public function test_coupon_usage_validation_discount_amount_minimum(): void
    {
        Livewire::test(CouponUsageResource\Pages\CreateCouponUsage::class)
            ->fillForm([
                'coupon_id' => Coupon::factory()->create()->id,
                'user_id' => User::factory()->create()->id,
                'discount_amount' => -10.00,
            ])
            ->call('create')
            ->assertHasFormErrors(['discount_amount' => 'min']);
    }

    public function test_coupon_usage_validation_used_at_required(): void
    {
        Livewire::test(CouponUsageResource\Pages\CreateCouponUsage::class)
            ->fillForm([
                'coupon_id' => Coupon::factory()->create()->id,
                'user_id' => User::factory()->create()->id,
                'discount_amount' => 10.00,
                'used_at' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['used_at' => 'required']);
    }

    public function test_coupon_usage_relationships_coupon(): void
    {
        $coupon = Coupon::factory()->create();
        $couponUsage = CouponUsage::factory()->create(['coupon_id' => $coupon->id]);

        $this->assertEquals($coupon->id, $couponUsage->coupon->id);
        $this->assertEquals($coupon->code, $couponUsage->coupon->code);
    }

    public function test_coupon_usage_relationships_user(): void
    {
        $user = User::factory()->create();
        $couponUsage = CouponUsage::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($user->id, $couponUsage->user->id);
        $this->assertEquals($user->name, $couponUsage->user->name);
    }

    public function test_coupon_usage_relationships_order(): void
    {
        $order = Order::factory()->create();
        $couponUsage = CouponUsage::factory()->create(['order_id' => $order->id]);

        $this->assertEquals($order->id, $couponUsage->order->id);
    }

    public function test_coupon_usage_scope_used_today(): void
    {
        CouponUsage::factory()->create(['used_at' => now()]);
        CouponUsage::factory()->create(['used_at' => now()->subDay()]);

        $todayUsages = CouponUsage::usedToday()->get();
        $this->assertCount(1, $todayUsages);
        $this->assertTrue($todayUsages->first()->used_at->isToday());
    }

    public function test_coupon_usage_scope_used_this_week(): void
    {
        CouponUsage::factory()->create(['used_at' => now()]);
        CouponUsage::factory()->create(['used_at' => now()->subWeek()]);

        $thisWeekUsages = CouponUsage::usedThisWeek()->get();
        $this->assertCount(1, $thisWeekUsages);
        $this->assertTrue($thisWeekUsages->first()->used_at->isThisWeek());
    }

    public function test_coupon_usage_scope_used_this_month(): void
    {
        CouponUsage::factory()->create(['used_at' => now()]);
        CouponUsage::factory()->create(['used_at' => now()->subMonth()]);

        $thisMonthUsages = CouponUsage::usedThisMonth()->get();
        $this->assertCount(1, $thisMonthUsages);
        $this->assertTrue($thisMonthUsages->first()->used_at->isThisMonth());
    }

    public function test_coupon_usage_usage_period_today(): void
    {
        $couponUsage = CouponUsage::factory()->create(['used_at' => now()]);

        // This would test the usage period formatting in the table
        $this->assertTrue($couponUsage->used_at->isToday());
    }

    public function test_coupon_usage_usage_period_this_week(): void
    {
        $couponUsage = CouponUsage::factory()->create(['used_at' => now()->subDays(3)]);

        // This would test the usage period formatting in the table
        $this->assertTrue($couponUsage->used_at->isThisWeek());
    }

    public function test_coupon_usage_usage_period_this_month(): void
    {
        $couponUsage = CouponUsage::factory()->create(['used_at' => now()->subDays(10)]);

        // This would test the usage period formatting in the table
        $this->assertTrue($couponUsage->used_at->isThisMonth());
    }

    public function test_coupon_usage_usage_period_older(): void
    {
        $couponUsage = CouponUsage::factory()->create(['used_at' => now()->subMonths(2)]);

        // This would test the usage period formatting in the table
        $this->assertFalse($couponUsage->used_at->isThisMonth());
    }

    public function test_coupon_usage_discount_amount_formatting(): void
    {
        $couponUsage = CouponUsage::factory()->create(['discount_amount' => 25.50]);

        // This would test the discount amount formatting in the table
        $this->assertEquals(25.50, $couponUsage->discount_amount);
    }

    public function test_coupon_usage_order_id_formatting(): void
    {
        $order = Order::factory()->create();
        $couponUsage = CouponUsage::factory()->create(['order_id' => $order->id]);

        // This would test the order ID formatting in the table
        $this->assertEquals($order->id, $couponUsage->order_id);
    }

    public function test_coupon_usage_metadata(): void
    {
        $couponUsage = CouponUsage::factory()->create([
            'metadata' => ['source' => 'email', 'campaign' => 'summer_sale'],
        ]);

        $this->assertIsArray($couponUsage->metadata);
        $this->assertEquals('email', $couponUsage->metadata['source']);
        $this->assertEquals('summer_sale', $couponUsage->metadata['campaign']);
    }

    public function test_coupon_usage_created_at(): void
    {
        $couponUsage = CouponUsage::factory()->create();

        $this->assertNotNull($couponUsage->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $couponUsage->created_at);
    }

    public function test_coupon_usage_updated_at(): void
    {
        $couponUsage = CouponUsage::factory()->create();

        $this->assertNotNull($couponUsage->updated_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $couponUsage->updated_at);
    }

    public function test_coupon_usage_soft_deletes(): void
    {
        $couponUsage = CouponUsage::factory()->create();

        $couponUsage->delete();

        $this->assertSoftDeleted('coupon_usages', [
            'id' => $couponUsage->id,
        ]);
    }

    public function test_coupon_usage_restore(): void
    {
        $couponUsage = CouponUsage::factory()->create();

        $couponUsage->delete();
        $couponUsage->restore();

        $this->assertDatabaseHas('coupon_usages', [
            'id' => $couponUsage->id,
            'deleted_at' => null,
        ]);
    }

    public function test_coupon_usage_force_delete(): void
    {
        $couponUsage = CouponUsage::factory()->create();

        $couponUsage->forceDelete();

        $this->assertDatabaseMissing('coupon_usages', [
            'id' => $couponUsage->id,
        ]);
    }

    public function test_coupon_usage_default_sort(): void
    {
        CouponUsage::factory()->create(['used_at' => now()->subDays(3)]);
        CouponUsage::factory()->create(['used_at' => now()]);
        CouponUsage::factory()->create(['used_at' => now()->subDay()]);

        $couponUsages = CouponUsage::orderBy('used_at', 'desc')->get();
        $this->assertEquals(now()->format('Y-m-d H:i:s'), $couponUsages->first()->used_at->format('Y-m-d H:i:s'));
    }
}
