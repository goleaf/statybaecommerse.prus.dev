<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\ProductVariant;
use Livewire\Component;
use Illuminate\Support\Collection;

final class VariantComparisonTable extends Component
{
    public array $variantIds = [];
    public Collection $variantsToCompare;
    public bool $showComparison = false;

    protected $listeners = ['addVariantToComparison', 'removeVariantFromComparison', 'clearComparison'];

    public function mount(): void
    {
        $this->loadVariants();
    }

    public function loadVariants(): void
    {
        if (empty($this->variantIds)) {
            $this->variantsToCompare = collect();
            $this->showComparison = false;
            return;
        }

        $this->variantsToCompare = ProductVariant::whereIn('id', $this->variantIds)
            ->with([
                'product',
                'variantAttributeValues.attribute',
                'variantAttributeValues.attributeValue'
            ])
            ->get();

        $this->showComparison = $this->variantsToCompare->isNotEmpty();
    }

    public function addVariantToComparison(int $variantId): void
    {
        if (!in_array($variantId, $this->variantIds) && count($this->variantIds) < 4) {
            $this->variantIds[] = $variantId;
            $this->loadVariants();
            
            $this->dispatch('comparisonUpdated', [
                'variantIds' => $this->variantIds,
                'count' => count($this->variantIds)
            ]);
        }
    }

    public function removeVariantFromComparison(int $variantId): void
    {
        $this->variantIds = array_diff($this->variantIds, [$variantId]);
        $this->loadVariants();
        
        $this->dispatch('comparisonUpdated', [
            'variantIds' => $this->variantIds,
            'count' => count($this->variantIds)
        ]);
    }

    public function clearComparison(): void
    {
        $this->variantIds = [];
        $this->loadVariants();
        
        $this->dispatch('comparisonUpdated', [
            'variantIds' => $this->variantIds,
            'count' => 0
        ]);
    }

    public function getVariantAttributes(ProductVariant $variant): array
    {
        $attributes = [];
        foreach ($variant->variantAttributeValues as $attributeValue) {
            $attributes[$attributeValue->attribute_name] = [
                'value' => $attributeValue->attribute_value,
                'display' => $attributeValue->attribute_value_display,
                'localized' => $attributeValue->getLocalizedDisplayValue(),
            ];
        }
        return $attributes;
    }

    public function getAllAttributeNames(): array
    {
        $allAttributes = [];
        foreach ($this->variantsToCompare as $variant) {
            $attributes = $this->getVariantAttributes($variant);
            $allAttributes = array_merge($allAttributes, array_keys($attributes));
        }
        return array_unique($allAttributes);
    }

    public function getVariantPrice(ProductVariant $variant): float
    {
        return $variant->getCurrentPrice();
    }

    public function getVariantOriginalPrice(ProductVariant $variant): ?float
    {
        return $variant->compare_price > $variant->price ? $variant->compare_price : null;
    }

    public function getVariantDiscountPercentage(ProductVariant $variant): ?int
    {
        $originalPrice = $this->getVariantOriginalPrice($variant);
        $currentPrice = $this->getVariantPrice($variant);

        if (!$originalPrice || $originalPrice <= $currentPrice) {
            return null;
        }

        return (int) round((($originalPrice - $currentPrice) / $originalPrice) * 100);
    }

    public function getVariantStockStatus(ProductVariant $variant): string
    {
        return $variant->getStockStatus();
    }

    public function getVariantStockMessage(ProductVariant $variant): string
    {
        $status = $this->getVariantStockStatus($variant);
        $quantity = $variant->available_quantity;

        return match ($status) {
            'in_stock' => __('product_variants.messages.in_stock', ['quantity' => $quantity]),
            'low_stock' => __('product_variants.messages.low_stock', ['quantity' => $quantity]),
            'out_of_stock' => __('product_variants.messages.out_of_stock'),
            default => __('product_variants.messages.variant_not_available'),
        };
    }

    public function getVariantBadges(ProductVariant $variant): array
    {
        $badges = [];

        if ($variant->is_new) {
            $badges[] = ['type' => 'new', 'label' => __('product_variants.badges.new')];
        }

        if ($variant->is_featured) {
            $badges[] = ['type' => 'featured', 'label' => __('product_variants.badges.featured')];
        }

        if ($variant->is_bestseller) {
            $badges[] = ['type' => 'bestseller', 'label' => __('product_variants.badges.bestseller')];
        }

        if ($variant->isCurrentlyOnSale()) {
            $badges[] = ['type' => 'sale', 'label' => __('product_variants.badges.sale')];
        }

        return $badges;
    }

    public function getVariantRating(ProductVariant $variant): float
    {
        return $variant->product->average_rating ?? 0;
    }

    public function getVariantReviewsCount(ProductVariant $variant): int
    {
        return $variant->product->reviews_count ?? 0;
    }

    public function getVariantWeight(ProductVariant $variant): float
    {
        return $variant->getFinalWeight();
    }

    public function getVariantDimensions(ProductVariant $variant): string
    {
        // This would typically come from variant attributes or product dimensions
        return 'N/A';
    }

    public function getVariantWarranty(ProductVariant $variant): string
    {
        // This would typically come from product or variant attributes
        return '2 years';
    }

    public function getVariantShipping(ProductVariant $variant): string
    {
        // This would typically come from product or variant attributes
        return 'Free shipping';
    }

    public function render()
    {
        return view('livewire.components.variant-comparison-table');
    }
}