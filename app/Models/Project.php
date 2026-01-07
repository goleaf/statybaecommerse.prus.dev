<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPolymorphicRelationships;
use App\Models\Concerns\OrdersByName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Project
 *
 * @property int                             $id
 * @property string                          $name
 * @property string                          $slug
 * @property string|null                     $description
 * @property string                          $status
 * @property string                          $type
 * @property int|null                        $user_id
 * @property int|null                        $organization_id
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property array|null                      $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static Builder|Project personal()
 * @method static Builder|Project organizational()
 * @method static Builder|Project active()
 * @method static Builder|Project byStatus(string $status)
 */
final class Project extends Model
{
    use HasFactory, HasPolymorphicRelationships, OrdersByName;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'type',
        'user_id',
        'organization_id',
        'start_date',
        'end_date',
        'metadata',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'metadata'   => 'array',
    ];

    // Relationships

    /**
     * Owner for personal projects.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Organization for organizational projects.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Project members with roles.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->withPivot(['role', 'permissions', 'joined_at', 'left_at'])
            ->withTimestamps();
    }

    /**
     * Project leads.
     */
    public function leads(): BelongsToMany
    {
        return $this->members()->wherePivot('role', 'lead');
    }

    /**
     * Project contributors.
     */
    public function contributors(): BelongsToMany
    {
        return $this->members()->wherePivot('role', 'contributor');
    }

    /**
     * Tasks in this project.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Active tasks only.
     */
    public function activeTasks(): HasMany
    {
        return $this->tasks()->whereIn('status', ['pending', 'in_progress']);
    }

    /**
     * Root tasks (no parent).
     */
    public function rootTasks(): HasMany
    {
        return $this->tasks()->whereNull('parent_task_id');
    }

    /**
     * Comments through tasks (has-many-through).
     */
    public function taskComments(): HasManyThrough
    {
        return $this->hasManyThrough(Comment::class, Task::class, 'project_id', 'commentable_id')
            ->where('commentable_type', Task::class);
    }

    /**
     * Organization owner through organization (has-one-through).
     */
    public function organizationOwner(): HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            Organization::class,
            'id', // organization.id
            'id', // user.id
            'organization_id', // project.organization_id
            'id' // organization.id (local key on intermediate)
        )->join('organization_user', function ($join) {
            $join->on('users.id', '=', 'organization_user.user_id')
                ->on('organizations.id', '=', 'organization_user.organization_id')
                ->where('organization_user.role', 'owner')
                ->where('organization_user.is_active', true);
        });
    }

    /**
     * Comments on project (polymorphic).
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Files attached to project (polymorphic).
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    /**
     * Tags for project (polymorphic many-to-many).
     */
    public function tags(): MorphMany
    {
        return $this->morphMany(Taggable::class, 'taggable');
    }

    // Scopes

    public function scopePersonal(Builder $query): Builder
    {
        return $query->where('type', 'personal');
    }

    public function scopeOrganizational(Builder $query): Builder
    {
        return $query->where('type', 'organizational');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id) // personal projects
                ->orWhereHas('members', function ($memberQuery) use ($user) {
                    $memberQuery->where('user_id', $user->id);
                })
                ->orWhereHas('organization.users', function ($orgQuery) use ($user) {
                    $orgQuery->where('user_id', $user->id);
                });
        });
    }

    public function scopeWithLatestTask(Builder $query): Builder
    {
        return $query->addSelect([
            'latest_task_id' => Task::select('id')
                ->whereColumn('project_id', 'projects.id')
                ->orderBy('created_at', 'desc')
                ->limit(1),
            'latest_task_title' => Task::select('title')
                ->whereColumn('project_id', 'projects.id')
                ->orderBy('created_at', 'desc')
                ->limit(1),
        ]);
    }

    // Helper methods

    /**
     * Check if project is personal.
     */
    public function isPersonal(): bool
    {
        return $this->type === 'personal';
    }

    /**
     * Check if project is organizational.
     */
    public function isOrganizational(): bool
    {
        return $this->type === 'organizational';
    }

    /**
     * Get project manager (owner or lead).
     */
    public function getManager(): ?User
    {
        if ($this->isPersonal()) {
            return $this->owner;
        }

        return $this->leads()->first() ?? $this->organization?->owners()->first();
    }

    /**
     * Add member to project.
     */
    public function addMember(User $user, string $role = 'member', array $permissions = []): void
    {
        $this->members()->attach($user->id, [
            'role'        => $role,
            'permissions' => $permissions,
            'joined_at'   => now(),
        ]);
    }
}
