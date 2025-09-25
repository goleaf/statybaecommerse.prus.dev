<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Discount;
use App\Models\DiscountCode;
use Illuminate\Database\Seeder;

final class DiscountCodeSeeder extends Seeder
{
    public function run(): void
    {
        Discount::query()
            ->with('codes')
            ->get()
            ->each(function (Discount $discount): void {
                $this->seedCodesForDiscount($discount);
            });
    }

    private function seedCodesForDiscount(Discount $discount): void
    {
        $targetCount = 20;
        $missing = $targetCount - $discount->codes->count();

        if ($missing <= 0) {
            return;
        }

        $discount->codes()->saveMany(
            DiscountCode::factory()
                ->count($missing)
                ->withDiscount($discount)
                ->make()
        );
    }
}


