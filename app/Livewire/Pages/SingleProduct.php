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
            ->with(['brand', 'category', 'media', 'variants', 'reviews'])
            ->firstOrFail();
    }

    public function getRelatedProductsProperty()
    {
        return Product::where('category_id', $this->product->category_id)
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