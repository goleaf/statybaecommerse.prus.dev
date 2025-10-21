<?php

declare(strict_types=1);

namespace App\Support\Repositories;

use Illuminate\Support\Facades\DB;

final class ProductRepository
{
    public function count(?string $connection = null): int
    {
        return (int) DB::connection($connection)->table('products')->count();
    }
}
