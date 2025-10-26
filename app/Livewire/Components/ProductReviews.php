<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Livewire\Concerns\WithNotifications;
use App\Models\Product;
use App\Models\Review;
use App\Models\Scopes\ApprovedScope;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * ProductReviews
 *
 * Livewire component for ProductReviews with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property Product $product
 * @property bool    $showReviewForm
 * @property string  $title
 * @property string  $content
 * @property int     $rating
 */
final class ProductReviews extends Component
{
    use WithNotifications;
    use WithPagination;

    public Product $product;

    public bool $showReviewForm = false;

    public bool $showLoginPrompt = false;

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('required|string|max:2000')]
    public string $content = '';

    #[Validate('required|integer|min:1|max:5')]
    public int $rating = 5;

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(Product $product): void
    {
        $this->product = $product;
    }

    /**
     * Handle toggleReviewForm functionality with proper error handling.
     */
    public function toggleReviewForm(): void
    {
        if (! Auth::check()) {
            // Toggle a dedicated login prompt instead of redirecting guests away from the product page.
            $this->promptLogin();

            return;
        }
        $this->showLoginPrompt = false;
        $this->showReviewForm = ! $this->showReviewForm;
        if (! $this->showReviewForm) {
            $this->reset(['title', 'content', 'rating']);
            $this->resetValidation();
        }
    }

    /**
     * Handle submitReview functionality with proper error handling.
     */
    public function submitReview(): void
    {
        if (! Auth::check()) {
            // Surface the login prompt for guests attempting to submit a review.
            $this->promptLogin();

            return;
        }
        $this->validate();
        // Check if user already reviewed this product
        $existingReview = Review::where('product_id', $this->product->id)->where('user_id', Auth::id())->first();
        if ($existingReview) {
            $this->addError('review', __('translations.already_reviewed_product'));

            return;
        }
        // Persist the pending review while keeping the request wrapped in a transaction for consistency.
        DB::transaction(function (): void {
            Review::create([
                'product_id'  => $this->product->id,
                'user_id'     => Auth::id(),
                'title'       => $this->title,
                'content'     => $this->content,
                'rating'      => $this->rating,
                'is_approved' => false,
                'locale'      => app()->getLocale(),
            ]);
        });
        $this->reset(['title', 'content', 'rating', 'showReviewForm']);
        $this->resetValidation();
        session()->flash('success', __('translations.review_submitted_for_approval'));
    }

    /**
     * Prompt guests to authenticate before interacting with review features.
     */
    public function promptLogin(): void
    {
        $this->showReviewForm = false;
        $this->showLoginPrompt = true;
        $this->notifyInfo(__('translations.login_required_to_review_prompt'));
    }

    /**
     * Hide the login prompt when the customer dismisses it.
     */
    public function hideLoginPrompt(): void
    {
        $this->showLoginPrompt = false;
    }

    /**
     * Register a helpful vote for the specified review.
     */
    public function markReviewHelpful(int $reviewId): void
    {
        if (! Auth::check()) {
            // Require authentication before recording helpful votes.
            $this->promptLogin();

            return;
        }
        $review = $this->findReviewForInteraction($reviewId);
        if (! $review instanceof \App\Models\Review) {
            return;
        }

        $metadata = $this->normaliseMetadata($review);
        $userId = (int) Auth::id();
        $likedByRaw = $metadata['liked_by'] ?? [];
        if (! is_array($likedByRaw)) {
            $likedByRaw = [];
        }
        $likedBy = [];
        foreach ($likedByRaw as $value) {
            if (! is_int($value) && ! is_string($value) && ! is_float($value)) {
                continue;
            }

            $likedBy[] = (int) $value;
        }
        $likedBy = array_values(array_unique($likedBy));
        if (in_array($userId, $likedBy, true)) {
            $this->notifyInfo(__('translations.review_already_marked_helpful'));

            return;
        }

        $likedBy[] = $userId;
        $metadata['liked_by'] = array_values(array_unique($likedBy));

        try {
            // Persist the updated helpful metadata atomically.
            DB::transaction(static function () use ($review, $metadata): void {
                $review->setAttribute('metadata', $metadata);
                $review->setAttribute('helpful_count', count($metadata['liked_by']));
                $review->save();
            });
            $this->notifySuccess(__('Thanks for your feedback!'));
        } catch (QueryException $exception) {
            report($exception);
            $this->notifyError(__('translations.review_feedback_failed'));
        }
    }

    /**
     * Submit a report for the specified review.
     */
    public function reportReview(int $reviewId): void
    {
        if (! Auth::check()) {
            // Guests must authenticate before reporting a review.
            $this->promptLogin();

            return;
        }
        $review = $this->findReviewForInteraction($reviewId);
        if (! $review instanceof \App\Models\Review) {
            return;
        }

        $metadata = $this->normaliseMetadata($review);
        $userId = (int) Auth::id();
        $reportedByRaw = $metadata['reported_by'] ?? [];
        if (! is_array($reportedByRaw)) {
            $reportedByRaw = [];
        }
        $reportedBy = [];
        foreach ($reportedByRaw as $value) {
            if (! is_int($value) && ! is_string($value) && ! is_float($value)) {
                continue;
            }

            $reportedBy[] = (int) $value;
        }
        $reportedBy = array_values(array_unique($reportedBy));
        if (in_array($userId, $reportedBy, true)) {
            $this->notifyInfo(__('translations.review_already_reported'));

            return;
        }

        $reportedBy[] = $userId;
        $metadata['reported_by'] = array_values(array_unique($reportedBy));

        try {
            // Persist the report metadata inside a transaction for accuracy.
            DB::transaction(static function () use ($review, $metadata): void {
                $review->setAttribute('metadata', $metadata);
                $review->setAttribute('reported_count', count($metadata['reported_by']));
                $review->save();
            });
            $this->notifySuccess(__('Thanks for letting us know.'));
        } catch (QueryException $exception) {
            report($exception);
            $this->notifyError(__('translations.review_feedback_failed'));
        }
    }

    /**
     * Resolve a pending review created by the authenticated customer.
     */
    private function pendingReview(): ?Review
    {
        if (! Auth::check()) {
            return null;
        }

        return Review::withoutGlobalScope(ApprovedScope::class)
            ->with('user')
            ->where('product_id', $this->product->id)
            ->where('user_id', Auth::id())
            ->where('is_approved', false)
            ->whereNull('rejected_at')
            ->latest('created_at')
            ->first();
    }

    /**
     * Locate a review that can be interacted with from the storefront.
     */
    private function findReviewForInteraction(int $reviewId): ?Review
    {
        /** @var Review|null $review */
        $review = Review::withoutGlobalScope(ApprovedScope::class)
            ->where('product_id', $this->product->id)
            ->where('is_approved', true)
            ->find($reviewId);

        return $review;
    }

    /**
     * Normalise the metadata payload for the provided review instance.
     */
    /**
     * @return array<string, mixed>
     */
    private function normaliseMetadata(Review $review): array
    {
        $metadata = $review->metadata ?? [];
        if (! is_array($metadata)) {
            $metadata = [];
        }

        return $metadata;
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        $reviews = Review::with('user')->where('product_id', $this->product->id)->approved()->latest()->paginate(10);
        $averageRating = Review::where('product_id', $this->product->id)->approved()->avg('rating');
        $ratingDistribution = Review::where('product_id', $this->product->id)->approved()->selectRaw('rating, COUNT(*) as count')->groupBy('rating')->orderBy('rating', 'desc')->pluck('count', 'rating')->toArray();

        return view('livewire.components.product-reviews', [
            'reviews'            => $reviews,
            'averageRating'      => round((float) $averageRating, 1),
            'totalReviews'       => $reviews->total(),
            'ratingDistribution' => $ratingDistribution,
            'pendingReview'      => $this->pendingReview(),
        ]);
    }
}
