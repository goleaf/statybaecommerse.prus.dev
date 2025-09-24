<?php

declare(strict_types=1);

namespace App\Livewire;

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

    public bool $showVariantDetails = false;

    protected $listeners = ['variantSelected' => 'onVariantSelected'];

    public function mount(Product $product): void
    {
        $this->product = $product;
        $this->loadVariants();
        $this->loadAttributes();
        $this->selectDefaultVariant();
        $this->recordProductView();
    }

    public function loadVariants(): void
    {
        $this->variants = $this->product->variants()
            ->with(['attributes.attribute', 'images'])
            ->enabled()
            ->get();
    }

    public function loadAttributes(): void
    {
        $this->variantAttributes = Attribute::whereHas('values', function ($query) {
            $query->whereHas('variants', function ($subQuery) {
                $subQuery->where('product_id', $this->product->id);
            });
        })
            ->with(['values' => function ($query) {
                $query->whereHas('variants', function ($subQuery) {
                    $subQuery->where('product_id', $this->product->id);
                });
            }])
            ->enabled()
            ->orderBy('sort_order')
            ->get();
    }

    public function selectDefaultVariant(): void
    {
        $defaultVariant = $this->variants->where('is_default_variant', true)->first();

        if (! $defaultVariant && $this->variants->isNotEmpty()) {
            $defaultVariant = $this->variants->first();
        }

        if ($defaultVariant) {
            $this->selectedVariant = $defaultVariant;
            $this->selectedAttributes = $this->getVariantAttributes($defaultVariant);
        }
    }

    public function onAttributeChange(string $attributeSlug, string $value): void
    {
        $this->selectedAttributes[$attributeSlug] = $value;
        $this->findMatchingVariant();
        $this->recordVariantClick();
    }

    public function findMatchingVariant(): void
    {
        if (empty($this->selectedAttributes)) {
            $this->selectDefaultVariant();

            return;
        }

        $matchingVariant = $this->variants->first(function (ProductVariant $variant) {
            $variantAttributes = $this->getVariantAttributes($variant);

            foreach ($this->selectedAttributes as $attributeSlug => $selectedValue) {
                if (! isset($variantAttributes[$attributeSlug]) ||
                    $variantAttributes[$attributeSlug] !== $selectedValue) {
                    return false;
                }
            }

            return true;
        });

        $this->selectedVariant = $matchingVariant;
        $this->showVariantDetails = $matchingVariant !== null;
    }

    public function getVariantAttributes(ProductVariant $variant): array
    {
        $attributes = [];

        foreach ($variant->attributes as $attributeValue) {
            $attributes[$attributeValue->attribute->slug] = $attributeValue->value;
        }

        return $attributes;
    }

    public function getAvailableValues(string $attributeSlug): Collection
    {
        $attribute = $this->variantAttributes->where('slug', $attributeSlug)->first();

        if (! $attribute) {
            return collect();
        }

        // Get all variants that match current selections (excluding this attribute)
        $currentSelections = $this->selectedAttributes;
        unset($currentSelections[$attributeSlug]);

        $matchingVariants = $this->variants->filter(function (ProductVariant $variant) use ($currentSelections) {
            $variantAttributes = $this->getVariantAttributes($variant);

            foreach ($currentSelections as $attrSlug => $selectedValue) {
                if (! isset($variantAttributes[$attrSlug]) ||
                    $variantAttributes[$attrSlug] !== $selectedValue) {
                    return false;
                }
            }

            return true;
        });

        // Get unique values for this attribute from matching variants
        $availableValues = collect();

        foreach ($matchingVariants as $variant) {
            $variantAttributes = $this->getVariantAttributes($variant);
            if (isset($variantAttributes[$attributeSlug])) {
                $value = $variantAttributes[$attributeSlug];
                $attributeValue = $attribute->values->where('value', $value)->first();
                if ($attributeValue && ! $availableValues->contains('value', $value)) {
                    $availableValues->push($attributeValue);
                }
            }
        }

        return $availableValues->sortBy('sort_order');
    }

    public function isValueAvailable(string $attributeSlug, string $value): bool
    {
        return $this->getAvailableValues($attributeSlug)->contains('value', $value);
    }

    public function isValueSelected(string $attributeSlug, string $value): bool
    {
        return isset($this->selectedAttributes[$attributeSlug]) &&
               $this->selectedAttributes[$attributeSlug] === $value;
    }

    public function incrementQuantity(): void
    {
        if ($this->selectedVariant && $this->quantity < $this->selectedVariant->availableQuantity()) {
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
            $this->dispatch('show-error', message: __('product_variants.messages.no_variant_selected'));

            return;
        }

        if (! $this->selectedVariant->isAvailableForPurchase()) {
            $this->dispatch('show-error', message: __('product_variants.messages.variant_not_available'));

            return;
        }

        if ($this->quantity > $this->selectedVariant->availableQuantity()) {
            $this->dispatch('show-error', message: __('product_variants.messages.insufficient_stock'));

            return;
        }

        // Record analytics
        $this->recordAddToCart();

        // Dispatch event to add to cart
        $this->dispatch('add-to-cart', [
            'variant_id' => $this->selectedVariant->id,
            'quantity' => $this->quantity,
        ]);

        $this->dispatch('show-success', message: __('product_variants.messages.added_to_cart'));
    }

    public function getVariantPrice(): float
    {
        return $this->selectedVariant ? $this->selectedVariant->getCurrentPrice() : 0;
    }

    public function getVariantOriginalPrice(): float
    {
        return $this->selectedVariant ? $this->selectedVariant->price : 0;
    }

    public function getVariantPromotionalPrice(): ?float
    {
        return $this->selectedVariant && $this->selectedVariant->isCurrentlyOnSale()
            ? $this->selectedVariant->promotional_price
            : null;
    }

    public function isVariantOnSale(): bool
    {
        return $this->selectedVariant ? $this->selectedVariant->isCurrentlyOnSale() : false;
    }

    public function getVariantLocalizedName(?string $locale = null): string
    {
        return $this->selectedVariant ? $this->selectedVariant->getLocalizedName($locale) : '';
    }

    public function getVariantLocalizedDescription(?string $locale = null): ?string
    {
        return $this->selectedVariant ? $this->selectedVariant->getLocalizedDescription($locale) : null;
    }

    public function getVariantStockStatus(): string
    {
        if (! $this->selectedVariant) {
            return 'unavailable';
        }

        return $this->selectedVariant->stock_status;
    }

    public function getVariantStockMessage(): string
    {
        if (! $this->selectedVariant) {
            return __('product_variants.messages.select_variant');
        }

        $available = $this->selectedVariant->availableQuantity();

        if ($available <= 0) {
            return __('product_variants.messages.out_of_stock');
        }

        if ($available <= $this->selectedVariant->low_stock_threshold) {
            return __('product_variants.messages.low_stock', ['quantity' => $available]);
        }

        return __('product_variants.messages.in_stock', ['quantity' => $available]);
    }

    /**
     * Record product view for analytics.
     */
    public function recordProductView(): void
    {
        // Record view for the product
        $this->product->increment('views_count');

        // Record view for the default variant if available
        if ($this->selectedVariant) {
            $this->selectedVariant->recordView();
        }
    }

    /**
     * Record variant click for analytics.
     */
    public function recordVariantClick(): void
    {
        if ($this->selectedVariant) {
            $this->selectedVariant->recordClick();
        }
    }

    /**
     * Record add to cart action for analytics.
     */
    public function recordAddToCart(): void
    {
        if ($this->selectedVariant) {
            $this->selectedVariant->recordDailyAnalytics('add_to_cart');
        }
    }

    /**
     * Get variant badges (new, featured, bestseller, on sale).
     */
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

    /**
     * Get variant discount percentage.
     */
    public function getVariantDiscountPercentage(): ?float
    {
        if (! $this->selectedVariant || ! $this->isVariantOnSale()) {
            return null;
        }

        $originalPrice = $this->getVariantOriginalPrice();
        $currentPrice = $this->getVariantPrice();

        if ($originalPrice <= 0 || $currentPrice >= $originalPrice) {
            return null;
        }

        return round((($originalPrice - $currentPrice) / $originalPrice) * 100, 0);
    }

    public function render()
    {
        return view('livewire.product-variant-selector');
    }
}
