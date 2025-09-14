<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreNewsCommentRequest;
use App\Models\News;
use App\Models\NewsComment;
use Illuminate\Http\RedirectResponse;

final /**
 * NewsCommentController
 * 
 * HTTP controller handling web requests and responses.
 */
class NewsCommentController extends Controller
{
    public function store(StoreNewsCommentRequest $request, string $slug): RedirectResponse
    {
        $news = News::published()
            ->whereHas('translations', function ($query) use ($slug) {
                $query->where('slug', $slug)
                    ->where('locale', app()->getLocale());
            })
            ->firstOrFail();

        $comment = NewsComment::create([
            'news_id' => $news->id,
            'parent_id' => $request->validated('parent_id'),
            'author_name' => $request->validated('author_name'),
            'author_email' => $request->validated('author_email'),
            'content' => $request->validated('content'),
            'is_approved' => false, // Comments need approval
            'is_visible' => true,
        ]);

        return redirect()
            ->route('news.show', $slug)
            ->with('success', __('news.comment_success'));
    }
}
