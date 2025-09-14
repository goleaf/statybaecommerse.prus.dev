<?php

declare (strict_types=1);
namespace App\Livewire\Components\Product;

use Illuminate\Contracts\View\View;
use Livewire\Component;
/**
 * Images
 * 
 * Livewire component for Images with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property string|null $thumbnail
 * @property array $images
 * @property int $active
 */
class Images extends Component
{
    public ?string $thumbnail = null;
    public array $images = [];
    public int $active = 0;
    /**
     * Handle setActive functionality with proper error handling.
     * @param int $index
     * @return void
     */
    public function setActive(int $index): void
    {
        $this->active = max(0, min($index, count($this->images) - 1));
    }
    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    public function render(): View
    {
        return view('livewire.components.product.images');
    }
}