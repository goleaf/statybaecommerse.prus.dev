<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Throwable;

/**
 * PublishedScope
 *
 * Eloquent model representing the PublishedScope entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|PublishedScope newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PublishedScope newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PublishedScope query()
 *
 * @mixin \Eloquent
 */
final class PublishedScope implements Scope
{
    /**
     * Cache schema lookups to avoid repeated information_schema round-trips.
     *
     * @var array<string, array<string, bool>>
     */
    private static array $columnPresence = [];

    public function apply(Builder $builder, Model $model): void
    {
        if (defined($model::class . '::SCOPE_COLUMN_HINTS')) {
            $hints = $model::SCOPE_COLUMN_HINTS;

            if ($hints['published_at'] ?? false) {
                $builder->whereNotNull('published_at')->where('published_at', '<=', now());
            }

            if ($hints['status'] ?? false) {
                $builder->whereIn('status', ['published', 'active']);
            }

            return;
        }

        $connection = $model->getConnection();
        $table = $model->getTable();
        $cacheKey = sprintf('%s::%s', $connection->getName() ?: 'default', $table);

        if (! array_key_exists($cacheKey, self::$columnPresence)) {
            try {
                $schema = $connection->getSchemaBuilder();
                self::$columnPresence[$cacheKey] = [
                    'published_at' => $schema->hasColumn($table, 'published_at'),
                    'status'       => $schema->hasColumn($table, 'status'),
                ];
            } catch (Throwable) {
                self::$columnPresence[$cacheKey] = [
                    'published_at' => false,
                    'status'       => false,
                ];
            }
        }

        $columns = self::$columnPresence[$cacheKey];

        if ($columns['published_at']) {
            $builder->whereNotNull('published_at')->where('published_at', '<=', now());
        }

        if ($columns['status']) {
            $builder->whereIn('status', ['published', 'active']);
        }
    }
}
