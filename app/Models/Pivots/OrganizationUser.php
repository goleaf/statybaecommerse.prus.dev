<?php

declare(strict_types=1);

namespace App\Models\Pivots;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * OrganizationUser Pivot
 *
 * @property int                             $id
 * @property int                             $organization_id
 * @property int                             $user_id
 * @property string                          $role
 * @property array|null                      $permissions
 * @property bool                            $is_active
 * @property \Illuminate\Support\Carbon      $joined_at
 * @property \Illuminate\Support\Carbon|null $left_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class OrganizationUser extends Pivot
{
    protected $table = 'organization_user';

    protected $fillable = [
        'organization_id',
        'user_id',
        'role',
        'permissions',
        'is_active',
        'joined_at',
        'left_at',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active'   => 'boolean',
        'joined_at'   => 'datetime',
        'left_at'     => 'datetime',
    ];

    // Relationships

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods

    /**
     * Check if user has specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];

        return in_array($permission, $permissions);
    }

    /**
     * Add permission to user.
     */
    public function addPermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        if (! in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->update(['permissions' => $permissions]);
        }
    }

    /**
     * Remove permission from user.
     */
    public function removePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        $permissions = array_filter($permissions, fn ($p) => $p !== $permission);
        $this->update(['permissions' => array_values($permissions)]);
    }

    /**
     * Check if membership is active.
     */
    public function isActive(): bool
    {
        return $this->is_active && $this->left_at === null;
    }

    /**
     * Deactivate membership.
     */
    public function deactivate(): void
    {
        $this->update([
            'is_active' => false,
            'left_at'   => now(),
        ]);
    }

    /**
     * Reactivate membership.
     */
    public function reactivate(): void
    {
        $this->update([
            'is_active' => true,
            'left_at'   => null,
        ]);
    }
}
