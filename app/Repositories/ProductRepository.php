<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Product;

final class ProductRepository
{
    public function count(?string $connection = null): int
    {
        $builder = $connection ? Product::on($connection) : Product::query();

        return $builder->count();
    }
}
