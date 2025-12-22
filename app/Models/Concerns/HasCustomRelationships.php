<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * Trait for custom and advanced relationship types.
 */
trait HasCustomRelationships
{
    /**
     * Get users through organization memberships (has-many-through).
     */
    public function organizationUsers(): HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\User::class,
            \App\Models\Organization::class,
            'id', // organizations.id
            'id', // users.id  
            'organization_id', // projects.organization_id
            'id' // organizations.id (local key on intermediate)
        )->join('organization_user', function ($join) {
            $join->on('users.id', '=', 'organization_user.user_id')
                ->on('organizations.id', '=', 'organization_user.organization_id')
                ->where('organization_user.is_active', true);
        });
    }

    /**
     * Get organization owner through organization (has-one-through).
     */
    public function organizationOwner(): HasOneThrough
    {
        return $this->hasOneThrough(
            \App\Models\User::class,
            \App\Models\Organization::class,
            'id', // organizations.id
            'id', // users.id
            'organization_id', // projects.organization_id
            'id' // organizations.id
        )->join('organization_user', function ($join) {
            $join->on('users.id', '=', 'organization_user.user_id')
                ->on('organizations.id', '=', 'organization_user.organization_id')
                ->where('organization_user.role', 'owner')
                ->where('organization_user.is_active', true);
        });
    }

    /**
     * Custom relationship: Get related tasks through various paths.
     */
    public function relatedTasks(): Builder
    {
        return \App\Models\Task::query()
            ->where(function (Builder $query) {
                // Tasks in same project
                $query->whereIn('project_id', $this->projects()->pluck('id'))
                    // Tasks assigned to same users
                    ->orWhereHas('assignees', function (Builder $assigneeQuery) {
                        $assigneeQuery->whereIn('user_id', $this->members()->pluck('user_id'));
                    })
                    // Tasks with same tags
                    ->orWhereHas('tags', function (Builder $tagQuery) {
                        $tagQuery->whereIn('tag_id', $this->tags()->pluck('tag_id'));
                    });
            })
            ->where('id', '!=', $this->getKey()); // Exclude self if this is a task
    }

    /**
     * Dynamic relationship based on user permissions.
     */
    public function accessibleProjects(\App\Models\User $user): Builder
    {
        return \App\Models\Project::query()
            ->where(function (Builder $query) use ($user) {
                $query->where('user_id', $user->id) // Personal projects
                    ->orWhereHas('members', function (Builder $memberQuery) use ($user) {
                        $memberQuery->where('user_id', $user->id);
                    })
                    ->orWhereHas('organization.users', function (Builder $orgQuery) use ($user) {
                        $orgQuery->where('user_id', $user->id)
                            ->where('is_active', true);
                    });
            });
    }

    /**
     * Subquery relationship for performance.
     */
    public function latestTaskSubquery(): Builder
    {
        return \App\Models\Task::query()
            ->select('*')
            ->whereColumn('project_id', $this->getTable() . '.id')
            ->orderBy('created_at', 'desc')
            ->limit(1);
    }

    /**
     * Count relationship for aggregates.
     */
    public function tasksCountByStatus(): array
    {
        return \App\Models\Task::query()
            ->where('project_id', $this->getKey())
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    /**
     * Conditional relationship based on model state.
     */
    public function conditionalTasks(): Builder
    {
        $query = $this->hasMany(\App\Models\Task::class);

        // Add conditions based on current model state
        if ($this->status === 'active') {
            $query->whereIn('status', ['pending', 'in_progress']);
        } elseif ($this->status === 'completed') {
            $query->where('status', 'completed');
        }

        return $query;
    }

    /**
     * Relationship with custom pivot data calculation.
     */
    public function membersWithStats(): Builder
    {
        return $this->belongsToMany(\App\Models\User::class, 'project_user')
            ->withPivot(['role', 'joined_at'])
            ->withTimestamps()
            ->selectRaw('users.*, 
                (SELECT COUNT(*) FROM tasks 
                 JOIN task_user ON tasks.id = task_user.task_id 
                 WHERE task_user.user_id = users.id 
                 AND tasks.project_id = ?) as tasks_count,
                (SELECT COUNT(*) FROM tasks 
                 JOIN task_user ON tasks.id = task_user.task_id 
                 WHERE task_user.user_id = users.id 
                 AND tasks.project_id = ? 
                 AND task_user.completed_at IS NOT NULL) as completed_tasks_count', 
                [$this->getKey(), $this->getKey()]);
    }

    /**
     * Relationship with complex joins.
     */
    public function taskAssignmentsWithDetails(): Builder
    {
        return \App\Models\Task::query()
            ->join('task_user', 'tasks.id', '=', 'task_user.task_id')
            ->join('users', 'task_user.user_id', '=', 'users.id')
            ->where('tasks.project_id', $this->getKey())
            ->select([
                'tasks.*',
                'users.name as assignee_name',
                'users.email as assignee_email',
                'task_user.responsibility',
                'task_user.assigned_at',
                'task_user.completed_at',
            ]);
    }

    /**
     * Relationship with window functions (for supported databases).
     */
    public function rankedTasks(): Builder
    {
        return \App\Models\Task::query()
            ->selectRaw('*, ROW_NUMBER() OVER (PARTITION BY project_id ORDER BY created_at DESC) as task_rank')
            ->where('project_id', $this->getKey());
    }

    /**
     * Relationship with recursive CTE (for supported databases).
     */
    public function taskHierarchy(): Builder
    {
        return \App\Models\Task::query()
            ->fromRaw('
                WITH RECURSIVE task_hierarchy AS (
                    SELECT *, 0 as level 
                    FROM tasks 
                    WHERE project_id = ? AND parent_task_id IS NULL
                    
                    UNION ALL
                    
                    SELECT t.*, th.level + 1
                    FROM tasks t
                    JOIN task_hierarchy th ON t.parent_task_id = th.id
                )
                SELECT * FROM task_hierarchy
            ', [$this->getKey()]);
    }

    /**
     * Relationship with full-text search (MySQL).
     */
    public function searchableTasks(string $searchTerm): Builder
    {
        return $this->hasMany(\App\Models\Task::class)
            ->whereRaw('MATCH(title, description) AGAINST(? IN BOOLEAN MODE)', [$searchTerm]);
    }

    /**
     * Relationship with geospatial queries (if applicable).
     */
    public function nearbyProjects(float $latitude, float $longitude, int $radiusKm = 50): Builder
    {
        return \App\Models\Project::query()
            ->selectRaw('*, 
                (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + sin(radians(?)) * 
                sin(radians(latitude)))) AS distance', 
                [$latitude, $longitude, $latitude])
            ->having('distance', '<', $radiusKm)
            ->orderBy('distance');
    }
}