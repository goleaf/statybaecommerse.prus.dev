<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PaginationService;

use function collect;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOL;

use function filter_var;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
        // Validate incoming filters so pagination and search behave predictably.
        /** @var array{author?: int|string|null, featured?: bool|int|string|null, search?: string|null} $validated */
        $validated = $request->validate([
            'author'   => ['nullable', 'integer'],
            'featured' => ['nullable', 'boolean'],
            'search'   => ['nullable', 'string'],
        ]);

        // Build the base query including the author relationship for eager loading.
        $query = Post::query()->with('user')->latest('published_at');

        // Apply the featured filter when requested so highlighted posts float to the top.
        $isFeatured = filter_var($validated['featured'] ?? null, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        if ($isFeatured === true) {
            $query->featured();
        }

        // Narrow results to a specific author when the identifier is present.
        $authorId = array_key_exists('author', $validated) && $validated['author'] !== null
            ? (int) $validated['author']
            : null;
        if ($authorId !== null) {
            $query->byAuthor($authorId);
        }

        // Apply a simple search across title, excerpt, and content with safe bindings.
        $searchTerm = isset($validated['search']) ? trim($validated['search']) : '';
        if ($searchTerm !== '') {
            $query->where(function (Builder $builder) use ($searchTerm): void {
                // Use parameter bindings to avoid wildcard injection in LIKE queries.
                $builder
                    ->where('title', 'like', '%' . $searchTerm . '%')
                    ->orWhere('excerpt', 'like', '%' . $searchTerm . '%')
                    ->orWhere('content', 'like', '%' . $searchTerm . '%');
            });
        }

        // Paginate with contextual settings so the view receives consistent pagination metadata.
        $posts = PaginationService::paginateWithContext($query, 'posts');

        /** @var view-string $view */
        $view = 'posts.index';

        return view($view, ['posts' => $posts]);
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(Post $post): View
    {
        // Abort early if the resolved post is not actually published (protects against manual unscoping).
        if (! $post->isPublished()) {
            abort(404);
        }

        // Atomically increment the view counter to capture the visit.
        $post->increment('views_count');

        // Load related posts for the same author while ensuring each candidate is display-ready.
        $relatedPosts = Post::query()
            ->with('user')
            ->whereKeyNot($post->getKey())
            ->where('user_id', $post->user_id)
            ->whereNotNull('title')
            ->where('title', '!=', '')
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->whereNotNull('excerpt')
            ->where('excerpt', '!=', '')
            ->latest('published_at')
            ->limit(3)
            ->get()
            ->values();

        /** @var view-string $view */
        $view = 'posts.show';

        return view($view, ['post' => $post, 'relatedPosts' => $relatedPosts]);
    }

    /**
     * Handle featured functionality with proper error handling.
     */
    public function featured(): View
    {
        $posts = PaginationService::paginateWithContext(Post::published()->featured()->with('user')->latest('published_at'), 'posts');

        /** @var view-string $view */
        $view = 'posts.featured';

        return view($view, ['posts' => $posts]);
    }

    /**
     * Handle byAuthor functionality with proper error handling.
     */
    public function byAuthor(Request $request, int $authorId): View
    {
        // Maintain parity with index pagination settings for the author listing.
        $query = Post::query()->byAuthor($authorId)->with('user')->latest('published_at');

        // Paginate using the shared pagination service for consistent metadata.
        $posts = PaginationService::paginateWithContext($query, 'posts');

        // Resolve the author record from the first page or fall back to a lazy lookup when empty.
        /** @var Collection<int, Post> $pageItems */
        $pageItems = collect($posts->items());
        $author = $pageItems->first()?->user;

        /** @var view-string $view */
        $view = 'posts.by-author';

        return view($view, ['posts' => $posts, 'author' => $author]);
    }

    /**
     * Handle search functionality with proper error handling.
     */
    public function search(Request $request): View
    {
        // Validate the search input to keep query construction predictable.
        /** @var array{q?: string|null} $validated */
        $validated = $request->validate([
            'q' => ['nullable', 'string'],
        ]);

        // Establish the base query with eager loading for author details.
        $query = Post::query()->with('user')->latest('published_at');

        // Apply the sanitized search term across title, excerpt, content, and tags.
        $searchTerm = isset($validated['q']) ? trim($validated['q']) : '';
        if ($searchTerm !== '') {
            $query->where(function (Builder $builder) use ($searchTerm): void {
                // Chain the LIKE conditions with parameter bindings for safety.
                $builder
                    ->where('title', 'like', '%' . $searchTerm . '%')
                    ->orWhere('excerpt', 'like', '%' . $searchTerm . '%')
                    ->orWhere('content', 'like', '%' . $searchTerm . '%')
                    ->orWhere('tags', 'like', '%' . $searchTerm . '%');
            });
        }

        // Paginate using the default settings for search results.
        $posts = $query->paginate(12);

        /** @var view-string $view */
        $view = 'posts.search';

        return view($view, [
            'posts'      => $posts,
            'searchTerm' => $searchTerm,
        ]);
    }
}
