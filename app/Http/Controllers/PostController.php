<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PostController extends Controller
{
    public function index(Request $request): View
    {
        $query = Post::published()
            ->with('user')
            ->latest('published_at');

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
                $q
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate(12);

        return view('posts.index', compact('posts'));
    }

    public function show(Post $post): View
    {
        // Only show published posts
        if ($post->status !== 'published') {
            abort(404);
        }

        // Increment views count
        $post->increment('views_count');

        // Get related posts
        $relatedPosts = Post::published()
            ->where('id', '!=', $post->id)
            ->where('user_id', $post->user_id)
            ->limit(3)
            ->get();

        return view('posts.show', compact('post', 'relatedPosts'));
    }

    public function featured(): View
    {
        $posts = Post::published()
            ->featured()
            ->with('user')
            ->latest('published_at')
            ->paginate(12);

        return view('posts.featured', compact('posts'));
    }

    public function byAuthor(Request $request, int $authorId): View
    {
        $posts = Post::published()
            ->byAuthor($authorId)
            ->with('user')
            ->latest('published_at')
            ->paginate(12);

        $author = $posts->first()?->user;

        return view('posts.by-author', compact('posts', 'author'));
    }

    public function search(Request $request): View
    {
        $query = Post::published()
            ->with('user')
            ->latest('published_at');

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhere('tags', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate(12);
        $searchTerm = $request->q;

        return view('posts.search', compact('posts', 'searchTerm'));
    }
}

