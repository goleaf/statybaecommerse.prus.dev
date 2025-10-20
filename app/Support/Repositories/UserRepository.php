<?php

declare(strict_types=1);

namespace App\Support\Repositories;

use App\Models\User;
use Illuminate\Database\ConnectionInterface;

final class UserRepository
{
    public function __construct(private readonly ConnectionInterface $connection) {}

    public function count(): int
    {
        $table = (new User)->getTable();

        return (int) $this->connection->table($table)->count();
    }
}
