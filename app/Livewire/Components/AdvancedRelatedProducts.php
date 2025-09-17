<?php declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * AdvancedRelatedProducts
 *
 * Livewire component for AdvancedRelatedProducts with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property Product $product
 * @property int $limit
 * @property string $type
 * @property string $title
 * @property bool $showTitle
 * @property string $class
 */
final class AdvancedRelatedProducts extends Component
{
    public Product $product;
    public int $limit = 4;
    public string $type = 'mixed';
    // mixed, category, brand, price
    public string $title = '';
    public bool $showTitle = true;
    public string $class = '';

    /**
     * Initialize the Livewire component with parameters.
     * @param Product $product
     * @param int $limit
     * @param string $type
     * @param string $title
     * @param bool $showTitle
     * @param string $class
     * @return void
     */
    public function mount(Product $product, int $limit = 4, string $type = 'mixed', string $title = '', bool $showTitle = true, string $class = ''): void
    {
        $this->product = $product;
        $this->limit = $limit;
        $this->type = $type;
        $this->title = $title;
        $this->showTitle = $showTitle;
        $this->class = $class;
    }

    /**
     * Handle relatedProducts functionality with proper error handling.
     * @return Collection
     */
    #[Computed]
    public function relatedProducts(): Collection
    {
        return match ($this->type) {
            'category' => $this->product->getRelatedProductsByCategory($this->limit),
            'brand' => $this->product->getRelatedProductsByBrand($this->limit),
            'price' => $this->product->getRelatedProductsByPriceRange(0.2, $this->limit),
            default => $this->product->getRelatedProducts($this->limit),
        };
    }

    /**
     * Handle sectionTitle functionality with proper error handling.
     * @return string
     */
    #[Computed]
    public function sectionTitle(): string
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

    /**
     * Get section title method for Blade view compatibility.
     * @return string
     */
    public function getSectionTitle(): string
    {
        return $this->sectionTitle;
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        return view('livewire.components.advanced-related-products');
    }
}

