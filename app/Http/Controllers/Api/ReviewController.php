<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Events\ReviewSubmittedForModeration;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Product;
use App\Models\Review;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\ApprovedScope;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * ReviewController
 *
 * API controller responsible for handling authenticated review submissions
 * while coordinating duplicate detection, aggregation updates, and moderation.
 */
final class ReviewController extends Controller
{
    /**
     * Store a newly submitted review and hand it off for moderation.
     */
    public function store(StoreReviewRequest $request, Product $product): JsonResponse
    {
        $user = $request->user();

        // Defensive guard that should only trigger if the auth middleware was bypassed.
        if ($user === null) {
            abort(Response::HTTP_UNAUTHORIZED, __('auth.unauthenticated'));
        }

        $payload = $request->validated();

        // Prevent duplicates for the same product, order, and user combination.
        $duplicateExists = Review::query()
            ->withoutGlobalScopes([ActiveScope::class, ApprovedScope::class])
            ->where('product_id', $product->getKey())
            ->where('user_id', $user->getKey())
            ->where('metadata->order_id', $payload['order_id'])
            ->exists();

        if ($duplicateExists) {
            return response()->json([
                'message' => __('You have already submitted a review for this order.'),
            ], Response::HTTP_CONFLICT);
        }

        // Build the metadata payload while preserving null-friendly entries.
        $metadata = array_filter([
            'order_id' => $payload['order_id'],
        ], static fn ($value): bool => $value !== null);

        // Persist the review in a scope-free context so pending submissions are visible to moderators.
        $review = Review::query()
            ->withoutGlobalScopes([ActiveScope::class, ApprovedScope::class])
            ->create([
                'product_id' => $product->getKey(),
                'user_id' => $user->getKey(),
                'reviewer_name' => $user->name ?? $user->email ?? 'Guest',
                'reviewer_email' => $user->email ?? 'guest@example.com',
                'rating' => $payload['rating'],
                'title' => $payload['title'] ?? null,
                'content' => $payload['content'],
                'is_approved' => false,
                'is_featured' => false,
                'is_verified_purchase' => $request->verifiedPurchase(),
                'locale' => app()->getLocale(),
                'metadata' => $metadata,
            ]);

        // Reload the product aggregates so the response reflects the latest counts and averages.
        $product->loadAvg('reviews as average_rating', 'rating');
        $product->loadCount('reviews');
        $review->setRelation('product', $product);

        // Dispatch the moderation event so downstream processing can run asynchronously.
        ReviewSubmittedForModeration::dispatch($review);

        return ReviewResource::make($review)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
