<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SystemSettingDependency
 *
 * Eloquent model representing the SystemSettingDependency entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 *
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingDependency newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingDependency newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingDependency query()
 *
 * @mixin \Eloquent
 */
final class SystemSettingDependency extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::resolveRelationUsing('dependsOnSetting', fn (self $model): BelongsTo => $model->dependsOnSettingRelation());
    }

    public function __call($method, $parameters)
    {
        if ($method === 'dependsOnSetting' && $parameters !== []) {
            return $this->newQuery()->$method(...$parameters);
        }

        return parent::__call($method, $parameters);
    }

    protected $fillable = [
        'setting_id',
        'depends_on_setting_id',
        'condition',
        'condition_value',
        'is_active',
    ];

    protected $casts = [
        'condition' => 'string',
        'condition_value' => 'string',
        'is_active' => 'boolean',
    ];

    /**
     * Handle setting functionality with proper error handling.
     */
    public function setting(): BelongsTo
    {
        return $this->belongsTo(SystemSetting::class, 'setting_id');
    }

    /**
     * Relation to the source setting this dependency listens to.
     */
    public function dependsOnSettingRelation(): BelongsTo
    {
        return $this->belongsTo(SystemSetting::class, 'depends_on_setting_id');
    }

    /**
     * Accessor exposing the relation under the expected attribute name.
     */
    public function getDependsOnSettingAttribute(): ?SystemSetting
    {
        return $this->getRelationValue('dependsOnSettingRelation');
    }

    /** @deprecated Use dependsOnSettingRelation() or the dependsOnSetting attribute instead. */
    public function dependsOn(): BelongsTo
    {
        return $this->dependsOnSettingRelation();
    }

    /**
     * Handle scopeActive functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Handle scopeInactive functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Handle scopeForSetting functionality with proper error handling.
     *
     * @param mixed $query
     * @param mixed $settingId
     */
    public function scopeForSetting($query, $settingId)
    {
        return $query->where('setting_id', $settingId);
    }

    /**
     * Handle scopeDependsOnSetting functionality with proper error handling.
     *
     * @param mixed $query
     * @param mixed $settingId
     */
    public function scopeDependsOnSetting(Builder $query, int|string $settingId): Builder
    {
        return $query->where('depends_on_setting_id', $settingId);
    }

    /**
     * Handle scopeWithCondition functionality with proper error handling.
     *
     * @param mixed $query
     * @param mixed $condition
     */
    public function scopeWithCondition($query, $condition)
    {
        return $query->where(function ($q) use ($condition): void {
            $q
                ->where('condition', 'like', "%{$condition}%")
                ->orWhere('condition_value', 'like', "%{$condition}%");
        });
    }

    /**
     * Scope the query to dependencies matching the given condition operator.
     */
    public function scopeByCondition(Builder $query, string $operator): Builder
    {
        $normalizedOperator = strtolower($operator);

        return $query->where(function (Builder $builder) use ($operator, $normalizedOperator): void {
            $builder
                ->where('condition', $operator)
                ->orWhere('condition', $normalizedOperator);
        });
    }

    /**
     * Handle scopeCreatedBetween functionality with proper error handling.
     *
     * @param mixed $query
     * @param mixed $from
     * @param mixed $to
     */
    public function scopeCreatedBetween($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * Handle scopeUpdatedBetween functionality with proper error handling.
     *
     * @param mixed $query
     * @param mixed $from
     * @param mixed $to
     */
    public function scopeUpdatedBetween($query, $from, $to)
    {
        return $query->whereBetween('updated_at', [$from, $to]);
    }

    /**
     * Handle scopeSearch functionality with proper error handling.
     *
     * @param mixed $query
     * @param mixed $search
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search): void {
            $q
                ->where('condition', 'like', "%{$search}%")
                ->orWhere('condition_value', 'like', "%{$search}%")
                ->orWhereHas('setting', function ($q) use ($search): void {
                    $q
                        ->where('key', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                })
                ->orWhereHas('dependsOnSetting', function ($q) use ($search): void {
                    $q
                        ->where('key', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
        });
    }

    /**
     * Handle scopeOrderByCreatedAt functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeOrderByCreatedAt($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Handle scopeOrderByUpdatedAt functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeOrderByUpdatedAt($query)
    {
        return $query->orderBy('updated_at', 'desc');
    }

    /**
     * Handle scopeOrderByCondition functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeOrderByCondition($query)
    {
        return $query->orderBy('condition', 'desc');
    }

    /**
     * Handle scopeOrderByActiveStatus functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeOrderByActiveStatus($query)
    {
        return $query->orderBy('is_active', 'desc');
    }

    /**
     * Handle isConditionMet functionality with proper error handling.
     */
    public function isConditionMet(): bool
    {
        if (! $this->dependsOnSetting) {
            return false;
        }
        $dependencyValue = $this->dependsOnSetting->value;
        $operator = strtolower(trim((string) ($this->condition ?? '')));
        $expectedValue = $this->condition_value;

        if ($operator === '') {
            return false;
        }

        // Operators that require an explicit comparison value.
        $operatorsRequiringValue = [
            'equals',
            'not_equals',
            'greater_than',
            'greater_or_equals',
            'less_than',
            'less_or_equals',
            'contains',
            'not_contains',
            'starts_with',
            'ends_with',
            'in',
            'not_in',
        ];

        if (in_array($operator, $operatorsRequiringValue, true) && ($expectedValue === null || (is_string($expectedValue) && trim($expectedValue) === ''))) {
            return false;
        }

        $normalizedValue = is_string($dependencyValue) ? trim($dependencyValue) : $dependencyValue;
        $normalizedExpected = is_string($expectedValue) ? trim($expectedValue) : $expectedValue;

        return match ($operator) {
            'equals' => $this->compareValues($normalizedValue, $normalizedExpected) === 0,
            'not_equals' => $this->compareValues($normalizedValue, $normalizedExpected) !== 0,
            'greater_than' => $this->compareValues($normalizedValue, $normalizedExpected) === 1,
            'greater_or_equals' => $this->compareValues($normalizedValue, $normalizedExpected) >= 0,
            'less_than' => $this->compareValues($normalizedValue, $normalizedExpected) === -1,
            'less_or_equals' => $this->compareValues($normalizedValue, $normalizedExpected) <= 0,
            'contains' => is_string($normalizedValue) && is_string($normalizedExpected) && str_contains($normalizedValue, $normalizedExpected),
            'not_contains' => is_string($normalizedValue) && is_string($normalizedExpected) && ! str_contains($normalizedValue, $normalizedExpected),
            'starts_with' => is_string($normalizedValue) && is_string($normalizedExpected) && str_starts_with($normalizedValue, $normalizedExpected),
            'ends_with' => is_string($normalizedValue) && is_string($normalizedExpected) && str_ends_with($normalizedValue, $normalizedExpected),
            'in' => $this->isInList($normalizedValue, $normalizedExpected),
            'not_in' => ! $this->isInList($normalizedValue, $normalizedExpected),
            'is_empty' => blank($normalizedValue),
            'is_not_empty' => filled($normalizedValue),
            'is_true' => $this->toBoolean($normalizedValue) === true,
            'is_false' => $this->toBoolean($normalizedValue) === false,
            default => false,
        };
    }

    /**
     * Compare actual and expected values with support for numeric and string data.
     */
    private function compareValues(mixed $actual, mixed $expected): int
    {
        if ($actual === null || $expected === null) {
            return $actual === $expected ? 0 : ($actual === null ? -1 : 1);
        }

        if (is_numeric($actual) && is_numeric($expected)) {
            return $actual <=> $expected;
        }

        return strcmp((string) $actual, (string) $expected);
    }

    /**
     * Determine if a value exists in a comma separated or JSON encoded list.
     */
    private function isInList(mixed $actual, mixed $expected): bool
    {
        if ($expected === null) {
            return false;
        }

        if (is_string($expected)) {
            $decoded = json_decode($expected, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $expected = $decoded;
            } else {
                $expected = array_map('trim', array_filter(explode(',', $expected), static fn ($item): bool => $item !== ''));
            }
        }

        if (! is_array($expected)) {
            return false;
        }

        return in_array($actual, $expected, ! is_string($actual));
    }

    /**
     * Convert supported textual values to booleans for consistent comparisons.
     */
    private function toBoolean(mixed $value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === null) {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}
