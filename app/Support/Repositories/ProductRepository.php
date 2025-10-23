<?php

declare(strict_types=1);

namespace App\Support\Repositories;

use App\Models\Product;
use Illuminate\Database\ConnectionInterface;

final class ProductRepository
{
    public function __construct(private readonly ConnectionInterface $connection) {}

    public function count(): int
    {
        $table = (new Product)->getTable();

        return (int) $this->connection->table($table)->count();
    }
}
