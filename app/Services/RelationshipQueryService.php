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
            ->with(['organization', 'members'])
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
}
