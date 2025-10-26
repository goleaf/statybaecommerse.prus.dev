<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Models\Order;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Throwable;

/**
 * ActiveScope
 *
 * Eloquent model representing the ActiveScope entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @mixin \Eloquent
 */
final class ActiveScope implements Scope
{
    /**
     * Cache of table metadata to avoid repeated schema introspection.
     *
     * @var array<string, array{exists: bool, columns: array<string, bool>}>
     */
    private static array $tableMetadataCache = [];

    /**
     * Handle apply functionality with proper error handling.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if ($model instanceof Order) {
            // Orders use dedicated status scopes; avoid duplicating constraints that hide seeded records.
            return;
        }

        $metadata = $this->resolveTableMetadata($model);

        if (! $metadata['exists']) {
            return;
        }

        // Prefer stricter check when both flags exist to keep behaviour predictable.
        $hasIsActive = $metadata['columns']['is_active'] ?? false;
        $hasIsVisible = $metadata['columns']['is_visible'] ?? false;
        $hasIsEnabled = $metadata['columns']['is_enabled'] ?? false;
        $hasStatus = $metadata['columns']['status'] ?? false;

        if ($hasIsActive && $hasIsVisible) {
            $builder->where('is_active', true)->where('is_visible', true);

            return;
        }

        if ($hasIsActive) {
            $builder->where('is_active', true);

            return;
        }

        if ($hasIsEnabled) {
            $builder->where('is_enabled', true);

            return;
        }

        if ($hasIsVisible) {
            $builder->where('is_visible', true);
        } elseif ($hasStatus) {
            // Defer to model-specific allowlists instead of defaulting to a hard-coded
            // "active" value so enums such as orders remain queryable in diagnostics.
            $defaultStatuses = $this->getDefaultStatuses($model);

            if ($defaultStatuses !== []) {
                $builder->whereIn('status', $defaultStatuses);
            }
        }
    }

    /**
     * Surface default status filters on a per-model basis to avoid applying
     * incompatible hard-coded values (e.g. the orders enum has no "active").
     *
     * @return array<int, string>
     */
    private function getDefaultStatuses(Model $model): array
    {
        return match ($model::class) {
            \App\Models\Order::class              => [],
            \App\Models\Referral::class           => ['pending', 'active', 'completed', 'expired', 'cancelled'],
            \App\Models\DiscountRedemption::class => ['pending', 'redeemed', 'expired', 'cancelled'],
            default                               => ['active'],
        };
    }

    /**
     * Resolve cached metadata for the given model table.
     *
     * @return array{exists: bool, columns: array<string, bool>}
     */
    private function resolveTableMetadata(Model $model): array
    {
        $connection = $model->getConnection();
        $cacheKey = $this->buildMetadataCacheKey($connection, $model->getTable());

        if (defined($model::class . '::SCOPE_COLUMN_HINTS')) {
            $hints = $model::SCOPE_COLUMN_HINTS;

            return self::$tableMetadataCache[$cacheKey] = [
                'exists'  => true,
                'columns' => [
                    'is_active'  => (bool) ($hints['is_active'] ?? false),
                    'is_visible' => (bool) ($hints['is_visible'] ?? false),
                    'is_enabled' => (bool) ($hints['is_enabled'] ?? false),
                    'status'     => (bool) ($hints['status'] ?? false),
                ],
            ];
        }

        if (! array_key_exists($cacheKey, self::$tableMetadataCache)) {
            self::$tableMetadataCache[$cacheKey] = $this->introspectTable($connection, $model->getTable());
        }

        return self::$tableMetadataCache[$cacheKey];
    }

    /**
     * Generate a cache key that scopes metadata per-connection and table.
     */
    private function buildMetadataCacheKey(Connection $connection, string $table): string
    {
        return sprintf('%s::%s', $connection->getName() ?: 'default', $table);
    }

    /**
     * Attempt to introspect schema information for the given table.
     *
     * @return array{exists: bool, columns: array<string, bool>}
     */
    private function introspectTable(Connection $connection, string $table): array
    {
        $metadata = ['exists' => false, 'columns' => []];

        try {
            $schema = $connection->getSchemaBuilder();

            if (! $schema->hasTable($table)) {
                return $metadata;
            }

            $metadata['exists'] = true;

            foreach (['is_active', 'is_visible', 'is_enabled', 'status'] as $column) {
                $metadata['columns'][$column] = $schema->hasColumn($table, $column);
            }
        } catch (Throwable) {
            // In-memory sqlite connections can intermittently throw when introspecting
            // schema during rapid migrations. Fail gracefully by exposing an empty table.
            return $metadata;
        }

        return $metadata;
    }
}
