<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Livewire\Concerns\WithCart;
use App\Livewire\Concerns\WithNotifications;
use App\Models\Product;
use Livewire\Component;

/**
 * ProductCardExtended
 *
 * Livewire component for ProductCardExtended with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property Product $product
 * @property bool    $showQuickView
 * @property bool    $showCompare
 * @property string  $layout
 * @property bool    $isInComparison
 */
final class ProductCardExtended extends Component
{
    use WithCart {
        addToCart as performAddToCart;
    }
    use WithNotifications;

    public Product $product;

    public bool $showQuickView = false;

    public string $layout = 'grid';

    // grid, list, minimal

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void {}

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
     * Handle quickView functionality with proper error handling.
     */
    public function quickView(): void
    {
        $this->dispatch('open-quick-view', ['product_id' => $this->product->id]);
    }

    /**
     * Handle viewProduct functionality with proper error handling.
     */
    public function viewProduct()
    {
        return $this->redirect(route('product.show', $this->product));
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        return view('livewire.components.product-card-extended');
    }
}
