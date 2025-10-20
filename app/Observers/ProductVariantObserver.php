<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\ProductVariant;
use App\Support\Cache\CacheInvalidator;

final class ProductVariantObserver
{
    /**
     * @var array<int, string>
     */
    private const PRICE_COLUMNS = [
        'price',
        'compare_price',
        'cost_price',
        'wholesale_price',
        'member_price',
        'promotional_price',
    ];

    /**
     * @var array<int, string>
     */
    private const STOCK_COLUMNS = [
        'stock_quantity',
        'reserved_quantity',
        'available_quantity',
        'sold_quantity',
    ];

    public function created(ProductVariant $variant): void
    {
        $this->flushVariantCaches($variant);
    }

    public function updated(ProductVariant $variant): void
    {
        if (! $this->hasRelevantChanges($variant)) {
            return;
        }

        $this->flushVariantCaches($variant);
    }

    public function deleted(ProductVariant $variant): void
    {
        $this->flushVariantCaches($variant);
    }

    public function restored(ProductVariant $variant): void
    {
        $this->flushVariantCaches($variant);
    }

    public function forceDeleted(ProductVariant $variant): void
    {
        $this->flushVariantCaches($variant);
    }

    private function flushVariantCaches(ProductVariant $variant): void
    {
        app(CacheInvalidator::class)->variantChanged($variant);
    }

    private function hasRelevantChanges(ProductVariant $variant): bool
    {
        $columns = array_merge(self::PRICE_COLUMNS, self::STOCK_COLUMNS);

        foreach ($columns as $column) {
            if ($variant->wasChanged($column)) {
                return true;
            }
        }

        return false;
    }
}
