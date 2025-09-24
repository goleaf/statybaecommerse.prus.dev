<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProductVariant;
use App\Models\User;
use App\Models\VariantStockHistory;
use Illuminate\Database\Seeder;

class VariantStockHistorySeeder extends Seeder
{
    public function run(): void
    {
        $variants = ProductVariant::take(10)->get();
        $users = User::take(5)->get();

        if ($variants->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No variants or users found. Please run ProductVariantSeeder and UserSeeder first.');

            return;
        }

        $changeTypes = ['increase', 'decrease', 'adjustment', 'reserve', 'unreserve'];
        $changeReasons = ['sale', 'return', 'adjustment', 'reserve', 'unreserve', 'damage', 'theft', 'expired', 'manual'];
        $referenceTypes = ['order', 'reservation'];

        foreach ($variants as $variant) {
            // Create some initial stock history
            $initialQuantity = rand(50, 200);

            VariantStockHistory::create([
                'variant_id' => $variant->id,
                'old_quantity' => 0,
                'new_quantity' => $initialQuantity,
                'quantity_change' => $initialQuantity,
                'change_type' => 'increase',
                'change_reason' => 'manual',
                'changed_by' => $users->random()->id,
                'reference_type' => null,
                'reference_id' => null,
            ]);

            // Create some random stock changes
            $currentQuantity = $initialQuantity;
            for ($i = 0; $i < rand(5, 15); $i++) {
                $changeType = $changeTypes[array_rand($changeTypes)];
                $changeReason = $changeReasons[array_rand($changeReasons)];
                $referenceType = $referenceTypes[array_rand($referenceTypes)];

                $quantityChange = match ($changeType) {
                    'increase' => rand(1, 20),
                    'decrease' => -rand(1, min(10, $currentQuantity)),
                    'adjustment' => rand(-5, 10),
                    'reserve' => -rand(1, min(5, $currentQuantity)),
                    'unreserve' => rand(1, 10),
                    default => 0,
                };

                $newQuantity = max(0, $currentQuantity + $quantityChange);

                VariantStockHistory::create([
                    'variant_id' => $variant->id,
                    'old_quantity' => $currentQuantity,
                    'new_quantity' => $newQuantity,
                    'quantity_change' => $quantityChange,
                    'change_type' => $changeType,
                    'change_reason' => $changeReason,
                    'changed_by' => $users->random()->id,
                    'reference_type' => $changeType === 'increase' || $changeType === 'decrease' ? $referenceType : null,
                    'reference_id' => $changeType === 'increase' || $changeType === 'decrease' ? rand(1, 100) : null,
                ]);

                $currentQuantity = $newQuantity;
            }
        }

        $this->command->info('VariantStockHistory seeded successfully!');
    }
}
