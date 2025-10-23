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

    protected $fillable = ['setting_id', 'depends_on_setting_id', 'condition', 'condition_value', 'is_active'];

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

        return match ($condition['operator'] ?? 'equals') {
            'equals'       => $dependencyValue == $condition['value'],
            'not_equals'   => $dependencyValue != $condition['value'],
            'greater_than' => $dependencyValue > $condition['value'],
            'less_than'    => $dependencyValue < $condition['value'],
            'contains'     => str_contains($dependencyValue, $condition['value']),
            'not_contains' => ! str_contains($dependencyValue, $condition['value']),
            'in'           => in_array($dependencyValue, $condition['value'] ?? []),
            'not_in'       => ! in_array($dependencyValue, $condition['value'] ?? []),
            default        => false,
        };
    }

    private function compareEquality(mixed $actual, mixed $expected): bool
    {
        if ($expected === null) {
            return $actual === null || $this->isEmptyValue($actual);
        }

        if (is_bool($actual) || is_bool($expected)) {
            $actualBool = $this->toBool($actual);
            $expectedBool = $this->toBool($expected);

            return $actualBool !== null && $expectedBool !== null && $actualBool === $expectedBool;
        }

        return $this->normalizeString($actual) === $this->normalizeString($expected);
    }

    private function compareNumeric(mixed $actual, mixed $expected, callable $comparator): bool
    {
        $actualNumber = $this->toNumber($actual);
        $expectedNumber = $this->toNumber($expected);

        if ($actualNumber === null || $expectedNumber === null) {
            return false;
        }

        return (bool) $comparator($actualNumber, $expectedNumber);
    }

    private function containsValue(mixed $actual, mixed $expected): bool
    {
        if ($expected === null) {
            return false;
        }

        if (is_array($actual)) {
            return in_array($expected, $actual, true);
        }

        $actualString = $this->normalizeString($actual);
        $expectedString = $this->normalizeString($expected);

        return $actualString !== '' && $expectedString !== '' && str_contains($actualString, $expectedString);
    }

    private function inCollection(mixed $actual, mixed $expected): bool
    {
        if ($expected === null) {
            return false;
        }

        $collection = $this->toArray($expected);

        if ($collection === []) {
            return false;
        }

        if (is_array($actual)) {
            return (bool) array_intersect($actual, $collection);
        }

        return in_array($this->normalizeString($actual), array_map([$this, 'normalizeString'], $collection), true);
    }

    private function stringComparison(mixed $actual, mixed $expected, callable $callback): bool
    {
        if ($expected === null) {
            return false;
        }

        $actualString = $this->normalizeString($actual);
        $expectedString = $this->normalizeString($expected);

        return $actualString !== '' && $expectedString !== '' && $callback($actualString, $expectedString);
    }

    private function isEmptyValue(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_array($value)) {
            return count($value) === 0;
        }

        return false;
    }

    private function normalizeString(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_THROW_ON_ERROR);
        }

        return (string) $value;
    }

    private function toNumber(mixed $value): ?float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }

    private function toBool(mixed $value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value !== 0;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            return match ($normalized) {
                '1', 'true', 'yes', 'on' => true,
                '0', 'false', 'no', 'off', '' => false,
                default => null,
            };
        }

        return null;
    }

    private function toArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if ($value === null) {
            return [];
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            return array_filter(array_map('trim', explode(',', $value)), fn ($item) => $item !== '');
        }

        return [$value];
    }
}
