<?php declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Product;
use Livewire\Component;

final class SingleProduct extends Component
{
    public Product $product;

    public function mount(string $slug): void
    {
        $this->product = Product::where('slug', $slug)
            ->where('is_visible', true)
            ->with(['brand', 'categories', 'media', 'variants', 'reviews'])
            ->firstOrFail();
    }

    public function getRelatedProductsProperty()
    {
        // Get related products from the same categories
        $categoryIds = $this->product->categories->pluck('id')->toArray();
        
        if (empty($categoryIds)) {
            return collect();
        }
        
        return Product::whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('category_id', $categoryIds);
            })
            ->where('id', '!=', $this->product->id)
            ->where('is_visible', true)
            ->with(['media', 'brand'])
            ->limit(4)
            ->get();
    }

    public function render()
    {
        return view('livewire.pages.single-product', [
            'relatedProducts' => $this->relatedProducts,
        ])->layout('components.layouts.base', [
            'title' => $this->product->name
        ]);
    }
}