<?php declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

final class ProductReviews extends Component
{
    use WithPagination;

    public Product $product;
    public bool $showReviewForm = false;

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('required|string|max:2000')]
    public string $content = '';

    #[Validate('required|integer|min:1|max:5')]
    public int $rating = 5;

    public function mount(Product $product): void
    {
        $this->product = $product;
    }

    public function toggleReviewForm(): void
    {
        if (!Auth::check()) {
            $this->redirect(route('login'));
            return;
        }

        $this->showReviewForm = !$this->showReviewForm;
        
        if (!$this->showReviewForm) {
            $this->reset(['title', 'content', 'rating']);
            $this->resetValidation();
        }
    }

    public function submitReview(): void
    {
        if (!Auth::check()) {
            $this->redirect(route('login'));
            return;
        }

        $this->validate();

        // Check if user already reviewed this product
        $existingReview = Review::where('product_id', $this->product->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingReview) {
            $this->addError('review', __('translations.already_reviewed_product'));
            return;
        }

        Review::create([
            'product_id' => $this->product->id,
            'user_id' => Auth::id(),
            'title' => $this->title,
            'content' => $this->content,
            'rating' => $this->rating,
            'is_approved' => false, // Reviews need approval
        ]);

        $this->reset(['title', 'content', 'rating', 'showReviewForm']);
        $this->resetValidation();

        session()->flash('success', __('translations.review_submitted_for_approval'));
    }

    public function render(): View
    {
        $reviews = Review::with('user')
            ->where('product_id', $this->product->id)
            ->approved()
            ->latest()
            ->paginate(10);

        $averageRating = Review::where('product_id', $this->product->id)
            ->approved()
            ->avg('rating');

        $ratingDistribution = Review::where('product_id', $this->product->id)
            ->approved()
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->pluck('count', 'rating')
            ->toArray();

        return view('livewire.components.product-reviews', [
            'reviews' => $reviews,
            'averageRating' => round($averageRating, 1),
            'totalReviews' => $reviews->total(),
            'ratingDistribution' => $ratingDistribution,
        ]);
    }
}
