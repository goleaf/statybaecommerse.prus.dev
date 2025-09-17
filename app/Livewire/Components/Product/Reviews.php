<?php declare(strict_types=1);

namespace App\Livewire\Components\Product;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Reviews
 *
 * Livewire component for Reviews with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property int $productId
 * @property mixed $listeners
 */
class Reviews extends Component
{
    use WithPagination;

    public int $productId;

    /**
     * Initialize the Livewire component with parameters.
     * @param int $productId
     * @return void
     */
    public function mount(int $productId): void
    {
        $this->productId = $productId;
    }

    protected $listeners = ['review-submitted' => '$refresh'];

    /**
     * Handle reviews functionality with proper error handling.
     * @return LengthAwarePaginator
     */
    #[Computed]
    public function reviews(): LengthAwarePaginator
    {
        return Review::query()
            ->where('product_id', $this->productId)
            ->where('is_approved', true)
            ->latest('id')
            ->paginate(10);
    }

    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    public function render(): View
    {
        return view('livewire.components.product.reviews');
    }
}
