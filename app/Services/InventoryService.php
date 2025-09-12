<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Inventory;
use App\Models\VariantInventory;
use App\Models\Location;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class InventoryService
{
    public function adjustProductStock(Product $product, int $quantity, string $reason = 'adjustment', ?string $notes = null): bool
    {
        if (!$product->manage_stock) {
            return true;
        }

        try {
            DB::beginTransaction();

            $oldQuantity = $product->stock_quantity;
            $newQuantity = max(0, $oldQuantity + $quantity);
            
            $product->update(['stock_quantity' => $newQuantity]);

            // Log the adjustment
            $this->logStockAdjustment($product, $oldQuantity, $newQuantity, $reason, $notes);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to adjust product stock', [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function adjustVariantStock(ProductVariant $variant, int $quantity, string $reason = 'adjustment', ?string $notes = null): bool
    {
        if (!$variant->is_tracked) {
            return true;
        }

        try {
            DB::beginTransaction();

            $oldQuantity = $variant->stock;
            $newQuantity = max(0, $oldQuantity + $quantity);
            
            $variant->update(['stock' => $newQuantity]);

            // Log the adjustment
            $this->logVariantStockAdjustment($variant, $oldQuantity, $newQuantity, $reason, $notes);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to adjust variant stock', [
                'variant_id' => $variant->id,
                'quantity' => $quantity,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function reserveProductStock(Product $product, int $quantity): bool
    {
        if (!$product->manage_stock) {
            return true;
        }

        if ($product->availableQuantity() < $quantity) {
            return false;
        }

        // For simple products, we don't have reservations yet
        // This would be implemented when we add reservation system
        return true;
    }

    public function releaseProductStock(Product $product, int $quantity): void
    {
        if (!$product->manage_stock) {
            return;
        }

        // For simple products, we don't have reservations yet
        // This would be implemented when we add reservation system
    }

    public function getLowStockProducts(int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return Product::where('manage_stock', true)
            ->whereRaw('stock_quantity <= low_stock_threshold')
            ->where('stock_quantity', '>', 0)
            ->with(['brand', 'categories'])
            ->orderBy('stock_quantity', 'asc')
            ->limit($limit)
            ->get();
    }

    public function getOutOfStockProducts(int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return Product::where('manage_stock', true)
            ->where('stock_quantity', '<=', 0)
            ->with(['brand', 'categories'])
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getInventorySummary(): array
    {
        $totalProducts = Product::count();
        $trackedProducts = Product::where('manage_stock', true)->count();
        $lowStockProducts = Product::where('manage_stock', true)
            ->whereRaw('stock_quantity <= low_stock_threshold')
            ->where('stock_quantity', '>', 0)
            ->count();
        $outOfStockProducts = Product::where('manage_stock', true)
            ->where('stock_quantity', '<=', 0)
            ->count();
        $inStockProducts = Product::where('manage_stock', true)
            ->whereRaw('stock_quantity > low_stock_threshold')
            ->count();

        return [
            'total_products' => $totalProducts,
            'tracked_products' => $trackedProducts,
            'in_stock' => $inStockProducts,
            'low_stock' => $lowStockProducts,
            'out_of_stock' => $outOfStockProducts,
            'not_tracked' => $totalProducts - $trackedProducts,
        ];
    }

    public function bulkAdjustStock(array $adjustments): array
    {
        $results = [];
        
        foreach ($adjustments as $adjustment) {
            $productId = $adjustment['product_id'] ?? null;
            $quantity = $adjustment['quantity'] ?? 0;
            $reason = $adjustment['reason'] ?? 'bulk_adjustment';
            $notes = $adjustment['notes'] ?? null;

            if (!$productId) {
                $results[] = [
                    'product_id' => $productId,
                    'success' => false,
                    'error' => 'Product ID is required',
                ];
                continue;
            }

            $product = Product::find($productId);
            if (!$product) {
                $results[] = [
                    'product_id' => $productId,
                    'success' => false,
                    'error' => 'Product not found',
                ];
                continue;
            }

            $success = $this->adjustProductStock($product, $quantity, $reason, $notes);
            $results[] = [
                'product_id' => $productId,
                'success' => $success,
                'error' => $success ? null : 'Failed to adjust stock',
            ];
        }

        return $results;
    }

    private function logStockAdjustment(Product $product, int $oldQuantity, int $newQuantity, string $reason, ?string $notes): void
    {
        // Log to activity log
        activity()
            ->performedOn($product)
            ->withProperties([
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'adjustment' => $newQuantity - $oldQuantity,
                'reason' => $reason,
                'notes' => $notes,
            ])
            ->log('Stock adjusted');
    }

    private function logVariantStockAdjustment(ProductVariant $variant, int $oldQuantity, int $newQuantity, string $reason, ?string $notes): void
    {
        // Log to activity log
        activity()
            ->performedOn($variant)
            ->withProperties([
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'adjustment' => $newQuantity - $oldQuantity,
                'reason' => $reason,
                'notes' => $notes,
            ])
            ->log('Variant stock adjusted');
    }
}
