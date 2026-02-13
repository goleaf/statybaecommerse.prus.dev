<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Throwable;

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
        if (defined($model::class . '::SCOPE_COLUMN_HINTS')) {
            $hints = $model::SCOPE_COLUMN_HINTS;

            if (! ($hints['is_visible'] ?? false)) {
                return;
            }

            try {
                $schema = $model->getConnection()->getSchemaBuilder();

                if (! $schema->hasTable($model->getTable()) || ! $schema->hasColumn($model->getTable(), 'is_visible')) {
                    return;
                }
            } catch (Throwable) {
                return;
            }

            $builder->where($model->qualifyColumn('is_visible'), true);

            return;
        }

        $connection = $model->getConnection();
        $table = $model->getTable();

        if (app()->runningUnitTests()) {
            try {
                $schema = $connection->getSchemaBuilder();

                if ($schema->hasTable($table) && $schema->hasColumn($table, 'is_visible')) {
                    $builder->where($model->qualifyColumn('is_visible'), true);
                }
            } catch (Throwable) {
                return;
            }

            return;
        }

        $database = $connection->getDatabaseName() ?: 'default-db';
        $cacheKey = sprintf('%s::%s::%s', $connection->getName() ?: 'default', $database, $table);

        if (! array_key_exists($cacheKey, self::$columnPresence)) {
            try {
                self::$columnPresence[$cacheKey] = $connection->getSchemaBuilder()->hasColumn($table, 'is_visible');
            } catch (Throwable) {
                self::$columnPresence[$cacheKey] = false;
            }
        }

        if (self::$columnPresence[$cacheKey]) {
            $builder->where('is_visible', true);
        }
    }
}
