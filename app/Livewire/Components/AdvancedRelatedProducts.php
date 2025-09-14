<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Product;
use Livewire\Component;

final /**
 * AdvancedRelatedProducts
 * 
 * Livewire component for reactive frontend functionality.
 */
class AdvancedRelatedProducts extends Component
{
    public Product $product;

    public int $limit = 4;

    public string $type = 'mixed'; // mixed, category, brand, price

    public string $title = '';

    public bool $showTitle = true;

    public string $class = '';

    public function mount(
        Product $product,
        int $limit = 4,
        string $type = 'mixed',
        string $title = '',
        bool $showTitle = true,
        string $class = ''
    ): void {
        $this->product = $product;
        $this->limit = $limit;
        $this->type = $type;
        $this->title = $title;
        $this->showTitle = $showTitle;
        $this->class = $class;
    }

    public function getRelatedProductsProperty()
    {
        return match ($this->type) {
            'category' => $this->product->getRelatedProductsByCategory($this->limit),
            'brand' => $this->product->getRelatedProductsByBrand($this->limit),
            'price' => $this->product->getRelatedProductsByPriceRange(0.2, $this->limit),
            default => $this->product->getRelatedProducts($this->limit),
        };
    }

    public function getSectionTitle(): string
    {
        if ($this->title) {
            return $this->title;
        }

        return match ($this->type) {
            'category' => __('ecommerce.similar_products'),
            'brand' => __('ecommerce.you_might_also_like'),
            'price' => __('ecommerce.recommended_for_you'),
            default => __('ecommerce.related_products'),
        };
    }

    public function render()
    {
        return view('livewire.components.advanced-related-products');
    }
}
