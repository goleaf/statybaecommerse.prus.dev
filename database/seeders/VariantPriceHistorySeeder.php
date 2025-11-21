<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProductVariant;
use App\Models\VariantPriceHistory;
use Illuminate\Database\Seeder;

final class VariantPriceHistorySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🏷️ Starting Variant Price History seeding...');

        // Get or create product variants
        $variants = $this->getOrCreateVariants();

        if ($variants->isEmpty()) {
            $this->command->error('❌ No product variants available. Please run ProductVariantSeeder first.');
            return;
        }

        // Create price history for each variant
        $this->createPriceHistories($variants);

        $this->command->info('✅ Created variant price history records successfully!');
    }

    private function getOrCreateVariants()
    {
        $existingCount = ProductVariant::count();

        if ($existingCount >= 10) {
            $this->command->info("✓ Using {$existingCount} existing product variants");
            return ProductVariant::limit(10)->get();
        }

        $needed = 10 - $existingCount;
        $this->command->info("Creating {$needed} product variants...");

        ProductVariant::factory()->count($needed)->create();

        return ProductVariant::limit(10)->get();
    }

    private function createPriceHistories($variants): void
    {
        $this->command->info('Creating price history records...');

        $totalCreated = 0;

        $variants->each(function (ProductVariant $variant) use (&$totalCreated): void {
            // Check if this variant already has price history
            $existingCount = VariantPriceHistory::where('variant_id', $variant->id)->count();

            if ($existingCount >= 3) {
                $this->command->info("✓ Variant #{$variant->id} already has {$existingCount} price history records");
                return;
            }

            $needed = max(0, fake()->numberBetween(3, 5) - $existingCount);

            if ($needed === 0) {
                return;
            }

            // Create price histories with explicit variant_id
            for ($i = 0; $i < $needed; $i++) {
                VariantPriceHistory::factory()->create([
                    'variant_id' => $variant->id,
                ]);
                $totalCreated++;
            }
        });

        if ($totalCreated > 0) {
            $this->command->info("✓ Created {$totalCreated} new price history records");
        }
    }
}
