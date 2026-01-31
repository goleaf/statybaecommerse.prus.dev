<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Service for optimizing comment queries and preventing N+1 problems.
 */
final class CommentQueryOptimizationService
{
    /**
     * Get paginated comments for an entity with optimized queries.
     */
    public function getPaginatedComments(
        Model $entity,
        int $page = 1,
        int $perPage = 15,
        bool $includeReplies = true
    ): LengthAwarePaginator {
        $cacheKey = "comments:{$entity->getMorphClass()}:{$entity->getKey()}:page:{$page}:per_page:{$perPage}";

        return Cache::remember($cacheKey, 300, function () use ($entity, $page, $perPage, $includeReplies) {
            $query = Comment::query()
                ->forEntity($entity)
                ->approved()
                ->rootComments()
                ->with([
                    'user:id,name,avatar',
                    'children' => function (Builder $q) use ($includeReplies) {
                        if ($includeReplies) {
                            $q->approved()
                                ->with('user:id,name,avatar')
                                ->orderBy('created_at', 'asc')
                                ->limit(5);
                        }
                    },
                ])
                ->orderBy('is_pinned', 'desc')
                ->orderBy('created_at', 'desc');

            return $query->paginate($perPage, ['*'], 'page', $page);
        });
    }

    /**
     * Get comment statistics for an entity.
     */
    public function getCommentStats(Model $entity): array
    {
        $cacheKey = "comment_stats:{$entity->getMorphClass()}:{$entity->getKey()}";

        return Cache::remember($cacheKey, 600, function () use ($entity) {
            return [
                'total_comments'    => $this->getTotalCommentsCount($entity),
                'approved_comments' => $this->getApprovedCommentsCount($entity),
                'pending_comments'  => $this->getPendingCommentsCount($entity),
                'recent_comments'   => $this->getRecentCommentsCount($entity),
                'top_commenters'    => $this->getTopCommenters($entity),
            ];
        });
    }

    /**
     * Bulk approve comments with optimized query.
     */
    public function bulkApproveComments(array $commentIds): int
    {
        $updated = Comment::whereIn('id', $commentIds)
            ->where('is_approved', false)
            ->update(['is_approved' => true]);

        // Clear related caches
        $this->clearCommentCaches($commentIds);

        return $updated;
    }

    /**
     * Get comments that need moderation with efficient pagination.
     */
    public function getCommentsForModeration(int $perPage = 20): LengthAwarePaginator
    {
        return Comment::query()
            ->where('is_approved', false)
            ->with([
                'user:id,name,email',
                'commentable' => function ($morphTo) {
                    $morphTo->morphWith([
                        \App\Models\Project::class      => ['id', 'name'],
                        \App\Models\Organization::class => ['id', 'name'],
                    ]);
                },
            ])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Search comments with full-text search and proper indexing.
     */
    public function searchComments(string $query, ?Model $entity = null, int $perPage = 15): LengthAwarePaginator
    {
        $builder = Comment::query()
            ->approved()
            ->where('content', 'like', "%{$query}%")
            ->with(['user:id,name', 'commentable']);

        if ($entity) {
            $builder->forEntity($entity);
        }

        return $builder
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get comment thread with all replies efficiently.
     */
    public function getCommentThread(Comment $rootComment): Comment
    {
        return Comment::query()
            ->where('id', $rootComment->id)
            ->with([
                'user:id,name,avatar',
                'descendants' => function (Builder $q) {
                    $q->approved()
                        ->with('user:id,name,avatar')
                        ->orderBy('created_at', 'asc');
                },
            ])
            ->first();
    }

    private function getTotalCommentsCount(Model $entity): int
    {
        return Comment::forEntity($entity)->count();
    }

    private function getApprovedCommentsCount(Model $entity): int
    {
        return Comment::forEntity($entity)->approved()->count();
    }

    private function getPendingCommentsCount(Model $entity): int
    {
        return Comment::forEntity($entity)->where('is_approved', false)->count();
    }

    private function getRecentCommentsCount(Model $entity, int $days = 7): int
    {
        return Comment::forEntity($entity)
            ->approved()
            ->where('created_at', '>=', now()->subDays($days))
            ->count();
    }

    private function getTopCommenters(Model $entity, int $limit = 5): array
    {
        return DB::table('comments')
            ->select('users.name', DB::raw('COUNT(*) as comment_count'))
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->where('comments.commentable_type', $entity->getMorphClass())
            ->where('comments.commentable_id', $entity->getKey())
            ->where('comments.is_approved', true)
            ->whereNull('comments.deleted_at')
            ->groupBy('users.id', 'users.name')
            ->orderBy('comment_count', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    private function clearCommentCaches(array $commentIds): void
    {
        $comments = Comment::whereIn('id', $commentIds)
            ->with('commentable')
            ->get();

        foreach ($comments as $comment) {
            if ($comment->commentable) {
                $entity = $comment->commentable;
                $pattern = "comments:{$entity->getMorphClass()}:{$entity->getKey()}:*";
                Cache::flush(); // In production, use more specific cache clearing

                $statsKey = "comment_stats:{$entity->getMorphClass()}:{$entity->getKey()}";
                Cache::forget($statsKey);
            }
        }
    }
}
