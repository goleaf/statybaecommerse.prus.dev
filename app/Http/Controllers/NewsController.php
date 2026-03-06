<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\News;
use App\Services\PaginationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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
        $query = News::query()
            ->published()
            ->with(['images', 'translations']);

        $news = PaginationService::paginateWithContext($query->orderBy('published_at', 'desc'), 'news')
            ->appends($request->except('page'));

        $featuredNews = News::published()
            ->featured()
            ->with(['images', 'translations'])
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
            'news'         => $news,
            'featuredNews' => $featuredNews,
        ]);
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(string $slug): View
    {
        $news = News::query()
            ->published()
            ->whereHas('translations', function ($query) use ($slug): void {
                $query->where('slug', $slug)->where('locale', app()->getLocale());
            })
            ->with(['images', 'translations'])
            ->firstOrFail();

        $news->incrementViewCount();

        $relatedNews = News::published()
            ->where('id', '!=', $news->id)
            ->with(['images', 'translations'])
            ->orderBy('published_at', 'desc')
            ->limit(4)
            ->get()
            ->filter(function (News $related): bool {
                return $related->isReadyForFrontend();
            })
            ->values();

        return view('news.show', compact('news', 'relatedNews'));
    }

    /**
     * Handle legacy category URLs by redirecting to the canonical news index.
     */
    public function category(string $slug): RedirectResponse
    {
        return $this->redirectToIndex();
    }

    /**
     * Handle legacy tag URLs by redirecting to the canonical news index.
     */
    public function tag(string $slug): RedirectResponse
    {
        return $this->redirectToIndex();
    }

    private function redirectToIndex(): RedirectResponse
    {
        if (request()->routeIs('localized.news.*')) {
            if (Route::has('localized.news.index.lt')) {
                return redirect()->route('localized.news.index.lt');
            }

            if (Route::has('localized.news.index')) {
                return redirect()->route('localized.news.index');
            }
        }

        return redirect()->route('frontend.news.index');
    }
}
