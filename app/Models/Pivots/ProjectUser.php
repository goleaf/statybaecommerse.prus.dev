<?php

declare(strict_types=1);

namespace App\Models\Pivots;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * ProjectUser Pivot
 *
 * @property int                             $id
 * @property int                             $project_id
 * @property int                             $user_id
 * @property string                          $role
 * @property array|null                      $permissions
 * @property \Illuminate\Support\Carbon      $joined_at
 * @property \Illuminate\Support\Carbon|null $left_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class ProjectUser extends Pivot
{
    protected $table = 'project_user';

    protected $fillable = [
        'project_id',
        'user_id',
        'role',
        'permissions',
        'joined_at',
        'left_at',
    ];

    protected $casts = [
        'permissions' => 'array',
        'joined_at'   => 'datetime',
        'left_at'     => 'datetime',
    ];

    // Relationships

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
