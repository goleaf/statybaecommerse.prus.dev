<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Attribute;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

final class ProductVariantShowcase extends Component
{
    public Collection $products;

    public ?Product $selectedProduct = null;

    public Collection $productVariants;

    public Collection $productAttributes;

    public array $selectedAttributes = [];

    public ?ProductVariant $selectedVariant = null;

    public bool $showComparison = false;

    public array $comparisonVariants = [];

    public array $variantCounts = [
        'total_variants' => 0,
        'in_stock'       => 0,
        'low_stock'      => 0,
        'out_of_stock'   => 0,
    ];

    public function mount(): void
    {
        $this->loadProducts();
    }

    public function loadProducts(): void
    {
        $query = Product::with(['variants', 'brand', 'categories']);

        $productsTable = (new Product)->getTable();

        if (Schema::hasColumn($productsTable, 'is_visible')) {
            $query->where('is_visible', true);
        }

        if (Schema::hasColumn($productsTable, 'status')) {
            $query->where('status', 'published');
        }

        if (Schema::hasColumn($productsTable, 'published_at')) {
            $query->where(function ($builder): void {
                $builder
                    ->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
        }

        $products = $query->get();

        if ($products->isEmpty()) {
            $products = Product::withoutGlobalScopes()->with(['variants', 'brand', 'categories'])->get();
        }

        $this->products = $products;

        $this->products->each(function (Product $product): void {
            $product->setAttribute('variant_counts', $this->calculateVariantCounts($product->variants));
        });
    }

    public function selectProduct(int $productId): void
    {
        $products = $this->products ?? collect();

        $this->selectedProduct = $products->firstWhere('id', $productId);

        if (! $this->selectedProduct) {
            $this->selectedProduct = Product::withoutGlobalScopes()
                ->with(['variants', 'brand', 'categories'])
                ->find($productId);

            if (! $this->selectedProduct) {
                $this->loadEmptyProductState();

                return;
            }

            $this->products = $products
                ->push($this->selectedProduct)
                ->unique(fn (Product $product) => $product->getKey())
                ->values();
        }

        $this->loadProductVariants();

        $this->reset(['selectedAttributes', 'selectedVariant', 'showComparison', 'comparisonVariants']);
    }

    public function loadProductVariants(): void
    {
        if (! $this->selectedProduct) {
            $this->loadEmptyProductState();

            return;
        }

        $this->selectedProduct->loadMissing([
            'variants.variantAttributeValues.attribute',
            'variants.variantAttributeValues.attributeValue',
        ]);

        $this->productVariants = $this->selectedProduct->variants;

        $this->selectedProduct->setRelation('variants', $this->productVariants);

        $this->variantCounts = $this->calculateVariantCounts($this->productVariants);
        $this->selectedProduct->setAttribute('variant_counts', $this->variantCounts);

        $this->productAttributes = Attribute::whereHas('attributeValues', function ($query): void {
            $query->whereHas('variantAttributeValues', function ($q): void {
                $q->whereIn('variant_id', $this->productVariants->pluck('id'));
            });
        })->with(['attributeValues' => function ($query): void {
            $query->whereHas('variantAttributeValues', function ($q): void {
                $q->whereIn('variant_id', $this->productVariants->pluck('id'));
            });
        }])->orderBy('sort_order')->get();

        $this->selectedAttributes = [];
        $this->selectedVariant = $this->productVariants->where('is_default', true)->first();
    }

    private function loadEmptyProductState(): void
    {
        $this->selectedProduct = null;
        $this->productVariants = collect();
        $this->variantCounts = [
            'total_variants' => 0,
            'in_stock'       => 0,
            'low_stock'      => 0,
            'out_of_stock'   => 0,
        ];
        $this->productAttributes = collect();
        $this->selectedAttributes = [];
        $this->selectedVariant = null;
    }

    public function selectAttribute(string $attributeSlug, string $value): void
    {
        $this->selectedAttributes[$attributeSlug] = $value;
        $this->findMatchingVariant();
    }

    public function findMatchingVariant(): void
    {
        if (empty($this->selectedAttributes)) {
            $this->selectedVariant = $this->productVariants->where('is_default', true)->first();

            return;
        }

        $this->selectedVariant = $this->productVariants->first(function ($variant) {
            $variantAttributes = $variant->variantAttributeValues->pluck('attribute_value', 'attribute_name')->toArray();

            foreach ($this->selectedAttributes as $attributeSlug => $value) {
                if (! isset($variantAttributes[$attributeSlug]) || $variantAttributes[$attributeSlug] !== $value) {
                    return false;
                }
            }

            return true;
        });
    }

    public function addToComparison(int $variantId): void
    {
        if (! in_array($variantId, $this->comparisonVariants) && count($this->comparisonVariants) < 4) {
            $this->comparisonVariants[] = $variantId;
            $this->showComparison = true;
        }
    }

    public function removeFromComparison(int $variantId): void
    {
        $this->comparisonVariants = array_diff($this->comparisonVariants, [$variantId]);

        if (empty($this->comparisonVariants)) {
            $this->showComparison = false;
        }
    }

    public function clearComparison(): void
    {
        $this->comparisonVariants = [];
        $this->showComparison = false;
    }

    public function getVariantAttributes(ProductVariant $variant): array
    {
        $attributes = [];
        foreach ($variant->variantAttributeValues as $attributeValue) {
            $attributes[$attributeValue->attribute_name] = [
                'value'     => $attributeValue->attribute_value,
                'display'   => $attributeValue->attribute_value_display,
                'localized' => $attributeValue->getLocalizedDisplayValue(),
            ];
        }

        return $attributes;
    }

    public function getVariantPrice(ProductVariant $variant): float
    {
        return (float) $variant->getCurrentPrice();
    }

    public function getVariantOriginalPrice(ProductVariant $variant): ?float
    {
        return $variant->compare_price > $variant->price ? $variant->compare_price : null;
    }

    public function getVariantDiscountPercentage(ProductVariant $variant): ?int
    {
        $originalPrice = $this->getVariantOriginalPrice($variant);
        $currentPrice = $this->getVariantPrice($variant);

        if (! $originalPrice || $originalPrice <= $currentPrice) {
            return null;
        }

        return (int) round((($originalPrice - $currentPrice) / $originalPrice) * 100);
    }

    public function getVariantStockStatus(ProductVariant $variant): string
    {
        if (! $variant->track_inventory) {
            return 'not_tracked';
        }

        $available = (int) $variant->available_quantity;

        if ($available <= 0) {
            return 'out_of_stock';
        }

        if ($available <= (int) $variant->low_stock_threshold) {
            return 'low_stock';
        }

        return 'in_stock';
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

    public function getProductStats(): array
    {
        if (! $this->selectedProduct) {
            return [];
        }

        $variants = $this->productVariants;

        return [
            ...$this->variantCounts,
            'on_sale'       => $variants->where('is_on_sale', true)->count(),
            'featured'      => $variants->where('is_featured', true)->count(),
            'new'           => $variants->where('is_new', true)->count(),
            'bestsellers'   => $variants->where('is_bestseller', true)->count(),
            'average_price' => $variants->avg('price'),
            'highest_price' => $variants->max('price'),
            'lowest_price'  => $variants->min('price'),
        ];
    }

    public function getAvailableAttributeValues(string $attributeSlug): Collection
    {
        return $this->productAttributes->firstWhere('slug', $attributeSlug)?->attributeValues ?? collect();
    }

    public function isAttributeValueAvailable(string $attributeSlug, string $value): bool
    {
        return $this->productVariants->contains(function ($variant) use ($attributeSlug, $value) {
            return $variant->variantAttributeValues
                ->where('attribute_name', $attributeSlug)
                ->where('attribute_value', $value)
                ->isNotEmpty();
        });
    }

    public function getAttributeValueDisplay(string $attributeSlug, string $value): string
    {
        $attributeValue = $this->getAvailableAttributeValues($attributeSlug)
            ->firstWhere('value', $value);

        return $attributeValue?->getLocalizedDisplayValue() ?? $value;
    }

    public function render()
    {
        return view('livewire.product-variant-showcase');
    }

    private function calculateVariantCounts(Collection $variants): array
    {
        $counts = [
            'total_variants' => 0,
            'in_stock'       => 0,
            'low_stock'      => 0,
            'out_of_stock'   => 0,
        ];

        foreach ($variants as $variant) {
            if ($variant instanceof ProductVariant) {
                // Merge current attribute bag with the original snapshot so we can work with plain scalars.
                $rawAttributes = array_merge($variant->getRawOriginal(), $variant->getAttributes());
            } else {
                $rawAttributes = (array) $variant;
            }

            $availableQuantity = (int) ($rawAttributes['available_quantity'] ?? 0);
            $trackInventory = (bool) ($rawAttributes['track_inventory'] ?? false);
            $lowStockThreshold = (int) ($rawAttributes['low_stock_threshold'] ?? 0);

            $counts['total_variants']++;

            if ($availableQuantity > 0) {
                $counts['in_stock']++;
            }

            if (! $trackInventory) {
                continue;
            }

            if ($availableQuantity <= 0) {
                $counts['out_of_stock']++;

                continue;
            }

            if ($availableQuantity <= $lowStockThreshold) {
                $counts['low_stock']++;
            }
        }

        return $counts;
    }
}
