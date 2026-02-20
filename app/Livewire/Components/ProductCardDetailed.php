<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Livewire\Concerns\WithCart;
use App\Livewire\Concerns\WithNotifications;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * ProductCardDetailed
 *
 * Livewire component for ProductCardDetailed with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property Product $product
 * @property bool    $showQuickView
 * @property bool    $showCompare
 * @property string  $layout
 * @property bool    $isInComparison
 */
final class ProductCardDetailed extends Component
{
    use WithCart {
        addToCart as performAddToCart;
    }
    use WithNotifications;

    public Product $product;

    public bool $showQuickView = false;

    public bool $showCompare = true;

    public string $layout = 'grid';

    // grid, list, minimal
    public bool $isInComparison = false;

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(Product $product): void
    {
        // Optimize relationship loading using Laravel 12.10 relationLoaded dot notation
        if (! $product->relationLoaded('brand') || ! $product->relationLoaded('media') || ! $product->relationLoaded('categories')) {
            $product->load(['brand', 'media', 'categories']);
        }
        $this->product = $product;
        $this->checkComparisonStatus();
    }

    /**
     * Handle addToCart functionality with proper error handling.
     */
    public function addToCart(): void
    {
        $added = $this->performAddToCart($this->product->id);

        if (! $added) {
            return;
        }
    }

    /**
     * Handle viewProduct functionality with proper error handling.
     */
    public function viewProduct()
    {
        return $this->redirect(route('product.show', $this->product));
    }

    /**
     * Handle toggleComparison functionality with proper error handling.
     */
    public function toggleComparison(): void
    {
        $this->dispatch('add-to-comparison', productId: $this->product->id);
        $this->dispatch('notify', ['type' => 'success', 'message' => __('translations.add_to_compare')]);
    }

    /**
     * Handle checkComparisonStatus functionality with proper error handling.
     */
    private function checkComparisonStatus(): void
    {
        $this->isInComparison = false;
        // Simplified for now
    }

    /**
     * Handle quickView functionality with proper error handling.
     */
    public function quickView(): void
    {
        $this->dispatch('product-quick-view', productId: $this->product->id);
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.components.product-card-detailed');
    }
}
