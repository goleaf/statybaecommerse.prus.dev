<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;

final class UserRepository
{
    public function count(?string $connection = null): int
    {
        if ($connection !== null) {
            return User::on($connection)->newQuery()->count();
        }

        return User::query()->count();
    }
}
