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

    protected $fillable = [
        'setting_id',
        'depends_on_setting_id',
        'condition',
        'condition_value',
        'is_active',
    ];

    protected $casts = [
        'condition'       => 'string',
        'condition_value' => 'string',
        'is_active'       => 'boolean',
    ];

    /**
     * Handle setting functionality with proper error handling.
     */
    public function setting(): BelongsTo
    {
        return $this->belongsTo(SystemSetting::class, 'setting_id');
    }

    /**
     * Handle dependsOn functionality with proper error handling.
     */
    public function dependsOn(): BelongsTo
    {
        return $this->belongsTo(SystemSetting::class, 'depends_on_setting_id');
    }

    /**
     * Handle dependsOnSetting functionality with proper error handling.
     */
    public function dependsOnSetting(): BelongsTo
    {
        return $this->belongsTo(SystemSetting::class, 'depends_on_setting_id');
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
    public function scopeDependsOnSetting($query, $settingId)
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
        return $query->where(function ($q) use ($condition) {
            $q->where('condition', 'like', "%{$condition}%")
                ->orWhere('condition_value', 'like', "%{$condition}%");
        });
    }

    public function scopeByCondition(Builder $query, string $operator): Builder
    {
        $normalizedOperator = strtolower($operator);

        $acceptedOperators = array_unique([
            $operator,
            $normalizedOperator,
            strtoupper($normalizedOperator),
        ]);

        return $query->where(function (Builder $builder) use ($acceptedOperators): void {
            $builder
                ->whereIn('condition->operator', $acceptedOperators)
                ->orWhereIn('condition', $acceptedOperators);
        });
    }

    /**
     * Scope the query to dependencies matching the given condition operator.
     *
     * Supports case-insensitive matches on both the JSON `condition->operator`
     * key and the legacy string `condition` column representation for operators
     * such as `equals_to`, `greater_than`, and `contains`.
     */
    public function scopeByCondition(Builder $query, string $operator): Builder
    {
        // Normalize the operator and account for legacy uppercase values.
        $normalizedOperator = strtolower($operator);

        $acceptedOperators = array_unique([
            $operator,
            $normalizedOperator,
            strtoupper($normalizedOperator),
        ]);

        return $query->where(function (Builder $builder) use ($acceptedOperators): void {
            $builder
                ->whereIn('condition->operator', $acceptedOperators)
                ->orWhereIn('condition', $acceptedOperators);
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
                ->orWhereHas('setting', function ($q) use ($search) {
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
        return $query->orderBy('condition', 'asc');
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
        if (! $this->dependsOn) {
            return false;
        }
        $dependencyValue = $this->dependsOn->value;
        $operator = strtolower(trim((string) ($this->condition ?? '')));
        $expected = $this->condition_value;

        if ($operator === '') {
            return false;
        }

        $normalizedValue = is_string($dependencyValue) ? trim($dependencyValue) : $dependencyValue;
        $expectedValue = is_string($expected) ? trim($expected) : $expected;

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

        if (in_array($operator, $operatorsRequiringValue, true) && ($expectedValue === null || (is_string($expectedValue) && $expectedValue === ''))) {
            return false;
        }

        return match ($operator) {
            'equals'            => $this->compareValues($normalizedValue, $expectedValue) === 0,
            'not_equals'        => $this->compareValues($normalizedValue, $expectedValue) !== 0,
            'greater_than'      => $this->compareValues($normalizedValue, $expectedValue) === 1,
            'greater_or_equals' => $this->compareValues($normalizedValue, $expectedValue) >= 0,
            'less_than'         => $this->compareValues($normalizedValue, $expectedValue) === -1,
            'less_or_equals'    => $this->compareValues($normalizedValue, $expectedValue) <= 0,
            'contains'          => is_string($normalizedValue) && is_string($expectedValue) && str_contains($normalizedValue, $expectedValue),
            'not_contains'      => is_string($normalizedValue) && is_string($expectedValue) && ! str_contains($normalizedValue, $expectedValue),
            'starts_with'       => is_string($normalizedValue) && is_string($expectedValue) && str_starts_with($normalizedValue, $expectedValue),
            'ends_with'         => is_string($normalizedValue) && is_string($expectedValue) && str_ends_with($normalizedValue, $expectedValue),
            'in'                => $this->isInList($normalizedValue, $expectedValue),
            'not_in'            => ! $this->isInList($normalizedValue, $expectedValue),
            'is_empty'          => blank($normalizedValue),
            'is_not_empty'      => filled($normalizedValue),
            'is_true'           => $this->toBoolean($normalizedValue) === true,
            'is_false'          => $this->toBoolean($normalizedValue) === false,
            default             => false,
        };
    }

    private function compareValues(mixed $actual, mixed $expected): int
    {
        if (is_null($actual) || is_null($expected)) {
            return $actual === $expected ? 0 : (is_null($actual) ? -1 : 1);
        }

        if (is_numeric($actual) && is_numeric($expected)) {
            return $actual <=> $expected;
        }

        return strcmp((string) $actual, (string) $expected);
    }

    private function isInList(mixed $actual, mixed $expected): bool
    {
        if (is_null($expected)) {
            return false;
        }

        if (is_string($expected)) {
            $decoded = json_decode($expected, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $expected = $decoded;
            } else {
                $expected = array_map('trim', array_filter(explode(',', $expected), fn ($item) => $item !== ''));
            }
        }

        if (! is_array($expected)) {
            return false;
        }

        return in_array($actual, $expected, ! is_string($actual));
    }

    private function toBoolean(mixed $value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === null) {
            return null;
        }

        $filtered = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $filtered;
    }
}
