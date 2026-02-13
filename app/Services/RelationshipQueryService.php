<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Comment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service for complex relationship queries and examples.
 */
final class RelationshipQueryService
{
    /**
     * Get all projects for a user across ownership and membership.
     */
    public function getUserProjects(User $user): Collection
    {
        return Project::query()
            ->where(function (Builder $query) use ($user) {
                $query->where('user_id', $user->id) // Personal projects
                    ->orWhereHas('members', function (Builder $memberQuery) use ($user) {
                        $memberQuery->where('user_id', $user->id);
                    });
            })
            ->with(['owner', 'members'])
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
     * Users who lead active projects.
     */
    public function getActiveProjectLeads(): Collection
    {
        return User::query()
            ->whereExists(function (Builder $query) {
                $query->selectRaw('1')
                    ->from('project_user')
                    ->join('projects', 'projects.id', '=', 'project_user.project_id')
                    ->whereColumn('project_user.user_id', 'users.id')
                    ->where('project_user.role', 'lead')
                    ->where('projects.status', 'active');
            })
            ->get();
    }
}
