<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Product;
use Livewire\Component;

final /**
 * RelatedProducts
 * 
 * Livewire component for reactive frontend functionality.
 */
class RelatedProducts extends Component
{
    public Product $product;

    public int $limit = 4;

    public string $title = '';

    public bool $showTitle = true;

    public string $class = '';

    public function mount(
        Product $product,
        int $limit = 4,
        string $title = '',
        bool $showTitle = true,
        string $class = ''
    ): void {
        $this->product = $product;
        $this->limit = $limit;
        $this->title = $title;
        $this->showTitle = $showTitle;
        $this->class = $class;
    }

    public function getRelatedProductsProperty()
    {
        return $this->product->getRelatedProducts($this->limit);
    }

    public function render()
    {
        return view('livewire.components.related-products');
    }
}
