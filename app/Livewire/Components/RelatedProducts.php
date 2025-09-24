<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * RelatedProducts
 *
 * Livewire component for RelatedProducts with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property Product $product
 * @property int $limit
 * @property string $title
 * @property bool $showTitle
 * @property string $class
 */
final class RelatedProducts extends Component
{
    public Product $product;

    public int $limit = 4;

    public string $title = '';

    public bool $showTitle = true;

    public string $class = '';

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(Product $product, int $limit = 4, string $title = '', bool $showTitle = true, string $class = ''): void
    {
        $this->product = $product;
        $this->limit = $limit;
        $this->title = $title;
        $this->showTitle = $showTitle;
        $this->class = $class;
    }

    /**
     * Handle relatedProducts functionality with proper error handling.
     */
    #[Computed]
    public function relatedProducts(): Collection
    {
        return $this->product->getRelatedProducts($this->limit);
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        return view('livewire.components.related-products');
    }
}
