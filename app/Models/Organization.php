<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Organization
 *
 * @property int                             $id
 * @property string                          $name
 * @property string                          $slug
 * @property string|null                     $description
 * @property string                          $type
 * @property bool                            $is_active
 * @property array|null                      $settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static Builder|Organization active()
 * @method static Builder|Organization byType(string $type)
 */
#[ScopedBy([ActiveScope::class])]
final class Organization extends Model
{
    use HasFactory, OrdersByName;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings'  => 'array',
    ];

    /**
     * Bootstrap the model and ensure the slug column is automatically maintained.
     */
    protected static function booted(): void
    {
        // Generate a slug automatically when creating a record
        self::creating(static function (Organization $organization): void {
            if (! $organization->slug) {
                $organization->slug = \Illuminate\Support\Str::slug($organization->name);
            }
        });

        // Update slug when name changes
        self::updating(static function (Organization $organization): void {
            if ($organization->isDirty('name')) {
                $organization->slug = \Illuminate\Support\Str::slug($organization->name);
            }
        });
    }

    // Relationships

    /**
     * Users belonging to this organization with roles and permissions.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_user')
            ->using(\App\Models\Pivots\OrganizationUser::class)
            ->withPivot(['role', 'permissions', 'is_active', 'joined_at', 'left_at'])
            ->withTimestamps()
            ->wherePivot('is_active', true);
    }

    /**
     * All users (including inactive memberships).
     */
    public function allUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_user')
            ->using(\App\Models\Pivots\OrganizationUser::class)
            ->withPivot(['role', 'permissions', 'is_active', 'joined_at', 'left_at'])
            ->withTimestamps();
    }

    /**
     * Organization owners.
     */
    public function owners(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'owner');
    }

    /**
     * Organization admins.
     */
    public function admins(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'admin');
    }

    /**
     * Organization members.
     */
    public function members(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'member');
    }

    /**
     * Projects belonging to this organization.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Active projects only.
     */
    public function activeProjects(): HasMany
    {
        return $this->projects()->where('status', 'active');
    }

    /**
     * Comments on organization (polymorphic).
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Files attached to organization (polymorphic).
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    // Scopes

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    // Helper methods

    /**
     * Check if user is member of organization.
     */
    public function hasMember(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if user has specific role in organization.
     */
    public function userHasRole(User $user, string $role): bool
    {
        return $this->users()
            ->where('user_id', $user->id)
            ->wherePivot('role', $role)
            ->exists();
    }

    /**
     * Add user to organization with role.
     */
    public function addUser(User $user, string $role = 'member', array $permissions = []): void
    {
        $this->users()->attach($user->id, [
            'role'        => $role,
            'permissions' => $permissions,
            'is_active'   => true,
            'joined_at'   => now(),
        ]);
    }

    /**
     * Remove user from organization.
     */
    public function removeUser(User $user): void
    {
        $this->users()->updateExistingPivot($user->id, [
            'is_active' => false,
            'left_at'   => now(),
        ]);
    }
}
