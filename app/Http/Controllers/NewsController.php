<?php

declare (strict_types=1);
namespace App\Http\Controllers;

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\NewsTag;
use App\Services\PaginationService;
use Illuminate\Http\Request;
use Illuminate\View\View;
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
     * @return View
     */
    public function index(Request $request): View
    {
        $query = News::published()->with(['categories', 'tags', 'images'])->withCount('comments');
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->search($search);
        }
        // Category filter
        if ($request->filled('category')) {
            $query->byCategory((int) $request->get('category'));
        }
        // Tag filter
        if ($request->filled('tag')) {
            $query->byTag((int) $request->get('tag'));
        }
        // Featured filter
        if ($request->boolean('featured')) {
            $query->featured();
        }
        $news = PaginationService::paginateWithContext($query->orderBy('published_at', 'desc'), 'news');
        $categories = NewsCategory::visible()->with('translations')->get();
        $tags = NewsTag::visible()->with('translations')->get();
        $featuredNews = News::published()->featured()->with(['categories', 'tags', 'images'])->orderBy('published_at', 'desc')->limit(3)->get()->skipWhile(function ($news) {
            // Skip news items that are not properly configured for display
            return empty($news->title) || empty($news->slug) || !$news->is_published || empty($news->getFirstMediaUrl('images'));
        });
        return view('news.index', compact('news', 'categories', 'tags', 'featuredNews'));
    }
    /**
     * Display the specified resource with related data.
     * @param string $slug
     * @return View
     */
    public function show(string $slug): View
    {
        $news = News::published()->whereHas('translations', function ($query) use ($slug) {
            $query->where('slug', $slug)->where('locale', app()->getLocale());
        })->with(['categories', 'tags', 'images', 'comments' => function ($query) {
            $query->approved()->visible()->topLevel()->with('replies');
        }])->firstOrFail();
        // Increment view count
        $news->incrementViewCount();
        // Get related news
        $relatedNews = News::published()->where('id', '!=', $news->id)->whereHas('categories', function ($query) use ($news) {
            $query->whereIn('news_category_id', $news->categories->pluck('id'));
        })->with(['categories', 'tags', 'images'])->limit(4)->get()->skipWhile(function ($relatedNews) {
            // Skip related news items that are not properly configured for display
            return empty($relatedNews->title) || empty($relatedNews->slug) || !$relatedNews->is_published || empty($relatedNews->getFirstMediaUrl('images'));
        });
        return view('news.show', compact('news', 'relatedNews'));
    }
    /**
     * Handle category functionality with proper error handling.
     * @param string $slug
     * @return View
     */
    public function category(string $slug): View
    {
        $category = NewsCategory::visible()->whereHas('translations', function ($query) use ($slug) {
            $query->where('slug', $slug)->where('locale', app()->getLocale());
        })->firstOrFail();
        $news = News::published()->byCategory($category->id)->with(['categories', 'tags', 'images'])->orderBy('published_at', 'desc')->paginate(12);
        $categories = NewsCategory::visible()->with('translations')->get();
        $tags = NewsTag::visible()->with('translations')->get();
        return view('news.category', compact('news', 'category', 'categories', 'tags'));
    }
    /**
     * Handle tag functionality with proper error handling.
     * @param string $slug
     * @return View
     */
    public function tag(string $slug): View
    {
        $tag = NewsTag::visible()->whereHas('translations', function ($query) use ($slug) {
            $query->where('slug', $slug)->where('locale', app()->getLocale());
        })->firstOrFail();
        $news = News::published()->byTag($tag->id)->with(['categories', 'tags', 'images'])->orderBy('published_at', 'desc')->paginate(12);
        $categories = NewsCategory::visible()->with('translations')->get();
        $tags = NewsTag::visible()->with('translations')->get();
        return view('news.tag', compact('news', 'tag', 'categories', 'tags'));
    }
}
