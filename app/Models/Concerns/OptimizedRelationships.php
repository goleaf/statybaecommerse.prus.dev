<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Trait for optimized relationship loading and querying.
 */
trait OptimizedRelationships
{
    /**
     * Eager load relationships with constraints to prevent N+1 queries.
     */
    public function scopeWithOptimizedRelations(Builder $query): Builder
    {
        return $query->with([
            // Load only active members
            'members' => function (BelongsToMany $memberQuery) {
                $memberQuery->wherePivot('is_active', true)
                    ->select(['users.id', 'users.name', 'users.email'])
                    ->withPivot(['role', 'joined_at']);
            },
            // Load only approved comments
            'comments' => function ($commentQuery) {
                $commentQuery->where('is_approved', true)
                    ->select(['id', 'content', 'user_id', 'commentable_type', 'commentable_id', 'created_at'])
                    ->with('user:id,name')
                    ->limit(5);
            },
        ]);
    }

    /**
     * Load relationships with counts to avoid separate queries.
     */
    public function scopeWithCounts(Builder $query): Builder
    {
        return $query->withCount([
            'members',
            'members as active_members_count' => function (Builder $memberQuery) {
                $memberQuery->wherePivot('is_active', true);
            },
            'comments',
            'files',
        ]);
    }

    /**
     * Chunked relationship loading for large datasets.
     */
    public function loadRelationshipsInChunks(string $relation, int $chunkSize = 100): void
    {
        $this->{$relation}()->chunk($chunkSize, function (Collection $items) {
            $items->each(function ($item) {
                if (method_exists($item, 'user')) {
                    $item->load('user');
                }
            });
        });
    }

    /**
     * Lazy eager loading to prevent circular references.
     */
    public function scopeWithLazyEagerLoading(Builder $query): Builder
    {
        return $query->with([
            'organization' => function ($orgQuery) {
                $orgQuery->select(['id', 'name', 'slug'])
                    ->without(['projects']); // Prevent circular loading
            },
            'members' => function ($memberQuery) {
                $memberQuery->select(['users.id', 'users.name', 'users.email'])
                    ->without(['projects', 'organizations']); // Prevent circular loading
            },
        ]);
    }

    /**
     * Batch load related models efficiently.
     */
    public static function batchLoadRelations(Collection $models, array $relations): void
    {
        foreach ($relations as $relation => $callback) {
            $models->load([$relation => $callback]);
        }
    }
}
