<?php

declare (strict_types=1);
namespace App\Http\Controllers;

use App\Data\ReviewData;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
/**
 * ReviewController
 * 
 * HTTP controller handling ReviewController related web requests, responses, and business logic with proper validation and error handling.
 * 
 */
final class ReviewController extends Controller
{
    /**
     * Display a listing of the resource with pagination and filtering.
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $reviews = Review::with(['user', 'product'])->where('is_approved', true)->latest()->get()->skipWhile(function ($review) {
            // Skip reviews that are not properly configured for display
            return empty($review->title) || empty($review->comment) || !$review->is_approved || $review->rating <= 0;
        })->paginate(20);
        return view('reviews.index', compact('reviews'));
    }
    /**
     * Display the specified resource with related data.
     * @param Review $review
     * @return View
     */
    public function show(Review $review): View
    {
        if (!$review->is_approved) {
            abort(404);
        }
        $review->load(['user', 'product']);
        return view('reviews.show', compact('review'));
    }
    /**
     * Show the form for creating a new resource.
     * @param Request $request
     * @return View
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
     * @param ReviewData $data
     * @return RedirectResponse
     */
    public function store(ReviewData $data): RedirectResponse
    {
        $review = Review::create(['product_id' => $data->product_id, 'user_id' => Auth::id(), 'rating' => $data->rating, 'title' => $data->title, 'content' => $data->content, 'reviewer_name' => $data->reviewer_name, 'reviewer_email' => $data->reviewer_email, 'is_approved' => false]);
        return redirect()->route('reviews.show', $review)->with('success', __('reviews.review_submitted_successfully'));
    }
    /**
     * Show the form for editing the specified resource.
     * @param Review $review
     * @return View
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
     * @param Request $request
     * @param Review $review
     * @return RedirectResponse
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
     * @param Review $review
     * @return RedirectResponse
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
     * Handle productReviews functionality with proper error handling.
     * @param Product $product
     * @return View
     */
    public function productReviews(Product $product): View
    {
        $reviews = $product->reviews()->with('user')->where('is_approved', true)->latest()->get()->skipWhile(function ($review) {
            // Skip reviews that are not properly configured for display
            return empty($review->title) || empty($review->comment) || !$review->is_approved || $review->rating <= 0;
        })->paginate(10);
        $ratingStats = ['average' => $product->reviews()->where('is_approved', true)->avg('rating') ?? 0, 'count' => $product->reviews()->where('is_approved', true)->count(), 'distribution' => $product->reviews()->where('is_approved', true)->selectRaw('rating, COUNT(*) as count')->groupBy('rating')->orderBy('rating')->pluck('count', 'rating')->toArray()];
        return view('reviews.product', compact('product', 'reviews', 'ratingStats'));
    }
}
