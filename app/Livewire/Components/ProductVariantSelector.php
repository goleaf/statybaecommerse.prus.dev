<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Attribute;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Livewire\Component;

final class ProductVariantSelector extends Component
{
    public Product $product;

    public Collection $variants;

    public Collection $variantAttributes;

    public array $selectedAttributes = [];

    public ?ProductVariant $selectedVariant = null;

    public int $quantity = 1;

    public bool $showComparison = false;

    public array $comparisonVariants = [];

    protected $listeners = ['productChanged' => 'loadProduct'];

    public function mount(Product $product): void
    {
        $this->product = $product;
        $this->loadProduct();
    }

    public function loadProduct(): void
    {
        $this->variants = $this->product->variants()
            ->with(['variantAttributeValues.attribute', 'variantAttributeValues.attributeValue'])
            ->get();

        $this->variantAttributes = Attribute::whereHas('attributeValues', function ($query) {
            $query->whereHas('variantAttributeValues', function ($q) {
                $q->whereIn('variant_id', $this->variants->pluck('id'));
            });
        })->with(['attributeValues' => function ($query) {
            $query->whereHas('variantAttributeValues', function ($q) {
                $q->whereIn('variant_id', $this->variants->pluck('id'));
            });
        }])->orderBy('sort_order')->get();

        $this->selectedAttributes = [];
        $this->selectedVariant = null;
        $this->quantity = 1;
    }

    public function updatedSelectedAttributes(): void
    {
        $this->findMatchingVariant();
    }

    public function selectAttribute(string $attributeSlug, string $value): void
    {
        $this->selectedAttributes[$attributeSlug] = $value;
        $this->findMatchingVariant();

        // Track analytics
        $this->recordVariantClick();
    }

    public function findMatchingVariant(): void
    {
        if (empty($this->selectedAttributes)) {
            $this->selectedVariant = $this->variants->where('is_default', true)->first();

            return;
        }

        $this->selectedVariant = $this->variants->first(function ($variant) {
            $variantAttributes = $variant->variantAttributeValues->pluck('attribute_value', 'attribute_name')->toArray();

            foreach ($this->selectedAttributes as $attributeSlug => $value) {
                if (! isset($variantAttributes[$attributeSlug]) || $variantAttributes[$attributeSlug] !== $value) {
                    return false;
                }
            }

            return true;
        });
    }

    public function incrementQuantity(): void
    {
        if ($this->selectedVariant && $this->quantity < $this->selectedVariant->available_quantity) {
            $this->quantity++;
        }
    }

    public function decrementQuantity(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function addToCart(): void
    {
        if (! $this->selectedVariant) {
            $this->addError('variant', __('product_variants.messages.no_variant_selected'));

            return;
        }

        if ($this->selectedVariant->available_quantity < $this->quantity) {
            $this->addError('quantity', __('product_variants.messages.insufficient_stock'));

            return;
        }

        // Add to cart logic here
        // This would typically call a cart service

        // Track analytics
        $this->recordAddToCart();

        $this->dispatch('itemAddedToCart', [
            'variant_id' => $this->selectedVariant->id,
            'quantity' => $this->quantity,
            'price' => $this->getVariantPrice(),
        ]);

        session()->flash('success', __('product_variants.messages.added_to_cart'));
    }

    public function addToComparison(): void
    {
        if (! $this->selectedVariant) {
            return;
        }

        if (! in_array($this->selectedVariant->id, $this->comparisonVariants)) {
            $this->comparisonVariants[] = $this->selectedVariant->id;
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

    public function getVariantPrice(): float
    {
        if (! $this->selectedVariant) {
            return 0;
        }

        return $this->selectedVariant->getCurrentPrice();
    }

    public function getVariantOriginalPrice(): ?float
    {
        if (! $this->selectedVariant) {
            return null;
        }

        return $this->selectedVariant->compare_price > $this->selectedVariant->price
            ? $this->selectedVariant->compare_price
            : null;
    }

    public function getVariantPromotionalPrice(): ?float
    {
        if (! $this->selectedVariant || ! $this->selectedVariant->isCurrentlyOnSale()) {
            return null;
        }

        return $this->selectedVariant->promotional_price;
    }

    public function isVariantOnSale(): bool
    {
        return $this->selectedVariant && $this->selectedVariant->isCurrentlyOnSale();
    }

    public function getVariantLocalizedName(): string
    {
        if (! $this->selectedVariant) {
            return $this->product->name;
        }

        return $this->selectedVariant->getLocalizedName();
    }

    public function getVariantLocalizedDescription(): string
    {
        if (! $this->selectedVariant) {
            return $this->product->description;
        }

        return $this->selectedVariant->getLocalizedDescription();
    }

    public function getVariantBadges(): array
    {
        if (! $this->selectedVariant) {
            return [];
        }

        $badges = [];

        if ($this->selectedVariant->is_new) {
            $badges[] = ['type' => 'new', 'label' => __('product_variants.badges.new')];
        }

        if ($this->selectedVariant->is_featured) {
            $badges[] = ['type' => 'featured', 'label' => __('product_variants.badges.featured')];
        }

        if ($this->selectedVariant->is_bestseller) {
            $badges[] = ['type' => 'bestseller', 'label' => __('product_variants.badges.bestseller')];
        }

        if ($this->isVariantOnSale()) {
            $badges[] = ['type' => 'sale', 'label' => __('product_variants.badges.sale')];
        }

        return $badges;
    }

    public function getVariantDiscountPercentage(): ?int
    {
        if (! $this->selectedVariant || ! $this->isVariantOnSale()) {
            return null;
        }

        $originalPrice = $this->getVariantOriginalPrice();
        $currentPrice = $this->getVariantPrice();

        if (! $originalPrice || $originalPrice <= $currentPrice) {
            return null;
        }

        return (int) round((($originalPrice - $currentPrice) / $originalPrice) * 100);
    }

    public function getStockStatus(): string
    {
        if (! $this->selectedVariant) {
            return 'not_available';
        }

        return $this->selectedVariant->getStockStatus();
    }

    public function getStockMessage(): string
    {
        $status = $this->getStockStatus();
        $quantity = $this->selectedVariant?->available_quantity ?? 0;

        return match ($status) {
            'in_stock' => __('product_variants.messages.in_stock', ['quantity' => $quantity]),
            'low_stock' => __('product_variants.messages.low_stock', ['quantity' => $quantity]),
            'out_of_stock' => __('product_variants.messages.out_of_stock'),
            default => __('product_variants.messages.variant_not_available'),
        };
    }

    public function getAvailableAttributeValues(string $attributeSlug): Collection
    {
        return $this->variantAttributes->firstWhere('slug', $attributeSlug)?->attributeValues ?? collect();
    }

    public function isAttributeValueAvailable(string $attributeSlug, string $value): bool
    {
        return $this->variants->contains(function ($variant) use ($attributeSlug, $value) {
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

    private function recordVariantClick(): void
    {
        if ($this->selectedVariant) {
            $this->selectedVariant->recordClick();
        }
    }

    private function recordAddToCart(): void
    {
        if ($this->selectedVariant) {
            $this->selectedVariant->recordAddToCart();
        }
    }

    public function render()
    {
        return view('livewire.components.product-variant-selector');
    }
}
