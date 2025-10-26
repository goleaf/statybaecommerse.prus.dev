<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\ReviewData;
use App\Http\Requests\ReportReviewRequest;
use App\Models\Product;
use App\Models\Review;
use App\Models\Scopes\ApprovedScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

/**
 * ReviewController
 *
 * HTTP controller handling ReviewController related web requests, responses, and business logic with proper validation and error handling.
 */
final class ReviewController extends Controller
{
    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(Request $request): View
    {
        $reviews = Review::query()
            ->with(['user', 'product'])
            ->where('is_approved', true)
            ->where(function (Builder $query): void {
                // Ensure that only reviews with visible titles and content reach the paginator
                $query->whereNotNull('title')->where('title', '!=', '')->whereNotNull('content')->where('content', '!=', '');
            })
            ->where('rating', '>', 0)
            ->latest()
            ->paginate(20);

        return view('reviews.index', compact('reviews'));
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(string $reviewId): View
    {
        $review = Review::withoutGlobalScope(ApprovedScope::class)->findOrFail($reviewId);

        // Allow review owners to preview their own pending submissions while still hiding unapproved
        // entries from the public catalogue.
        if (! $review->is_approved && Auth::id() !== $review->user_id) {
            abort(404);
        }
        $review->load(['user', 'product']);

        return view('reviews.show', compact('review'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $productId = $request->get('product_id');
        $product = null;
        if ($productId) {
            $product = Product::findOrFail($productId);
        }

        return view('reviews.create', compact('product'));
    }

    /**
     * Store a newly created resource in storage with validation.
     */
    public function store(ReviewData $data): RedirectResponse
    {
        if (! Auth::check()) {
            // Halt orphaned submissions created by unauthenticated visitors to maintain ownership integrity.
            abort(401, (string) __('auth.unauthenticated'));
        }

        $review = Review::create(['product_id' => $data->product_id, 'user_id' => Auth::id(), 'rating' => $data->rating, 'title' => $data->title, 'content' => $data->content, 'reviewer_name' => $data->reviewer_name, 'reviewer_email' => $data->reviewer_email, 'locale' => app()->getLocale(), 'is_approved' => false]);

        return redirect()->route('reviews.show', $review)->with('success', __('reviews.review_submitted_successfully'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Review $review): View
    {
        if (Auth::id() !== $review->user_id) {
            abort(403);
        }

        return view('reviews.edit', compact('review'));
    }

    /**
     * Update the specified resource in storage with validation.
     */
    public function update(Request $request, Review $review): RedirectResponse
    {
        if (! Auth::check()) {
            // A guest reaching this branch indicates a CSRF or automation attempt, so stop early.
            abort(401, (string) __('auth.unauthenticated'));
        }
        if (Auth::id() !== $review->user_id) {
            abort(403);
        }
        $validator = Validator::make($request->all(), ['rating' => 'required|integer|min:1|max:5', 'title' => 'nullable|string|max:255', 'content' => 'nullable|string|max:2000']);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $review->update(['rating' => $request->rating, 'title' => $request->title, 'content' => $request->content, 'is_approved' => false]);

        return redirect()->route('reviews.show', $review)->with('success', __('reviews.review_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review): RedirectResponse
    {
        if (! Auth::check()) {
            // Prevent background requests from deleting reviews without an authenticated owner.
            abort(401, (string) __('auth.unauthenticated'));
        }
        if (Auth::id() !== $review->user_id) {
            abort(403);
        }
        $review->delete();

        return redirect()->route('reviews.index')->with('success', __('reviews.review_deleted_successfully'));
    }

    /**
     * Register a "helpful" interaction for the specified review.
     */
    public function like(Request $request, Review $review): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['message' => __('auth.unauthenticated')], 401);
        }

        $userId = (int) Auth::id();
        $interaction = $this->syncInteractionMetadata($review, 'liked_by', $userId);

        if ($interaction['already']) {
            return response()->json([
                'message'        => __('You have already marked this review as helpful.'),
                'helpful_count'  => (int) ($review->helpful_count ?? $interaction['total']),
                'reported_count' => (int) ($review->reported_count ?? 0),
            ]);
        }

        DB::transaction(function () use ($review, $interaction): void {
            // Persist the de-duplicated metadata list so repeated clicks stay idempotent.
            $review->setAttribute('metadata', $interaction['metadata']);
            $review->setAttribute('helpful_count', $interaction['total']);
            $review->save();
        });

        $review->refresh();

        return response()->json([
            'message'        => __('Thanks for your feedback!'),
            'helpful_count'  => (int) ($review->helpful_count ?? $interaction['total']),
            'reported_count' => (int) ($review->reported_count ?? 0),
        ]);
    }

    /**
     * Register a report for the specified review.
     */
    public function report(ReportReviewRequest $request, Review $review): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['message' => __('auth.unauthenticated')], 401);
        }

        $validated = $request->validated();

        $userId = (int) Auth::id();
        $interaction = $this->syncInteractionMetadata(
            review: $review,
            interactionKey: 'reported_by',
            userId: $userId,
            options: ['reasonKey' => 'reported_reasons', 'reason' => $validated['reason'] ?? null],
        );

        if ($interaction['already']) {
            return response()->json([
                'message'        => __('You have already reported this review.'),
                'helpful_count'  => (int) ($review->helpful_count ?? 0),
                'reported_count' => (int) ($review->reported_count ?? $interaction['total']),
            ]);
        }

        DB::transaction(function () use ($review, $interaction): void {
            // Persist the interaction metadata alongside the running report tally.
            $review->setAttribute('metadata', $interaction['metadata']);
            $review->setAttribute('reported_count', $interaction['total']);
            $review->save();
        });

        $review->refresh();

        return response()->json([
            'message'        => __('Thanks for letting us know.'),
            'helpful_count'  => (int) ($review->helpful_count ?? 0),
            'reported_count' => (int) ($review->reported_count ?? $interaction['total']),
        ]);
    }

    /**
     * Ensure metadata stored on the review model is always returned as an associative array.
     *
     * @return array<int|string, mixed>
     */
    private function normaliseMetadata(Review $review): array
    {
        $metadata = $review->getAttribute('metadata');

        if (is_array($metadata)) {
            return $metadata;
        }

        if (! is_string($metadata)) {
            return [];
        }

        $decoded = json_decode($metadata, true);

        return is_array($decoded) ? (array) $decoded : [];
    }

    /**
     * Ensure helpful/report feedback arrays remain unique and optionally capture a reason payload.
     *
     * @param array{reasonKey?: string, reason?: string}|null $options
     *
     * @return array{metadata: array<int|string, mixed>, already: bool, total: int}
     */
    private function syncInteractionMetadata(Review $review, string $interactionKey, int $userId, ?array $options = null): array
    {
        $metadata = $this->normaliseMetadata($review);

        $rawList = $metadata[$interactionKey] ?? [];
        if (! is_array($rawList)) {
            $rawList = [];
        }

        // Convert the stored metadata into a strict list of unique user identifiers.
        $userIds = [];
        foreach ($rawList as $value) {
            if (is_int($value) || is_float($value) || is_string($value)) {
                $userIds[] = (int) $value;
            }
        }
        $userIds = array_values(array_unique($userIds));

        $alreadyParticipated = in_array($userId, $userIds, true);
        if ($alreadyParticipated) {
            return [
                'metadata' => $metadata,
                'already'  => true,
                'total'    => count($userIds),
            ];
        }

        $userIds[] = $userId;
        $metadata[$interactionKey] = array_values(array_unique($userIds));

        if ($options !== null) {
            $reasonKey = $options['reasonKey'] ?? null;
            $reason = $options['reason'] ?? null;
            if ($reasonKey !== null && $reason !== null && $reason !== '') {
                $existingReasons = $metadata[$reasonKey] ?? [];
                if (! is_array($existingReasons)) {
                    $existingReasons = [];
                }

                // Persist the latest reason per user so moderators can review context quickly.
                $existingReasons[(string) $userId] = $reason;
                $metadata[$reasonKey] = $existingReasons;
            }
        }

        return [
            'metadata' => $metadata,
            'already'  => false,
            'total'    => count($metadata[$interactionKey]),
        ];
    }

    /**
     * Handle productReviews functionality with proper error handling.
     */
    public function productReviews(Product $product): View
    {
        $reviews = $product->reviews()
            ->with('user')
            ->where('is_approved', true)
            ->where(function (Builder $query): void {
                // Enforce the same visibility rules used on the index so storefront pagination never crashes
                $query->whereNotNull('title')->where('title', '!=', '')->whereNotNull('content')->where('content', '!=', '');
            })
            ->where('rating', '>', 0)
            ->latest()
            ->paginate(10);
        $ratingStats = ['average' => $product->reviews()->where('is_approved', true)->avg('rating') ?? 0, 'count' => $product->reviews()->where('is_approved', true)->count(), 'distribution' => $product->reviews()->where('is_approved', true)->selectRaw('rating, COUNT(*) as count')->groupBy('rating')->orderBy('rating')->pluck('count', 'rating')->toArray()];

        return view('reviews.product', compact('product', 'reviews', 'ratingStats'));
    }
}
