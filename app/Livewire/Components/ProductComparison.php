<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

final /**
 * ProductComparison
 * 
 * Livewire component for reactive frontend functionality.
 */
class ProductComparison extends Component
{
    public array $compareProducts = [];

    public bool $isOpen = false;

    public int $maxProducts = 4;

    public function mount(): void
    {
        $this->compareProducts = session('compare_products', []);
    }

    #[On('add-to-compare')]
    public function addToCompare(int $productId): void
    {
        if (count($this->compareProducts) >= $this->maxProducts) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => __('translations.compare_limit_reached', ['max' => $this->maxProducts]),
            ]);

            return;
        }

        if (in_array($productId, $this->compareProducts)) {
            $this->dispatch('notify', [
                'type' => 'info',
                'message' => __('translations.product_already_in_comparison'),
            ]);

            return;
        }

        $this->compareProducts[] = $productId;
        session(['compare_products' => $this->compareProducts]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => __('translations.product_added_to_comparison'),
        ]);

        $this->dispatch('compare-updated');
    }

    public function removeFromCompare(int $productId): void
    {
        $this->compareProducts = array_values(array_filter($this->compareProducts, fn ($id) => $id !== $productId));
        session(['compare_products' => $this->compareProducts]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => __('translations.product_removed_from_comparison'),
        ]);

        $this->dispatch('compare-updated');
    }

    public function clearComparison(): void
    {
        $this->compareProducts = [];
        session()->forget('compare_products');

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => __('translations.comparison_cleared'),
        ]);

        $this->dispatch('compare-updated');
    }

    public function toggleComparisonPanel(): void
    {
        $this->isOpen = ! $this->isOpen;
    }

    public function getCompareProductsDataProperty()
    {
        if (empty($this->compareProducts)) {
            return collect();
        }

        return Product::whereIn('id', $this->compareProducts)
            ->with(['brand', 'categories', 'media', 'attributes.values', 'reviews'])
            ->get();
    }

    public function getComparisonAttributesProperty()
    {
        if ($this->compareProductsData->isEmpty()) {
            return collect();
        }

        // Get all unique attributes from compared products
        $attributes = collect();

        foreach ($this->compareProductsData as $product) {
            foreach ($product->attributes as $attribute) {
                if (! $attributes->contains('id', $attribute->id)) {
                    $attributes->push($attribute);
                }
            }
        }

        return $attributes->sortBy('sort_order');
    }

    public function getProductAttributeValue(Product $product, $attributeId): string
    {
        $attribute = $product->attributes->where('id', $attributeId)->first();

        if (! $attribute || ! $attribute->pivot) {
            return '-';
        }

        return $attribute->values->where('id', $attribute->pivot->attribute_value_id)->first()?->value ?? '-';
    }

    public function navigateToComparison(): void
    {
        $this->redirect(route('products.compare', [
            'locale' => app()->getLocale(),
            'products' => implode(',', $this->compareProducts),
        ]));
    }

    public function render(): View
    {
        return view('livewire.components.product-comparison', [
            'compareProductsData' => $this->compareProductsData,
            'comparisonAttributes' => $this->comparisonAttributes,
        ]);
    }
}
