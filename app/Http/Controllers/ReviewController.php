<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\ReviewData;
use App\Http\Requests\ReportReviewRequest;
use App\Models\Product;
use App\Models\Review;
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
    public function show(Review $review): View
    {
        if (! $review->is_approved) {
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
        $metadata = $review->metadata ?? [];
        $likedBy = collect($metadata['liked_by'] ?? []);

        if ($likedBy->contains($userId)) {
            return response()->json([
                'message'        => __('You have already marked this review as helpful.'),
                'helpful_count'  => (int) ($review->helpful_count ?? $likedBy->count()),
                'reported_count' => (int) ($review->reported_count ?? 0),
            ]);
        }

        $likedBy = $likedBy->push($userId)->unique()->values();
        $metadata['liked_by'] = $likedBy->all();

        DB::transaction(function () use ($review, $metadata, $likedBy): void {
            $review->metadata = $metadata;
            $review->helpful_count = $likedBy->count();
            $review->save();
        });

        $review->refresh();

        return response()->json([
            'message'        => __('Thanks for your feedback!'),
            'helpful_count'  => (int) ($review->helpful_count ?? $likedBy->count()),
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
        $metadata = $review->metadata ?? [];
        $reportedBy = collect($metadata['reported_by'] ?? []);

        if ($reportedBy->contains($userId)) {
            return response()->json([
                'message'        => __('You have already reported this review.'),
                'helpful_count'  => (int) ($review->helpful_count ?? 0),
                'reported_count' => (int) ($review->reported_count ?? $reportedBy->count()),
            ]);
        }

        $reportedBy = $reportedBy->push($userId)->unique()->values();
        $metadata['reported_by'] = $reportedBy->all();

        if (($validated['reason'] ?? null) !== null && $validated['reason'] !== '') {
            $reasons = $metadata['reported_reasons'] ?? [];
            $reasons[(string) $userId] = $validated['reason'];
            $metadata['reported_reasons'] = $reasons;
        }

        DB::transaction(function () use ($review, $metadata, $reportedBy): void {
            $review->metadata = $metadata;
            $review->reported_count = $reportedBy->count();
            $review->save();
        });

        $review->refresh();

        return response()->json([
            'message'        => __('Thanks for letting us know.'),
            'helpful_count'  => (int) ($review->helpful_count ?? 0),
            'reported_count' => (int) ($review->reported_count ?? $reportedBy->count()),
        ]);
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
