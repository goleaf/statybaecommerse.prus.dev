<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use Spatie\Permission\Models\Role as SpatieRole;

final class Role extends SpatieRole
{
    /**
     * Reuse the shared alphabetical ordering scope for role selectors in the admin panel.
     */
    use OrdersByName;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'permissions_matrix' => 'array',
    ];
}
