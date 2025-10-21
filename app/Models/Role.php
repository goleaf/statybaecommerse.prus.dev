<?php

declare(strict_types=1);

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

final class Role extends SpatieRole
{
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'permissions_matrix' => 'array',
    ];
}
