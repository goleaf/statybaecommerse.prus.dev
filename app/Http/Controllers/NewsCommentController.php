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
        $news = News::published()->whereHas('translations', function ($query) use ($slug) {
            $query->where('slug', $slug)->where('locale', app()->getLocale());
        })->firstOrFail();
        $comment = NewsComment::create([
            'news_id' => $news->id,
            'parent_id' => $data->parent_id,
            'author_name' => $data->author_name,
            'author_email' => $data->author_email,
            'content' => $data->content,
            'is_approved' => false,
            // Comments need approval
            'is_visible' => true,
        ]);

        return redirect()->route('news.show', $slug)->with('success', __('news.comment_success'));
    }
}
