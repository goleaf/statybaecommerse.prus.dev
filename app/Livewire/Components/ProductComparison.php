<?php

declare (strict_types=1);
namespace App\Livewire\Components;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
/**
 * ProductComparison
 * 
 * Livewire component for ProductComparison with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property array $compareProducts
 * @property bool $isOpen
 * @property int $maxProducts
 */
final class ProductComparison extends Component
{
    public array $compareProducts = [];
    public bool $isOpen = false;
    public int $maxProducts = 4;
    /**
     * Initialize the Livewire component with parameters.
     * @return void
     */
    public function mount(): void
    {
        $this->compareProducts = session('compare_products', []);
    }
    /**
     * Handle addToCompare functionality with proper error handling.
     * @param int $productId
     * @return void
     */
    #[On('add-to-compare')]
    public function addToCompare(int $productId): void
    {
        if (count($this->compareProducts) >= $this->maxProducts) {
            $this->dispatch('notify', ['type' => 'warning', 'message' => __('translations.compare_limit_reached', ['max' => $this->maxProducts])]);
            return;
        }
        if (in_array($productId, $this->compareProducts)) {
            $this->dispatch('notify', ['type' => 'info', 'message' => __('translations.product_already_in_comparison')]);
            return;
        }
        $this->compareProducts[] = $productId;
        session(['compare_products' => $this->compareProducts]);
        $this->dispatch('notify', ['type' => 'success', 'message' => __('translations.product_added_to_comparison')]);
        $this->dispatch('compare-updated');
    }
    /**
     * Handle removeFromCompare functionality with proper error handling.
     * @param int $productId
     * @return void
     */
    public function removeFromCompare(int $productId): void
    {
        $this->compareProducts = array_values(array_filter($this->compareProducts, fn($id) => $id !== $productId));
        session(['compare_products' => $this->compareProducts]);
        $this->dispatch('notify', ['type' => 'success', 'message' => __('translations.product_removed_from_comparison')]);
        $this->dispatch('compare-updated');
    }
    /**
     * Handle clearComparison functionality with proper error handling.
     * @return void
     */
    public function clearComparison(): void
    {
        $this->compareProducts = [];
        session()->forget('compare_products');
        $this->dispatch('notify', ['type' => 'success', 'message' => __('translations.comparison_cleared')]);
        $this->dispatch('compare-updated');
    }
    /**
     * Handle toggleComparisonPanel functionality with proper error handling.
     * @return void
     */
    public function toggleComparisonPanel(): void
    {
        $this->isOpen = !$this->isOpen;
    }
    /**
     * Handle compareProductsData functionality with proper error handling.
     * @return Collection
     */
    #[Computed]
    public function compareProductsData(): Collection
    {
        if (empty($this->compareProducts)) {
            return collect();
        }
        return Product::whereIn('id', $this->compareProducts)->with(['brand', 'categories', 'media', 'attributes.values', 'reviews'])->get();
    }
    /**
     * Handle comparisonAttributes functionality with proper error handling.
     * @return Collection
     */
    #[Computed]
    public function comparisonAttributes(): Collection
    {
        if ($this->compareProductsData->isEmpty()) {
            return collect();
        }
        // Get all unique attributes from compared products
        $attributes = collect();
        foreach ($this->compareProductsData as $product) {
            foreach ($product->attributes as $attribute) {
                if (!$attributes->contains('id', $attribute->id)) {
                    $attributes->push($attribute);
                }
            }
        }
        return $attributes->sortBy('sort_order');
    }
    /**
     * Handle getProductAttributeValue functionality with proper error handling.
     * @param Product $product
     * @param mixed $attributeId
     * @return string
     */
    public function getProductAttributeValue(Product $product, $attributeId): string
    {
        $attribute = $product->attributes->where('id', $attributeId)->first();
        if (!$attribute || !$attribute->pivot) {
            return '-';
        }
        return $attribute->values->where('id', $attribute->pivot->attribute_value_id)->first()?->value ?? '-';
    }
    /**
     * Handle navigateToComparison functionality with proper error handling.
     * @return void
     */
    public function navigateToComparison(): void
    {
        $this->redirect(localized_route('products.compare', ['products' => implode(',', $this->compareProducts)]));
    }
    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    public function render(): View
    {
        return view('livewire.components.product-comparison', ['compareProductsData' => $this->compareProductsData, 'comparisonAttributes' => $this->comparisonAttributes]);
    }
}
