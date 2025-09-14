<?php

declare(strict_types=1);

namespace App\Livewire\Components\Product;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Reviews
 * 
 * Livewire component for reactive frontend functionality.
 */
class Reviews extends Component
{
    use WithPagination;

    public int $productId;

    public function mount(int $productId): void
    {
        $this->productId = $productId;
    }

    protected $listeners = ['review-submitted' => '$refresh'];

    public function render(): View
    {
        $reviews = Review::query()
            ->where('reviewrateable_type', app(Product::class)->getMorphClass())
            ->where('reviewrateable_id', $this->productId)
            ->where('approved', true)
            ->latest('id')
            ->paginate(10);

        return view('livewire.components.product.reviews', compact('reviews'));
    }
}
