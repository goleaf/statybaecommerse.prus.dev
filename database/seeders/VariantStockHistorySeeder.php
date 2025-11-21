<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProductVariant;
use App\Models\VariantStockHistory;
use Illuminate\Database\Seeder;

class VariantStockHistorySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📦 Starting Variant Stock History seeding...');

        // Get or create product variants
        $variants = $this->getOrCreateVariants();

        if ($variants->isEmpty()) {
            $this->command->error('❌ No product variants available. Please run ProductVariantSeeder first.');
            return;
        }

        // Create stock history for each variant
        $this->createStockHistories($variants);

        $this->command->info('✅ VariantStockHistory seeded successfully!');
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

    private function createStockHistories($variants): void
    {
        $this->command->info('Creating stock history records...');

        $totalCreated = 0;
        $totalSkipped = 0;

        $variants->each(function (ProductVariant $variant) use (&$totalCreated, &$totalSkipped): void {
            // Check if this variant already has stock history
            $existingCount = VariantStockHistory::where('variant_id', $variant->id)->count();

            if ($existingCount >= 6) {
                $this->command->info("✓ Variant #{$variant->id} already has {$existingCount} stock history records");
                $totalSkipped++;
                return;
            }

            $targetCount = fake()->numberBetween(6, 12);
            $needed = max(0, $targetCount - $existingCount);

            if ($needed === 0) {
                $totalSkipped++;
                return;
            }

            // Create stock histories with explicit variant_id or using for() with relationship name
            $created = VariantStockHistory::factory()
                ->count($needed)
                ->for($variant, 'variant')  // Explicitly specify the relationship name
                ->create();

            $totalCreated += $created->count();
        });

        if ($totalCreated > 0) {
            $this->command->info("✓ Created {$totalCreated} new stock history records");
        }

        if ($totalSkipped > 0) {
            $this->command->info("✓ Skipped {$totalSkipped} variants with sufficient history");
        }
    }
}
