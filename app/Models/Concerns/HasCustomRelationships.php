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
