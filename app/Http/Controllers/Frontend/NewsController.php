<?php

declare (strict_types=1);
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\NewsTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\LazyCollection;
/**
 * NewsController
 * 
 * HTTP controller handling NewsController related web requests, responses, and business logic with proper validation and error handling.
 * 
 */
final class NewsController extends Controller
{
    /**
     * Display a listing of the resource with pagination and filtering.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = News::published()->with(['categories', 'tags', 'images'])->withCount('comments');
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->search($search);
        }
        // Category filter
        if ($request->filled('category')) {
            $category = $request->get('category');
            $query->whereHas('categories', function ($q) use ($category) {
                $q->where('slug', $category);
            });
        }
        // Tag filter
        if ($request->filled('tag')) {
            $tag = $request->get('tag');
            $query->whereHas('tags', function ($q) use ($tag) {
                $q->where('slug', $tag);
            });
        }
        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('published_at', '>=', $request->get('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('published_at', '<=', $request->get('to_date'));
        }
        // Sorting
        $sortBy = $request->get('sort', 'published_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);
        // Pagination
        $perPage = min((int) $request->get('per_page', 15), 50);
        $news = $query->paginate($perPage);
        return response()->json(['success' => true, 'data' => $news->items(), 'pagination' => ['current_page' => $news->currentPage(), 'last_page' => $news->lastPage(), 'per_page' => $news->perPage(), 'total' => $news->total(), 'from' => $news->firstItem(), 'to' => $news->lastItem()]]);
    }
    /**
     * Display the specified resource with related data.
     * @param string $slug
     * @return JsonResponse
     */
    public function show(string $slug): JsonResponse
    {
        $news = News::published()->where('slug', $slug)->with(['categories', 'tags', 'images', 'comments' => function ($query) {
            $query->approved()->with('user');
        }])->withCount('comments')->first();
        if (!$news) {
            return response()->json(['success' => false, 'message' => __('api.news_not_found')], 404);
        }
        // Increment view count
        $news->increment('views_count');
        return response()->json(['success' => true, 'data' => ['id' => $news->id, 'title' => $news->title, 'slug' => $news->slug, 'excerpt' => $news->excerpt, 'content' => $news->content, 'featured_image' => $news->featured_image, 'author' => $news->author, 'published_at' => $news->published_at, 'views_count' => $news->views_count, 'comments_count' => $news->comments_count, 'categories' => $news->categories->map(function ($category) {
            return ['id' => $category->id, 'name' => $category->name, 'slug' => $category->slug];
        }), 'tags' => $news->tags->map(function ($tag) {
            return ['id' => $tag->id, 'name' => $tag->name, 'slug' => $tag->slug];
        }), 'images' => $news->images->map(function ($image) {
            return ['id' => $image->id, 'url' => $image->getUrl(), 'alt' => $image->alt_text, 'caption' => $image->caption];
        }), 'comments' => $news->comments->map(function ($comment) {
            return ['id' => $comment->id, 'content' => $comment->content, 'author' => $comment->user->name, 'created_at' => $comment->created_at];
        })]]);
    }
    /**
     * Handle featured functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function featured(Request $request): JsonResponse
    {
        $limit = min((int) $request->get('limit', 5), 10);
        $timeout = now()->addSeconds(10);
        // 10 second timeout for featured news
        $featuredNews = News::published()->where('is_featured', true)->with(['categories', 'tags'])->orderBy('published_at', 'desc')->limit($limit)->cursor()->takeUntilTimeout($timeout)->collect()->skipWhile(function ($news) {
            // Skip news items that are not properly configured for display
            return empty($news->title) || empty($news->slug) || !$news->is_published || empty($news->excerpt);
        });
        return response()->json(['success' => true, 'data' => $featuredNews->map(function ($news) {
            return ['id' => $news->id, 'title' => $news->title, 'slug' => $news->slug, 'excerpt' => $news->excerpt, 'featured_image' => $news->featured_image, 'published_at' => $news->published_at, 'categories' => $news->categories->pluck('name')];
        })]);
    }
    /**
     * Handle categories functionality with proper error handling.
     * @return JsonResponse
     */
    public function categories(): JsonResponse
    {
        $categories = NewsCategory::active()->withCount('news')->orderBy('name')->get()->skipWhile(function ($category) {
            // Skip categories that are not properly configured for display
            return empty($category->name) || empty($category->slug) || !$category->is_active || $category->news_count <= 0;
        });
        return response()->json(['success' => true, 'data' => $categories->map(function ($category) {
            return ['id' => $category->id, 'name' => $category->name, 'slug' => $category->slug, 'description' => $category->description, 'news_count' => $category->news_count];
        })]);
    }
    /**
     * Handle tags functionality with proper error handling.
     * @return JsonResponse
     */
    public function tags(): JsonResponse
    {
        $tags = NewsTag::active()->withCount('news')->orderBy('name')->get()->skipWhile(function ($tag) {
            // Skip tags that are not properly configured for display
            return empty($tag->name) || empty($tag->slug) || !$tag->is_active || $tag->news_count <= 0;
        });
        return response()->json(['success' => true, 'data' => $tags->map(function ($tag) {
            return ['id' => $tag->id, 'name' => $tag->name, 'slug' => $tag->slug, 'news_count' => $tag->news_count];
        })]);
    }
    /**
     * Handle related functionality with proper error handling.
     * @param string $slug
     * @param Request $request
     * @return JsonResponse
     */
    public function related(string $slug, Request $request): JsonResponse
    {
        $limit = min((int) $request->get('limit', 5), 10);
        $news = News::published()->where('slug', $slug)->with('categories')->first();
        if (!$news) {
            return response()->json(['success' => false, 'message' => __('api.news_not_found')], 404);
        }
        $categoryIds = $news->categories->pluck('id');
        $relatedNews = News::published()->where('id', '!=', $news->id)->whereHas('categories', function ($query) use ($categoryIds) {
            $query->whereIn('id', $categoryIds);
        })->with(['categories'])->orderBy('published_at', 'desc')->limit($limit)->get()->skipWhile(function ($related) {
            // Skip related news items that are not properly configured for display
            return empty($related->title) || empty($related->slug) || !$related->is_published || empty($related->excerpt);
        });
        return response()->json(['success' => true, 'data' => $relatedNews->map(function ($related) {
            return ['id' => $related->id, 'title' => $related->title, 'slug' => $related->slug, 'excerpt' => $related->excerpt, 'featured_image' => $related->featured_image, 'published_at' => $related->published_at, 'categories' => $related->categories->pluck('name')];
        })]);
    }
}