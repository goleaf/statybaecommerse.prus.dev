<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;

final /**
 * InventoryStatus
 * 
 * Livewire component for reactive frontend functionality.
 */
class InventoryStatus extends Component
{
    public Product $product;

    public bool $showDetails = false;

    public function mount(Product $product): void
    {
        $this->product = $product;
    }

    public function toggleDetails(): void
    {
        $this->showDetails = ! $this->showDetails;
    }

    public function render()
    {
        return view('livewire.inventory-status');
    }
}
