<?php declare(strict_types=1);

namespace App\Livewire\Components\Product;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * ReviewForm
 *
 * Livewire component for ReviewForm with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property int $productId
 * @property string $title
 * @property string $content
 * @property int $rating
 */
class ReviewForm extends Component
{
    public int $productId;

    #[Validate('required|string|min:3|max:150')]
    public string $title = '';

    #[Validate('required|string|min:10|max:2000')]
    public string $content = '';

    #[Validate('required|integer|min:1|max:5')]
    public int $rating = 5;

    /**
     * Initialize the Livewire component with parameters.
     * @param int $productId
     * @return void
     */
    public function mount(int $productId): void
    {
        $this->productId = $productId;
    }

    /**
     * Handle submit functionality with proper error handling.
     * @return void
     */
    public function submit(): void
    {
        $this->validate();
        abort_unless(Auth::check(), 403);
        $user = Auth::user();
        Review::query()->create([
            'product_id' => $this->productId,
            'user_id' => $user->id,
            'reviewer_name' => $user->name,
            'reviewer_email' => $user->email,
            'title' => $this->title,
            'content' => $this->content,
            'rating' => $this->rating,
            'is_approved' => false
        ]);
        $this->reset(['title', 'content', 'rating']);
        $this->dispatch('review-submitted');
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        return view('livewire.components.product.review-form');
    }
}
