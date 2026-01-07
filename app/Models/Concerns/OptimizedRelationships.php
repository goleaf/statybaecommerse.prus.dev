<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
            // Load only recent tasks
            'tasks' => function (HasMany $taskQuery) {
                $taskQuery->where('created_at', '>=', now()->subDays(30))
                    ->select(['id', 'title', 'status', 'project_id', 'created_at'])
                    ->orderBy('created_at', 'desc')
                    ->limit(10);
            },
            // Load task counts instead of full tasks
            'tasks:count',
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
            'tasks',
            'tasks as active_tasks_count' => function (Builder $taskQuery) {
                $taskQuery->whereIn('status', ['pending', 'in_progress']);
            },
            'tasks as completed_tasks_count' => function (Builder $taskQuery) {
                $taskQuery->where('status', 'completed');
            },
            'tasks as overdue_tasks_count' => function (Builder $taskQuery) {
                $taskQuery->where('due_date', '<', now())
                    ->whereNotIn('status', ['completed', 'cancelled']);
            },
            'members',
            'members as active_members_count' => function (Builder $memberQuery) {
                $memberQuery->wherePivot('is_active', true);
            },
            'comments',
            'files',
            'tags',
        ]);
    }

    /**
     * Subquery for latest task without loading all tasks.
     */
    public function scopeWithLatestTask(Builder $query): Builder
    {
        return $query->addSelect([
            'latest_task_id' => \App\Models\Task::select('id')
                ->whereColumn('project_id', $this->getTable() . '.id')
                ->orderBy('created_at', 'desc')
                ->limit(1),
            'latest_task_title' => \App\Models\Task::select('title')
                ->whereColumn('project_id', $this->getTable() . '.id')
                ->orderBy('created_at', 'desc')
                ->limit(1),
        ]);
    }

    /**
     * Aggregate subqueries for performance.
     */
    public function scopeWithAggregates(Builder $query): Builder
    {
        return $query->addSelect([
            'total_tasks' => \App\Models\Task::selectRaw('count(*)')
                ->whereColumn('project_id', $this->getTable() . '.id'),
            'completed_tasks' => \App\Models\Task::selectRaw('count(*)')
                ->whereColumn('project_id', $this->getTable() . '.id')
                ->where('status', 'completed'),
            'average_task_completion_days' => \App\Models\Task::selectRaw('avg(datediff(completed_at, created_at))')
                ->whereColumn('project_id', $this->getTable() . '.id')
                ->whereNotNull('completed_at'),
            'total_comments' => \App\Models\Comment::selectRaw('count(*)')
                ->where('commentable_type', static::class)
                ->whereColumn('commentable_id', $this->getTable() . '.id'),
        ]);
    }

    /**
     * Chunked relationship loading for large datasets.
     */
    public function loadRelationshipsInChunks(string $relation, int $chunkSize = 100): void
    {
        $this->{$relation}()->chunk($chunkSize, function (Collection $items) {
            // Process each chunk
            $items->each(function ($item) {
                // Perform operations on each item
                $item->load(['user', 'tags']);
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

    /**
     * Optimized exists queries.
     */
    public function scopeWithActiveTasksExists(Builder $query): Builder
    {
        return $query->addSelect([
            'has_active_tasks' => \App\Models\Task::selectRaw('count(*) > 0')
                ->whereColumn('project_id', $this->getTable() . '.id')
                ->whereIn('status', ['pending', 'in_progress'])
                ->limit(1),
        ]);
    }

    /**
     * Memory-efficient relationship iteration.
     */
    public function processTasksEfficiently(callable $callback): void
    {
        $this->tasks()
            ->select(['id', 'title', 'status', 'project_id']) // Only needed columns
            ->chunk(50, function (Collection $tasks) use ($callback) {
                $tasks->each($callback);
            });
    }

    /**
     * Cached relationship results.
     */
    public function getCachedTaskCount(): int
    {
        return cache()->remember(
            "project_{$this->id}_task_count",
            now()->addMinutes(15),
            fn () => $this->tasks()->count()
        );
    }

    /**
     * Optimized search across relationships.
     */
    public function scopeSearchOptimized(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $searchQuery) use ($term) {
            $searchQuery->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhereHas('tasks', function (Builder $taskQuery) use ($term) {
                    $taskQuery->where('title', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                })
                ->orWhereHas('members', function (Builder $memberQuery) use ($term) {
                    $memberQuery->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
        });
    }

    /**
     * Prevent N+1 queries in loops.
     */
    public static function withPreloadedRelations(array $ids): Collection
    {
        return static::whereIn('id', $ids)
            ->with([
                'members:id,name,email',
                'tasks:id,title,status,project_id',
                'organization:id,name',
                'tags:id,name,type',
            ])
            ->get();
    }

    /**
     * Optimized relationship existence check.
     */
    public function hasActiveTasks(): bool
    {
        return $this->tasks()
            ->whereIn('status', ['pending', 'in_progress'])
            ->exists();
    }

    /**
     * Bulk relationship operations.
     */
    public function bulkAssignTasks(array $taskIds, \App\Models\User $user): void
    {
        $assignments = collect($taskIds)->map(function ($taskId) use ($user) {
            return [
                'task_id'        => $taskId,
                'user_id'        => $user->id,
                'responsibility' => 'assignee',
                'assigned_at'    => now(),
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        });

        DB::table('task_user')->insert($assignments->toArray());
    }
}
