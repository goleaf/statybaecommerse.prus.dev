<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SystemSettingDependency
 *
 * Eloquent model representing the SystemSettingDependency entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property int                             $id
 * @property int                             $setting_id
 * @property int                             $depends_on_setting_id
 * @property string|null                     $condition
 * @property string|null                     $condition_value
 * @property bool                            $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read SystemSetting $setting
 * @property-read SystemSetting $dependsOnSetting
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static> newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static> newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static> query()
 * @method static \Illuminate\Database\Eloquent\Builder<static> active()
 * @method static \Illuminate\Database\Eloquent\Builder<static> inactive()
 * @method static \Illuminate\Database\Eloquent\Builder<static> forSetting(int|string $settingId)
 * @method static \Illuminate\Database\Eloquent\Builder<static> dependsOnSetting(int|string $settingId)
 *
 * @mixin \Eloquent
 */
final class SystemSettingDependency extends Model
{
    /** @use HasFactory<\Database\Factories\SystemSettingDependencyFactory> */
    use HasFactory;

    use OrdersByName;

    /**
     * Sort dependencies by their condition column so diagnostic tables remain
     * predictable even when filtering by dependent key values.
     */
    protected string $nameColumn = 'condition';

    protected static function booted(): void
    {
        self::resolveRelationUsing(
            'dependsOnSetting',
            static fn (self $model): BelongsTo => $model->dependsOnSettingRelation()
        );
    }

