<?php declare(strict_types=1);

namespace App\Livewire\Components\Product;

use App\Models\Review;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Reviews extends Component
{
    public int $productId;

    public function mount(int $productId): void
    {
        $this->productId = $productId;
    }

    protected $listeners = ['review-submitted' => '$refresh'];

    public function render(): View
    {
        $reviews = Review::query()
            ->where('reviewrateable_type', app(App\Models\Product::class)->getMorphClass())
            ->where('reviewrateable_id', $this->productId)
            ->where('approved', true)
            ->latest('id')
            ->limit(25)
            ->get();

        return view('livewire.components.product.reviews', compact('reviews'));
    }
}
