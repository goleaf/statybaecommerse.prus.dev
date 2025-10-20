<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;

final class UserRepository
{
    public function count(?string $connection = null): int
    {
        $builder = $connection ? User::on($connection) : User::query();

        return $builder->count();
    }
}
