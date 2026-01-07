<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Comment;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service for complex relationship queries and examples.
 */
final class RelationshipQueryService
{
    /**
     * Get all projects for a user across all organizations.
     */
    public function getUserProjects(User $user): Collection
    {
        return Project::query()
            ->where(function (Builder $query) use ($user) {
                $query->where('user_id', $user->id) // Personal projects
                    ->orWhereHas('members', function (Builder $memberQuery) use ($user) {
                        $memberQuery->where('user_id', $user->id);
                    })
                    ->orWhereHas('organization.users', function (Builder $orgQuery) use ($user) {
                        $orgQuery->where('user_id', $user->id)
                            ->where('is_active', true);
                    });
            })
            ->with(['organization', 'members', 'tasks'])
            ->get();
    }

    /**
     * Get tasks with assignees and their roles.
     */
    public function getTasksWithAssignees(Project $project): Collection
    {
        return Task::query()
            ->where('project_id', $project->id)
            ->with([
                'assignees' => function ($query) {
                    $query->withPivot(['responsibility', 'assigned_at', 'completed_at', 'notes']);
                },
                'assignees.roles',
                'creator',
                'parent',
                'children.assignees',
            ])
            ->get();
    }

    /**
     * Fetch all comments in a thread (nested comments).
     */
    public function getCommentThread(Comment $rootComment): Collection
    {
        return Comment::query()
            ->where('id', $rootComment->id)
            ->orWhere('parent_id', $rootComment->id)
            ->orWhereHas('parent', function (Builder $query) use ($rootComment) {
                $query->where('parent_id', $rootComment->id);
            })
            ->with(['user', 'children.user', 'children.children.user'])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Load tags for multiple model types.
     */
    public function getTagsForModels(array $modelTypes): Collection
    {
        return Tag::query()
            ->whereHas('taggables', function (Builder $query) use ($modelTypes) {
                $query->whereIn('taggable_type', $modelTypes);
            })
            ->withCount(['taggables' => function (Builder $query) use ($modelTypes) {
                $query->whereIn('taggable_type', $modelTypes);
            }])
            ->orderBy('taggables_count', 'desc')
            ->get();
    }

    /**
     * Complex whereHas query: Users who are organization owners with active projects.
     */
    public function getActiveOrganizationOwners(): Collection
    {
        return User::query()
            ->whereHas('organizations', function (Builder $query) {
                $query->where('organization_user.role', 'owner')
                    ->where('organization_user.is_active', true)
                    ->whereHas('projects', function (Builder $projectQuery) {
                        $projectQuery->where('status', 'active');
                    });
            })
            ->with(['organizations.projects' => function ($query) {
                $query->where('status', 'active');
            }])
            ->get();
    }

    /**
     * Users who don't have any completed tasks.
     */
    public function getUsersWithoutCompletedTasks(): Collection
    {
        return User::query()
            ->doesntHave('tasks', function (Builder $query) {
                $query->wherePivot('completed_at', '!=', null);
            })
            ->orWhereDoesntHave('tasks')
            ->get();
    }

    /**
     * Get overdue tasks with nested relationships.
     */
    public function getOverdueTasks(): Collection
    {
        return Task::query()
            ->where('due_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with([
                'project.organization',
                'assignees' => function ($query) {
                    $query->wherePivot('responsibility', 'assignee')
                        ->wherePivotNull('completed_at');
                },
                'parent',
                'children' => function ($query) {
                    $query->whereNotIn('status', ['completed', 'cancelled']);
                },
            ])
            ->get();
    }

