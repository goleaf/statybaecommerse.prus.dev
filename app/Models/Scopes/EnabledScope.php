<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Throwable;

/**
 * EnabledScope
 *
 * Eloquent model representing the EnabledScope entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|EnabledScope newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EnabledScope newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EnabledScope query()
 *
 * @mixin \Eloquent
 */
final class EnabledScope implements Scope
{
    /**
     * Cache column existence lookups to avoid repeated schema introspection during hot paths.
     *
     * @var array<string, bool>
     */
    private static array $columnPresence = [];

    public function apply(Builder $builder, Model $model): void
    {
        if (defined($model::class . '::SCOPE_COLUMN_HINTS')) {
            $hints = $model::SCOPE_COLUMN_HINTS;

            if (! ($hints['is_enabled'] ?? false)) {
                return;
            }

            $builder->where('is_enabled', true);

            return;
        }

        $connection = $model->getConnection();
        $table = $model->getTable();
        $cacheKey = sprintf('%s::%s', $connection->getName() ?: 'default', $table);

        $shouldRefresh = ! array_key_exists($cacheKey, self::$columnPresence);

        if (! $shouldRefresh && self::$columnPresence[$cacheKey] === false) {
            try {
                $schema = $connection->getSchemaBuilder();

                if ($schema->hasTable($table)) {
                    $shouldRefresh = true;
                }
            } catch (Throwable) {
                // Ignore transient schema errors so caches stay stable until the
                // next request, which mirrors the behaviour in ActiveScope.
            }
        }

        if ($shouldRefresh) {
            try {
                $schema = $connection->getSchemaBuilder();
                $hasColumn = $schema->hasTable($table) && $schema->hasColumn($table, 'is_enabled');

                // Cache the refreshed lookup so subsequent queries avoid redundant
                // schema introspection after migrations bring the table online.
                self::$columnPresence[$cacheKey] = $hasColumn;
            } catch (Throwable) {
                self::$columnPresence[$cacheKey] = false;
            }
        }

        if (self::$columnPresence[$cacheKey] ?? false) {
            $builder->where('is_enabled', true);
        }
    }
}
