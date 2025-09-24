<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PaginationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * PostController
 *
 * HTTP controller handling PostController related web requests, responses, and business logic with proper validation and error handling.
 */
final class PostController extends Controller
{
    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(Request $request): View
    {
        $query = Post::published()->with('user')->latest('published_at');
        // Filter by featured
        if ($request->boolean('featured')) {
            $query->featured();
        }
        // Filter by author
        if ($request->filled('author')) {
            $query->byAuthor((int) $request->author);
        }
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")->orWhere('excerpt', 'like', "%{$search}%")->orWhere('content', 'like', "%{$search}%");
            });
        }
        $posts = PaginationService::paginateWithContext($query, 'posts');

        return view('posts.index', compact('posts'));
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(Post $post): View
    {
        // Only show published posts
        if ($post->status !== 'published') {
            abort(404);
        }
        // Increment views count
        $post->increment('views_count');
        // Get related posts
        $relatedPosts = Post::published()->where('id', '!=', $post->id)->where('user_id', $post->user_id)->limit(3)->get()->skipWhile(function ($relatedPost) {
            // Skip related posts that are not properly configured for display
            return empty($relatedPost->title) || empty($relatedPost->slug) || $relatedPost->status !== 'published' || empty($relatedPost->excerpt);
        });

        return view('posts.show', compact('post', 'relatedPosts'));
    }

    /**
     * Handle featured functionality with proper error handling.
     */
    public function featured(): View
    {
        $posts = PaginationService::paginateWithContext(Post::published()->featured()->with('user')->latest('published_at'), 'posts');

        return view('posts.featured', compact('posts'));
    }

    /**
     * Handle byAuthor functionality with proper error handling.
     */
    public function byAuthor(Request $request, int $authorId): View
    {
        $posts = Post::published()->byAuthor($authorId)->with('user')->latest('published_at')->paginate(12);
        $author = $posts->first()?->user;

        return view('posts.by-author', compact('posts', 'author'));
    }

    /**
     * Handle search functionality with proper error handling.
     */
    public function search(Request $request): View
    {
        $query = Post::published()->with('user')->latest('published_at');
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")->orWhere('excerpt', 'like', "%{$search}%")->orWhere('content', 'like', "%{$search}%")->orWhere('tags', 'like', "%{$search}%");
            });
        }
        $posts = $query->paginate(12);
        $searchTerm = $request->q;

        return view('posts.search', compact('posts', 'searchTerm'));
    }
}
