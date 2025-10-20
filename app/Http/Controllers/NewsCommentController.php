<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\NewsCommentData;
use App\Models\News;
use App\Models\NewsComment;
use Illuminate\Http\RedirectResponse;

/**
 * NewsCommentController
 *
 * HTTP controller handling NewsCommentController related web requests, responses, and business logic with proper validation and error handling.
 */
final class NewsCommentController extends Controller
{
    /**
     * Store a newly created resource in storage with validation.
     */
    public function store(NewsCommentData $data, string $slug): RedirectResponse
    {
        $news = News::published()->whereHas('translations', function ($query) use ($slug): void {
            $query->where('slug', $slug)->where('locale', app()->getLocale());
        })->firstOrFail();
        NewsComment::create([
            'news_id'      => $news->id,
            'parent_id'    => $data->parent_id,
            'author_name'  => $data->author_name,
            'author_email' => $data->author_email,
            'content'      => $data->content,
            'is_approved'  => false,
            // Comments need approval
            'is_visible' => true,
        ]);

        $route = request()->route();
        $routeName = $route?->getName();
        $isLocalized = is_string($routeName) && str_starts_with($routeName, 'localized.news.');
        $locale = $route?->parameter('locale');

        $redirectRoute = $isLocalized ? 'localized.news.show' : 'news.show';
        $redirectParameters = $isLocalized
            ? [
                'locale' => is_string($locale) && $locale !== '' ? $locale : app()->getLocale(),
                'slug'   => $slug,
            ]
            : $slug;

        return redirect()
            ->route($redirectRoute, $redirectParameters)
            ->with('success', __('news.comment_success'));
    }
}
