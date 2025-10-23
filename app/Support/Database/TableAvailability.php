<?php

declare(strict_types=1);

namespace App\Support\Database;

use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Schema;
use Throwable;

class TableAvailability
{
    /**
     * @var array<string, bool>
     */
    private array $cache = [];

    public function has(string $table, ?string $connection = null): bool
    {
        $cacheKey = $this->cacheKey($table, $connection);

        if (! array_key_exists($cacheKey, $this->cache)) {
            $this->cache[$cacheKey] = $this->detectTable($table, $connection);
        }

        return $this->cache[$cacheKey];
    }

    public function forget(?string $table = null, ?string $connection = null): void
    {
        if ($table === null) {
            $this->cache = [];

            return;
        }

        unset($this->cache[$this->cacheKey($table, $connection)]);
    }

    private function detectTable(string $table, ?string $connection): bool
    {
        try {
            return $this->resolveBuilder($connection)->hasTable($table);
        } catch (Throwable) {
            return false;
        }
    }

    private function resolveBuilder(?string $connection): Builder
    {
        /** @var Builder $builder */
        $builder = Schema::connection($connection);

        return $builder;
    }

    private function cacheKey(string $table, ?string $connection): string
    {
        return $connection === null ? $table : $connection.'::'.$table;
    }
}