    /**
     * Get user's task statistics across organizations.
     */
    public function getUserTaskStatistics(User $user): array
    {
        $stats = [
            'total_assigned'  => 0,
            'completed'       => 0,
            'overdue'         => 0,
            'by_organization' => [],
            'by_priority'     => [],
        ];

        // Total assigned tasks
        $stats['total_assigned'] = Task::query()
            ->whereHas('assignees', function (Builder $query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->count();

        // Completed tasks
        $stats['completed'] = Task::query()
            ->whereHas('assignees', function (Builder $query) use ($user) {
                $query->where('user_id', $user->id)
                    ->whereNotNull('task_user.completed_at');
            })
            ->count();

        // Overdue tasks
        $stats['overdue'] = Task::query()
            ->where('due_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereHas('assignees', function (Builder $query) use ($user) {
                $query->where('user_id', $user->id)
                    ->whereNull('task_user.completed_at');
            })
            ->count();

        // By organization
        $orgStats = Task::query()
            ->select('organizations.name', DB::raw('count(*) as task_count'))
            ->join('projects', 'tasks.project_id', '=', 'projects.id')
            ->join('organizations', 'projects.organization_id', '=', 'organizations.id')
            ->whereHas('assignees', function (Builder $query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->groupBy('organizations.id', 'organizations.name')
            ->get();

        $stats['by_organization'] = $orgStats->pluck('task_count', 'name')->toArray();

        // By priority
        $priorityStats = Task::query()
            ->select('priority', DB::raw('count(*) as task_count'))
            ->whereHas('assignees', function (Builder $query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->groupBy('priority')
            ->get();

        $stats['by_priority'] = $priorityStats->pluck('task_count', 'priority')->toArray();

        return $stats;
    }

    /**
     * Get projects with their task completion rates.
     */
    public function getProjectsWithCompletionRates(Organization $organization): Collection
    {
        return Project::query()
            ->where('organization_id', $organization->id)
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => function (Builder $query) {
                    $query->where('status', 'completed');
                },
            ])
            ->get()
            ->map(function (Project $project) {
                $project->completion_rate = $project->tasks_count > 0
                    ? round(($project->completed_tasks_count / $project->tasks_count) * 100, 2)
                    : 0;

                return $project;
            });
    }

    /**
     * Get most active users across organizations.
     */
    public function getMostActiveUsers(int $limit = 10): Collection
    {
        return User::query()
            ->withCount([
                'tasks as assigned_tasks_count',
                'tasks as completed_tasks_count' => function (Builder $query) {
                    $query->wherePivot('completed_at', '!=', null);
                },
                'comments',
                'organizations',
            ])
            ->having('assigned_tasks_count', '>', 0)
            ->orderBy('completed_tasks_count', 'desc')
            ->orderBy('comments_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get hierarchical task structure for a project.
     */
    public function getProjectTaskHierarchy(Project $project): Collection
    {
        return Task::query()
            ->where('project_id', $project->id)
            ->whereNull('parent_task_id') // Root tasks only
            ->with([
                'children' => function ($query) {
                    $query->with('children.children'); // 3 levels deep
                },
                'assignees',
                'comments.user',
            ])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Search across multiple models with tags.
     */
    public function searchWithTags(string $searchTerm, array $tagIds = []): array
    {
        $results = [];

        // Search projects
        $projectQuery = Project::query()
            ->where('name', 'like', "%{$searchTerm}%")
            ->orWhere('description', 'like', "%{$searchTerm}%");

        if (! empty($tagIds)) {
            $projectQuery->whereHas('tags', function (Builder $query) use ($tagIds) {
                $query->whereIn('tag_id', $tagIds);
            });
        }

        $results['projects'] = $projectQuery->with('tags.tag')->get();

        // Search tasks
        $taskQuery = Task::query()
            ->where('title', 'like', "%{$searchTerm}%")
            ->orWhere('description', 'like', "%{$searchTerm}%");

        if (! empty($tagIds)) {
            $taskQuery->whereHas('tags', function (Builder $query) use ($tagIds) {
                $query->whereIn('tag_id', $tagIds);
            });
        }

        $results['tasks'] = $taskQuery->with(['project', 'tags.tag'])->get();

        // Search users
        $userQuery = User::query()
            ->where('name', 'like', "%{$searchTerm}%")
            ->orWhere('email', 'like', "%{$searchTerm}%");

        if (! empty($tagIds)) {
            $userQuery->whereHas('tags', function (Builder $query) use ($tagIds) {
                $query->whereIn('tag_id', $tagIds);
            });
        }

        $results['users'] = $userQuery->with('tags.tag')->get();

        return $results;
    }
}
