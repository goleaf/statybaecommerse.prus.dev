<?php

declare(strict_types=1);

namespace App\Support\Repositories;

use Illuminate\Support\Facades\DB;

final class UserRepository
{
    public function count(?string $connection = null): int
    {
        $configuredDefault = config('database.default', 'sqlite');
        $defaultConnection = is_string($configuredDefault) && $configuredDefault !== ''
            ? $configuredDefault
            : 'sqlite';

        $connectionName = $connection !== null && $connection !== ''
            ? $connection
            : $defaultConnection;

        return (int) DB::connection($connectionName)->table('users')->count();
    }
}
