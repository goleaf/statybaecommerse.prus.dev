<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\DiscountRedemptionResource\Pages\CreateDiscountRedemption;
use App\Filament\Resources\DiscountRedemptionResource\Pages\EditDiscountRedemption;
use App\Filament\Resources\DiscountRedemptionResource\Pages\ListDiscountRedemptions;
use App\Filament\Resources\DiscountRedemptionResource\Pages\ViewDiscountRedemption;
use App\Models\Discount;
use App\Models\DiscountCode;
use App\Models\DiscountRedemption;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

final class DiscountRedemptionResourceTest extends TestCase
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

    public function test_can_load_discount_redemption_list_page(): void
    {
        $discount = Discount::factory()->create();
        $discountCode = DiscountCode::factory()->create(['discount_id' => $discount->id]);
        $user = User::factory()->create();
        $discountRedemptions = DiscountRedemption::factory()->count(5)->create([
            'discount_code_id' => $discountCode->id,
            'user_id' => $user->id,
        ]);

        Livewire::test(ListDiscountRedemptions::class)
            ->assertOk()
            ->assertCanSeeTableRecords($discountRedemptions);
    }

    public function test_can_create_discount_redemption(): void
    {
        $discount = Discount::factory()->create();
        $discountCode = DiscountCode::factory()->create(['discount_id' => $discount->id]);
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $newRedemptionData = DiscountRedemption::factory()->make([
            'discount_code_id' => $discountCode->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
            'discount_amount' => 15.50,
            'redeemed_at' => now(),
        ]);

        Livewire::test(CreateDiscountRedemption::class)
            ->fillForm([
                'discount_code_id' => $newRedemptionData->discount_code_id,
                'user_id' => $newRedemptionData->user_id,
                'order_id' => $newRedemptionData->order_id,
                'discount_amount' => $newRedemptionData->discount_amount,
                'redeemed_at' => $newRedemptionData->redeemed_at,
            ])
            ->call('create')
            ->assertNotified();

        $this->assertDatabaseHas('discount_redemptions', [
            'discount_code_id' => $newRedemptionData->discount_code_id,
            'user_id' => $newRedemptionData->user_id,
            'order_id' => $newRedemptionData->order_id,
            'discount_amount' => $newRedemptionData->discount_amount,
        ]);
    }

    public function test_can_edit_discount_redemption(): void
    {
        $discount = Discount::factory()->create();
        $discountCode = DiscountCode::factory()->create(['discount_id' => $discount->id]);
        $user = User::factory()->create();
        $redemption = DiscountRedemption::factory()->create([
            'discount_code_id' => $discountCode->id,
            'user_id' => $user->id,
            'discount_amount' => 10.00,
        ]);

        Livewire::test(EditDiscountRedemption::class, [
            'record' => $redemption->id,
        ])
            ->fillForm([
                'discount_amount' => 20.00,
            ])
            ->call('save')
            ->assertNotified();

        $this->assertDatabaseHas('discount_redemptions', [
            'id' => $redemption->id,
            'discount_amount' => 20.00,
        ]);
    }

    public function test_can_view_discount_redemption(): void
    {
        $discount = Discount::factory()->create();
        $discountCode = DiscountCode::factory()->create(['discount_id' => $discount->id]);
        $user = User::factory()->create();
        $redemption = DiscountRedemption::factory()->create([
            'discount_code_id' => $discountCode->id,
            'user_id' => $user->id,
        ]);

        Livewire::test(ViewDiscountRedemption::class, [
            'record' => $redemption->id,
        ])
            ->assertOk();
    }

    public function test_discount_redemption_relationships(): void
    {
        $discount = Discount::factory()->create();
        $discountCode = DiscountCode::factory()->create(['discount_id' => $discount->id]);
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $redemption = DiscountRedemption::factory()->create([
            'discount_code_id' => $discountCode->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
        ]);

        $this->assertEquals($discountCode->id, $redemption->discountCode->id);
        $this->assertEquals($user->id, $redemption->user->id);
        $this->assertEquals($order->id, $redemption->order->id);
    }
}
