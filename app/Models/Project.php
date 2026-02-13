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
     * Project members with roles.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->using(\App\Models\Pivots\ProjectUser::class)
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
                });
        });
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

        return $this->leads()->first() ?? $this->owner;
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
