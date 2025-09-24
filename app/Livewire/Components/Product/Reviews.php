<?php

declare(strict_types=1);

namespace App\Livewire\Components\Product;

use App\Models\Product;
use App\Models\Review;
use Livewire\Component;
use Livewire\WithPagination;

final class Reviews extends Component
{
    use WithPagination;

    public Product $product;

    public int $productId;

    public function mount(int $productId): void
    {
        $this->productId = $productId;
        $this->product = Product::findOrFail($productId);
    }

    public function getReviewsProperty()
    {
        return Review::where('product_id', $this->productId)
            ->where('is_approved', true)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.components.product.reviews', [
            'reviews' => $this->reviews,
        ]);
    }
}
