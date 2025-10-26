<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\NewsTag;
use App\Services\PaginationService;
use App\Support\SearchQuerySanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * NewsController
 *
 * HTTP controller handling NewsController related web requests, responses, and business logic with proper validation and error handling.
 */
final class NewsController extends Controller
{
    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = News::published()->with(['categories', 'tags', 'images'])->withCount('comments');

        $searchTerm = SearchQuerySanitizer::sanitize($request->get('search'));
        $categoryId = $request->filled('category') ? (int) $request->get('category') : null;
        $tagId = $request->filled('tag') ? (int) $request->get('tag') : null;
        $featuredOnly = $request->boolean('featured');

        // Search functionality
        if ($searchTerm !== '') {
            $query->search($searchTerm);
        }
        // Category filter
        if ($categoryId !== null) {
            $query->byCategory($categoryId);
        }
        // Tag filter
        if ($tagId !== null) {
            $query->byTag($tagId);
        }
        // Featured filter
        if ($featuredOnly) {
            $query->featured();
        }
        $news = PaginationService::paginateWithContext($query->orderBy('published_at', 'desc'), 'news');

        $appends = $request->except('page');

        if ($searchTerm === '') {
            unset($appends['search']);
        } else {
            $appends['search'] = $searchTerm;
        }

        if ($categoryId !== null) {
            $appends['category'] = $categoryId;
        }

        if ($tagId !== null) {
            $appends['tag'] = $tagId;
        }

        if ($featuredOnly) {
            $appends['featured'] = 1;
        } else {
            unset($appends['featured']);
        }

        $news = $news->appends($appends);
        $categories = NewsCategory::visible()->with('translations')->get();
        $tags = NewsTag::visible()->with('translations')->get();
        $featuredNews = News::published()
            ->featured()
            ->with(['categories', 'tags', 'images'])
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get()
            ->filter(function (News $news): bool {
                // Ensure carousel items expose localized content, moderation approval, and imagery.
                return $news->isReadyForFrontend();
            })
            ->values();

        if ($request->wantsJson()) {
            $itemsView = view('news.partials.grid-items', ['newsItems' => $news])->render();

            return response()->json([
                'html'          => $itemsView,
                'next_page_url' => $news->nextPageUrl(),
                'has_more'      => $news->hasMorePages(),
                'meta'          => [
                    'current_page' => $news->currentPage(),
                    'last_page'    => $news->lastPage(),
                    'per_page'     => $news->perPage(),
                    'total'        => $news->total(),
                ],
            ]);
        }

        return view('news.index', [
            'news'             => $news,
            'categories'       => $categories,
            'tags'             => $tags,
            'featuredNews'     => $featuredNews,
            'searchTerm'       => $searchTerm,
            'selectedCategory' => $categoryId,
            'selectedTag'      => $tagId,
            'featuredOnly'     => $featuredOnly,
        ]);
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(string $slug): View
    {
        $news = News::published()->whereHas('translations', function ($query) use ($slug): void {
            $query->where('slug', $slug)->where('locale', app()->getLocale());
        })->with(['categories', 'tags', 'images', 'comments' => function ($query): void {
            $query->approved()->visible()->topLevel()->with('replies');
        }])->firstOrFail();
        // Increment view count
        $news->incrementViewCount();
        // Get related news
        $relatedNews = News::published()
            ->where('id', '!=', $news->id)
            ->whereHas('categories', function ($query) use ($news): void {
                $query->whereIn('news_category_id', $news->categories->pluck('id'));
            })
            ->with(['categories', 'tags', 'images'])
            ->limit(4)
            ->get()
            ->filter(function (News $related): bool {
                // Avoid surfacing drafts or entries missing the assets required for the recommendation rail.
                return $related->isReadyForFrontend();
            })
            ->values();

                return $candidate->title !== ''
                    && $candidate->slug !== ''
                    && $candidate->is_published
                    && $hasFeaturedImage;
            })
            ->values(); // Preserve contiguous indexes for predictable carousel rendering.

        return view('news.show', ['news' => $news, 'relatedNews' => $relatedNews]);
    }

    /**
     * Handle category functionality with proper error handling.
     */
    public function category(string $slug): View
    {
        $category = NewsCategory::visible()->whereHas('translations', function ($query) use ($slug): void {
            $query->where('slug', $slug)->where('locale', app()->getLocale());
        })->firstOrFail();
        $news = News::published()->byCategory($category->id)->with(['categories', 'tags', 'images'])->orderBy('published_at', 'desc')->paginate(12);
        $categories = NewsCategory::visible()->with('translations')->get();
        $tags = NewsTag::visible()->with('translations')->get();

        return view('news.category', ['news' => $news, 'category' => $category, 'categories' => $categories, 'tags' => $tags]);
    }

    /**
     * Handle tag functionality with proper error handling.
     */
    public function tag(string $slug): View
    {
        $tag = NewsTag::visible()->whereHas('translations', function ($query) use ($slug): void {
            $query->where('slug', $slug)->where('locale', app()->getLocale());
        })->firstOrFail();
        $news = News::published()->byTag($tag->id)->with(['categories', 'tags', 'images'])->orderBy('published_at', 'desc')->paginate(12);
        $categories = NewsCategory::visible()->with('translations')->get();
        $tags = NewsTag::visible()->with('translations')->get();

        return view('news.tag', ['news' => $news, 'tag' => $tag, 'categories' => $categories, 'tags' => $tags]);
    }
}
