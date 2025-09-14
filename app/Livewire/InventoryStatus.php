<?php

declare (strict_types=1);
namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
/**
 * InventoryStatus
 * 
 * Livewire component for InventoryStatus with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property Product $product
 * @property bool $showDetails
 */
final class InventoryStatus extends Component
{
    public Product $product;
    public bool $showDetails = false;
    /**
     * Initialize the Livewire component with parameters.
     * @param Product $product
     * @return void
     */
    public function mount(Product $product): void
    {
        $this->product = $product;
    }
    /**
     * Handle toggleDetails functionality with proper error handling.
     * @return void
     */
    public function toggleDetails(): void
    {
        $this->showDetails = !$this->showDetails;
    }
    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        return view('livewire.inventory-status');
    }
}