<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Discount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class AllVariantsDiscountSeeder extends BaseSeeder
{
    private const DISCOUNT_SLUG = 'all-variants-auto-10';

    public function run(): void
    {
        if (! Schema::hasTable('discounts') || ! Schema::hasTable('discount_products') || ! Schema::hasTable('product_variants')) {
            $this->command?->warn('Required discount or variant tables are missing; skipping AllVariantsDiscountSeeder.');

            return;
        }

        $productIds = DB::table('product_variants')
            ->whereNotNull('product_id')
            ->distinct()
            ->pluck('product_id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->values();

        if ($productIds->isEmpty()) {
            $this->command?->info('No product variants found; no variant discounts were seeded.');

            return;
        }

        $discount = Discount::withoutGlobalScopes()->updateOrCreate(
            ['slug' => self::DISCOUNT_SLUG],
            [
                'name'            => 'All Variants 10% Auto Discount',
                'description'     => 'Automatically generated 10% discount for all products that have variants.',
                'type'            => 'percentage',
                'value'           => 10.0,
                'is_active'       => true,
                'is_enabled'      => true,
                'status'          => 'active',
                'starts_at'       => now()->subMinute(),
                'ends_at'         => now()->addYear(),
                'usage_limit'     => null,
                'usage_count'     => 0,
                'minimum_amount'  => 0,
                'stacking_policy' => 'single_best',
                'exclusive'       => false,
                'free_shipping'   => false,
            ]
        );

        $now = now();

        $rows = $productIds
            ->map(static fn (int $productId): array => [
                'discount_id' => (int) $discount->getKey(),
                'product_id'  => $productId,
                'created_at'  => $now,
                'updated_at'  => $now,
            ])
            ->all();

        DB::table('discount_products')->upsert(
            $rows,
            ['discount_id', 'product_id'],
            ['updated_at']
        );

        DB::table('discount_products')
            ->where('discount_id', $discount->getKey())
            ->whereNotIn('product_id', $productIds->all())
            ->delete();

        $this->command?->info(sprintf(
            'AllVariantsDiscountSeeder linked discount #%d to %d product(s) that have variants.',
            (int) $discount->getKey(),
            $productIds->count()
        ));
    }
}
