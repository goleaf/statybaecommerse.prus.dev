<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Discount;
use App\Models\DiscountCode;
use App\Models\DiscountRedemption;
use Illuminate\Database\Seeder;

final class DiscountRedemptionSeeder extends Seeder
{
    public function run(): void
    {
        Discount::query()
            ->with(['codes', 'codes.redemptions'])
            ->get()
            ->each(function (Discount $discount): void {
                if ($discount->codes->isEmpty()) {
                    $discount->codes()->saveMany(
                        DiscountCode::factory()
                            ->count(3)
                            ->withDiscount($discount)
                            ->make()
                    );
                }

                $discount->codes->each(fn (DiscountCode $code) => $this->seedRedemptions($code));
            });

        $this->seedScenarioBuckets();
    }

    private function seedRedemptions(DiscountCode $code): void
    {
        $target = 5;
        $existing = $code->redemptions()->count();
        $missing = $target - $existing;

        if ($missing <= 0) {
            return;
        }

        $code->redemptions()->saveMany(
            DiscountRedemption::factory()
                ->count($missing)
                ->forDiscount($code->discount)
                ->forCode($code)
                ->make()
        );
    }

    private function seedScenarioBuckets(): void
    {
        $discountCode = DiscountCode::factory()->withDiscount()->create();

        DiscountRedemption::factory()
            ->highValue()
            ->count(5)
            ->forDiscount($discountCode->discount)
            ->forCode($discountCode)
            ->eur()
            ->create();

        DiscountRedemption::factory()
            ->recent()
            ->count(10)
            ->redeemed()
            ->forDiscount($discountCode->discount)
            ->forCode($discountCode)
            ->create();

        DiscountRedemption::factory()
            ->pending()
            ->count(8)
            ->forDiscount($discountCode->discount)
            ->forCode($discountCode)
            ->create();

        DiscountRedemption::factory()
            ->expired()
            ->count(6)
            ->state([
                'redeemed_at' => fake()->dateTimeBetween('-60 days', '-30 days'),
            ])
            ->forDiscount($discountCode->discount)
            ->forCode($discountCode)
            ->create();

        DiscountRedemption::factory()
            ->cancelled()
            ->count(4)
            ->forDiscount($discountCode->discount)
            ->forCode($discountCode)
            ->create();
    }
}
