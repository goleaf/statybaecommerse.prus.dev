<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

final /**
 * ReviewController
 * 
 * HTTP controller handling web requests and responses.
 */
class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $reviews = Review::with(['user', 'product'])
            ->where('is_approved', true)
            ->latest()
            ->paginate(20);

        return view('reviews.index', compact('reviews'));
    }

    public function show(Review $review): View
    {
        if (! $review->is_approved) {
            abort(404);
        }

        $review->load(['user', 'product']);

        return view('reviews.show', compact('review'));
    }

    public function create(Request $request): View
    {
        $productId = $request->get('product_id');
        $product = null;

        if ($productId) {
            $product = Product::findOrFail($productId);
        }

        return view('reviews.create', compact('product'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:2000',
            'reviewer_name' => 'required|string|max:255',
            'reviewer_email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $review = Review::create([
            'product_id' => $request->product_id,
            'user_id' => Auth::id(),
            'rating' => $request->rating,
            'title' => $request->title,
            'comment' => $request->comment,
            'reviewer_name' => $request->reviewer_name,
            'reviewer_email' => $request->reviewer_email,
            'locale' => app()->getLocale(),
            'is_approved' => false,
        ]);

        return redirect()->route('reviews.show', $review)
            ->with('success', __('reviews.review_submitted_successfully'));
    }

    public function edit(Review $review): View
    {
        if (Auth::id() !== $review->user_id) {
            abort(403);
        }

        return view('reviews.edit', compact('review'));
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        if (Auth::id() !== $review->user_id) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $review->update([
            'rating' => $request->rating,
            'title' => $request->title,
            'comment' => $request->comment,
            'is_approved' => false, // Reset approval status when edited
        ]);

        return redirect()->route('reviews.show', $review)
            ->with('success', __('reviews.review_updated_successfully'));
    }

    public function destroy(Review $review): RedirectResponse
    {
        if (Auth::id() !== $review->user_id) {
            abort(403);
        }

        $review->delete();

        return redirect()->route('reviews.index')
            ->with('success', __('reviews.review_deleted_successfully'));
    }

    public function productReviews(Product $product): View
    {
        $reviews = $product->reviews()
            ->with('user')
            ->where('is_approved', true)
            ->latest()
            ->paginate(10);

        $ratingStats = [
            'average' => $product->reviews()->where('is_approved', true)->avg('rating') ?? 0,
            'count' => $product->reviews()->where('is_approved', true)->count(),
            'distribution' => $product->reviews()
                ->where('is_approved', true)
                ->selectRaw('rating, COUNT(*) as count')
                ->groupBy('rating')
                ->orderBy('rating')
                ->pluck('count', 'rating')
                ->toArray(),
        ];

        return view('reviews.product', compact('product', 'reviews', 'ratingStats'));
    }
}
