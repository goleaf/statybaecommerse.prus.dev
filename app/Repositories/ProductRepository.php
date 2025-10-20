<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Product;

final class ProductRepository
{
    public function count(?string $connection = null): int
    {
        if ($connection !== null) {
            return Product::on($connection)->newQuery()->count();
        }

        return Product::query()->count();
    }
}
