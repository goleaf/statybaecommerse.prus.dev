<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * VisibleScope
 *
 * Eloquent model representing the VisibleScope entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|VisibleScope newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VisibleScope newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VisibleScope query()
 *
 * @mixin \Eloquent
 */
final class VisibleScope implements Scope
{
    /**
     * Cache column existence lookups to avoid repeatedly querying information_schema.
     *
     * @var array<string, bool>
     */
    private static array $columnPresence = [];

    public function apply(Builder $builder, Model $model): void
    {
        if (defined($model::class.'::SCOPE_COLUMN_HINTS')) {
            $hints = $model::SCOPE_COLUMN_HINTS;

            if (! ($hints['is_visible'] ?? false)) {
                return;
            }

            $builder->where('is_visible', true);

            return;
        }

        $connection = $model->getConnection();
        $table = $model->getTable();
        $cacheKey = sprintf('%s::%s', $connection->getName() ?: 'default', $table);

        if (! array_key_exists($cacheKey, self::$columnPresence)) {
            try {
                self::$columnPresence[$cacheKey] = $connection->getSchemaBuilder()->hasColumn($table, 'is_visible');
            } catch (\Throwable) {
                self::$columnPresence[$cacheKey] = false;
            }
        }

        if (self::$columnPresence[$cacheKey]) {
            $builder->where('is_visible', true);
        }
    }
}
