<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Trait for conditional and scoped relationships.
 */
trait HasConditionalRelationships
{
    /**
     * Get active members only.
     */
    public function activeMembers(): BelongsToMany
    {
        return $this->belongsToMany(related: \App\Models\User::class, table: $this->getMembershipTable())
            ->wherePivot('is_active', true)
            ->wherePivotNull('left_at')
            ->withPivot(['role', 'permissions', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * Get members by role.
     */
    public function membersByRole(string $role): BelongsToMany
    {
        return $this->activeMembers()->wherePivot('role', $role);
    }

    /**
     * Get recent activities (last 30 days).
     */
    public function recentActivities(): HasMany
    {
        return $this->hasMany(\App\Models\AdminActivityLog::class, 'resource_id')
            ->where('resource_type', $this->getMorphClass())
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get membership table name based on model.
     */
    protected function getMembershipTable(): string
    {
        return match (static::class) {
            \App\Models\Organization::class => 'organization_user',
            \App\Models\Project::class      => 'project_user',
            default                         => 'user_memberships',
        };
    }

    /**
     * Scope for existence queries.
     */
    public function scopeWithActiveMembers(Builder $query): Builder
    {
        return $query->whereHas('activeMembers');
    }

    /**
     * Scope for non-existence queries.
     */
    public function scopeWithoutActiveMembers(Builder $query): Builder
    {
        return $query->whereDoesntHave('activeMembers');
    }

    /**
     * Scope for conditional relationships based on user permissions.
     */
    public function scopeAccessibleBy(Builder $query, \App\Models\User $user): Builder
    {
        return $query->where(function (Builder $q) use ($user) {
            $q->whereHas('activeMembers', function (Builder $memberQuery) use ($user) {
                $memberQuery->where('user_id', $user->id);
            })
                ->orWhere('created_by', $user->id)
                ->orWhere('user_id', $user->id);
        });
    }
}
