<?php

declare(strict_types=1);

namespace App\Support\Repositories;

use Illuminate\Support\Facades\DB;

final class UserRepository
{
    public function count(?string $connection = null): int
    {
        return (int) DB::connection($connection)->table('users')->count();
    }
}
