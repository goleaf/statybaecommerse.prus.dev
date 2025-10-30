<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;

final class Role extends SpatieRole
{
    use HasFactory;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'permissions_matrix' => 'array',
    ];

    /**
     * Provides a factory so feature tests can build roles with permission matrices.
     */
    protected static function newFactory(): RoleFactory
    {
        return RoleFactory::new();
    }
}
