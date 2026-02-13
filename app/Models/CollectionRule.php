<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\VisibleScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Throwable;

/**
 * CollectionRule
 *
 * Eloquent model representing the CollectionRule entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRule query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class CollectionRule extends Model
{
    use HasFactory;

    /**
     * @var array<string, bool>
     */
    private static array $columnExistenceCache = [];

    protected $table = 'collection_rules';

    protected $fillable = ['collection_id', 'field', 'operator', 'value', 'position', 'is_active'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        // Ensure ordering and activation flags are always typed consistently.
        return ['position' => 'integer', 'is_active' => 'boolean'];
    }

    /**
     * Automatically assign defaults whenever a collection rule is persisted.
     */
    protected static function booted(): void
    {
        self::creating(function (CollectionRule $rule): void {
            // Guarantee that soft-deactivated rules still persist with an explicit boolean flag.
            if ($rule->is_active === null && self::hasTableColumn($rule, 'is_active')) {
                $rule->is_active = true;
            }

            // When no position is provided we append the rule to the end of the collection stack.
            if ($rule->position === null && $rule->collection_id !== null) {
                $nextPosition = (int) static::withoutGlobalScopes()
                    ->where('collection_id', $rule->collection_id)
                    ->max('position');

                $rule->position = $nextPosition + 1;
            }
        });
    }

    private static function hasTableColumn(self $rule, string $column): bool
    {
        $cacheKey = implode('|', [
            $rule->getConnectionName() ?: 'default',
            $rule->getTable(),
            $column,
        ]);

        if (array_key_exists($cacheKey, self::$columnExistenceCache)) {
            return self::$columnExistenceCache[$cacheKey];
        }

        try {
            $exists = $rule->getConnection()
                ->getSchemaBuilder()
                ->hasColumn($rule->getTable(), $column);
        } catch (Throwable) {
            $exists = false;
        }

        self::$columnExistenceCache[$cacheKey] = $exists;

        return $exists;
    }

    public static function flushColumnExistenceCache(): void
    {
        self::$columnExistenceCache = [];
    }

    /**
     * Handle collection functionality with proper error handling.
     */
    public function collection(): BelongsTo
    {
        // Always expose the owning collection even if it is hidden by storefront scopes.
        return $this->belongsTo(Collection::class)->withoutGlobalScopes([
            ActiveScope::class,
            VisibleScope::class,
        ]);
    }

    /**
     * Handle scopeOrdered functionality with proper error handling.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        // Keep ordering deterministic so storefront renders rules predictably.
        return $query->orderBy('position');
    }

    /**
     * Handle scopeActive functionality with proper error handling.
     */
    public function scopeActive(Builder $query): Builder
    {
        // Provide an opt-in scope for diagnostics that disable the global ActiveScope.
        if (! self::hasTableColumn($this, 'is_active')) {
            return $query;
        }

        return $query->where('is_active', true);
    }

    /**
     * Handle scopeForCollection functionality with proper error handling.
     */
    public function scopeForCollection(Builder $query, int $collectionId): Builder
    {
        // Filter rules for a specific collection without leaking cross-collection data.
        return $query->where('collection_id', $collectionId);
    }
}
