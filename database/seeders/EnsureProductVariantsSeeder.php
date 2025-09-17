<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantInventory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EnsureProductVariantsSeeder extends Seeder
{
    public function run(): void
    {
        $createdVariants = 0;

        Product::query()
            ->withCount('variants')
            ->orderBy('id')
            ->chunkById(500, function ($products) use (&$createdVariants): void {
                foreach ($products as $product) {
                    if ($product->variants_count > 0) {
                        continue;
                    }

                    $baseSku = (string) ($product->sku ?? ('PRD-' . $product->id));
                    $variantSku = $this->uniqueSku($baseSku . '-V1');

                    $variant = new ProductVariant();
                    $variant->product_id = $product->id;
                    $variant->name = $product->name ?: 'Default';
                    $variant->sku = $variantSku;
                    $variant->barcode = $product->barcode ?? null;
                    $variant->price = $product->price ?? 0;
                    $variant->compare_price = $product->compare_price ?? null;
                    $variant->cost_price = $product->cost_price ?? null;
                    $variant->stock_quantity = $product->stock_quantity ?? 0;
                    $variant->weight = $product->weight ?? null;
                    $variant->track_inventory = true;
                    $variant->is_default = true;
                    $variant->is_enabled = true;
                    $variant->attributes = [];
                    $variant->save();

                    // Basic inventory record if table/model exists
                    try {
                        VariantInventory::firstOrCreate(
                            [
                                'variant_id' => $variant->id,
                                'warehouse_code' => 'main',
                            ],
                            [
                                'stock' => $variant->stock_quantity ?? 0,
                                'reserved' => 0,
                                'available' => $variant->stock_quantity ?? 0,
                            ]
                        );
                    } catch (\Throwable $e) {
                        // inventory table may not exist; ignore
                    }

                    // Mark product as variable if supported
                    try {
                        if (property_exists($product, 'type') || \Schema::hasColumn($product->getTable(), 'type')) {
                            $product->type = 'variable';
                            $product->save();
                        }
                    } catch (\Throwable $e) {
                        // best-effort
                    }

                    $createdVariants++;
                }
            });

        $this->command?->info("Created variants for {$createdVariants} products lacking variants.");
    }

    private function uniqueSku(string $candidate): string
    {
        $sku = Str::upper(preg_replace('/[^A-Z0-9\-]/i', '-', $candidate));
        $try = $sku;
        $counter = 1;
        while (ProductVariant::where('sku', $try)->exists()) {
            $try = $sku . '-' . $counter;
            $counter++;
        }
        return $try;
    }
}