    /**
     * @param mixed $method
     * @param mixed $parameters
     */
    public function __call($method, $parameters): mixed
    {
        if ($method === 'dependsOnSetting' && $parameters !== []) {
            /** @phpstan-ignore-next-line Dynamic scope method call */
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
        'meta',
    ];

    protected $casts = [
        'condition'       => 'string',
        'condition_value' => 'string',
        'is_active'       => 'boolean',
        'meta'            => 'array',
    ];

    /**
     * @return BelongsTo<SystemSetting, $this>
     */
    public function setting(): BelongsTo
    {
        return $this->belongsTo(SystemSetting::class, 'setting_id');
    }

    /**
     * @deprecated Use the dependsOnSetting relation instead.
     *
     * @return BelongsTo<SystemSetting, $this>
     */
    public function dependsOn(): BelongsTo
    {
        return $this->dependsOnSettingRelation();
    }

    /**
     * @return BelongsTo<SystemSetting, $this>
     */
    public function dependsOnSettingRelation(): BelongsTo
    {
        return $this->belongsTo(SystemSetting::class, 'depends_on_setting_id');
    }

    public function getDependsOnSettingAttribute(): ?SystemSetting
    {
        $relation = $this->getRelationValue('dependsOnSettingRelation');

        return $relation instanceof SystemSetting ? $relation : null;
    }

    /**
     * @param  Builder<static> $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<static> $query
     * @return Builder<static>
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    /**
     * @param  Builder<static> $query
     * @return Builder<static>
     */
    public function scopeForSetting(Builder $query, int|string $settingId): Builder
    {
        return $query->where('setting_id', $settingId);
    }

    /**
     * @param  Builder<static> $query
     * @return Builder<static>
     */
    public function scopeDependsOnSetting(Builder $query, int|string $settingId): Builder
    {
        return $query->where('depends_on_setting_id', $settingId);
    }

    /**
     * @param  Builder<static> $query
     * @return Builder<static>
     */
    public function scopeWithCondition(Builder $query, string $condition): Builder
    {
        $like = '%' . $condition . '%';

        return $query->where(function (Builder $builder) use ($like): void {
            $builder
                ->where('condition', 'like', $like)
                ->orWhere('condition_value', 'like', $like);
        });
    }

    /**
     * @param  Builder<static> $query
     * @return Builder<static>
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
     * @param  Builder<static> $query
     * @return Builder<static>
     */
    public function scopeCreatedBetween(Builder $query, mixed $from, mixed $to): Builder
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * @param  Builder<static> $query
     * @return Builder<static>
     */
    public function scopeUpdatedBetween(Builder $query, mixed $from, mixed $to): Builder
    {
        return $query->whereBetween('updated_at', [$from, $to]);
    }

    /**
     * @param  Builder<static> $query
     * @return Builder<static>
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        $search = trim($search);

        if ($search === '') {
            return $query;
        }

        $like = '%' . $search . '%';

        return $query->where(function (Builder $builder) use ($like): void {
            $builder
                ->where('condition', 'like', $like)
                ->orWhere('condition_value', 'like', $like)
                ->orWhereHas('setting', function (Builder $relation) use ($like): void {
                    $relation
                        ->where('key', 'like', $like)
                        ->orWhere('name', 'like', $like);
                })
                /** @phpstan-ignore larastan.relationExistence */
                ->orWhereHas('dependsOnSetting', function (Builder $relation) use ($like): void {
                    $relation
                        ->where('key', 'like', $like)
                        ->orWhere('name', 'like', $like);
                });
        });
    }

    /**
     * @param  Builder<static> $query
     * @return Builder<static>
     */
    public function scopeOrderByCreatedAt(Builder $query, string $direction = 'desc'): Builder
    {
        return $query->orderBy('created_at', $direction);
    }

    /**
     * @param  Builder<static> $query
     * @return Builder<static>
     */
    public function scopeOrderByUpdatedAt(Builder $query, string $direction = 'desc'): Builder
    {
        return $query->orderBy('updated_at', $direction);
    }

    /**
     * @param  Builder<static> $query
     * @return Builder<static>
     */
    public function scopeOrderByCondition(Builder $query, string $direction = 'desc'): Builder
    {
        return $query->orderBy('condition', $direction);
    }

    /**
     * @param  Builder<static> $query
     * @return Builder<static>
     */
    public function scopeOrderByActiveStatus(Builder $query, string $direction = 'desc'): Builder
    {
        return $query->orderBy('is_active', $direction);
    }

    public function isConditionMet(): bool
    {
        $dependsOnSetting = $this->getRelationValue('dependsOnSettingRelation');

        if (! $dependsOnSetting instanceof SystemSetting) {
            $dependsOnSetting = $this->dependsOnSettingRelation()->getResults();

            if ($dependsOnSetting instanceof SystemSetting) {
                $this->setRelation('dependsOnSettingRelation', $dependsOnSetting);
            }
        }

        if (! $dependsOnSetting instanceof SystemSetting) {
            return false;
        }

        /** @phpstan-ignore-next-line SystemSetting model has dynamic value property */
        $dependencyValue = $dependsOnSetting->value;
        $operator = strtolower(trim($this->condition ?? ''));
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

        if (in_array($operator, $operatorsRequiringValue, true) && ($expectedValue === null || trim($expectedValue) === '')) {
            return false;
        }

        $normalizedValue = is_string($dependencyValue) ? trim($dependencyValue) : $dependencyValue;
        $normalizedExpected = is_string($expectedValue) ? trim($expectedValue) : $expectedValue;

        return match ($operator) {
            'equals'            => $this->compareValues($normalizedValue, $normalizedExpected) === 0,
            'not_equals'        => $this->compareValues($normalizedValue, $normalizedExpected) !== 0,
            'greater_than'      => $this->compareValues($normalizedValue, $normalizedExpected) === 1,
            'greater_or_equals' => $this->compareValues($normalizedValue, $normalizedExpected) >= 0,
            'less_than'         => $this->compareValues($normalizedValue, $normalizedExpected) === -1,
            'less_or_equals'    => $this->compareValues($normalizedValue, $normalizedExpected) <= 0,
            'contains'          => is_string($normalizedValue) && is_string($normalizedExpected) && str_contains($normalizedValue, $normalizedExpected),
            'not_contains'      => is_string($normalizedValue) && is_string($normalizedExpected) && ! str_contains($normalizedValue, $normalizedExpected),
            'starts_with'       => is_string($normalizedValue) && is_string($normalizedExpected) && str_starts_with($normalizedValue, $normalizedExpected),
            'ends_with'         => is_string($normalizedValue) && is_string($normalizedExpected) && str_ends_with($normalizedValue, $normalizedExpected),
            'in'                => $this->isInList($normalizedValue, $normalizedExpected),
            'not_in'            => ! $this->isInList($normalizedValue, $normalizedExpected),
            'is_empty'          => blank($normalizedValue),
            'is_not_empty'      => filled($normalizedValue),
            'is_true'           => $this->toBoolean($normalizedValue) === true,
            'is_false'          => $this->toBoolean($normalizedValue) === false,
            default             => false,
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
            return (float) $actual <=> (float) $expected;
        }

        /** @phpstan-ignore-next-line Safe type casting for comparison */
        $comparison = strcmp((string) $actual, (string) $expected);

        return $comparison <=> 0;
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
