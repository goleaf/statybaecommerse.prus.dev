<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Export;
use App\Models\User;

final class ExportPolicy
{
    public function download(User $user, Export $export): bool
    {
        if ($export->requested_by === $user->getKey()) {
            return true;
        }

        if (method_exists($user, 'can')) {
            return $user->can('download exports') || $user->can('manage exports');
        }

        return false;
    }
}
