<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use Spatie\Permission\Models\Role as SpatieRole;

final class Role extends SpatieRole
{
    use OrdersByName; // Allow alphabetical ordering for role management screens.

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'permissions_matrix' => 'array',
    ];

    /**
     * Column used by the shared OrdersByName scope.
     */
    protected string $nameColumn = 'name';
}
