<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

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
     * @param string                  $method     Forwarded dynamic method call name.
     * @param array<array-key, mixed> $parameters Forwarded arguments from the caller.
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
     * @deprecated Use dependsOnSetting relation instead.
     *
     * @return BelongsTo<SystemSetting, $this>
     */
    public function dependsOnSettingRelation(): BelongsTo
    {
        return $this->belongsTo(SystemSetting::class, 'depends_on_setting_id');
    }

    public function getDependsOnSettingAttribute(): ?SystemSetting
    {
        $relation = $this->getRelationValue('dependsOnSettingRelation');

        if ($relation instanceof SystemSetting) {
            return $relation;
        }

        $relation = $this->dependsOnSettingRelation()->getResults();

        if ($relation instanceof SystemSetting) {
            // Store the hydrated relationship under the canonical key so any
            // subsequent access during the request cycle reuses the same
            // instance and avoids duplicate database queries.
            $this->setRelation('dependsOnSettingRelation', $relation);
        }

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
        // Normalise whitespace so callers can pass a loosely formatted string
        // (for example " enabled ") without impacting query results.
        $condition = trim($condition);

        if ($condition === '') {
            return $query;
        }

        // Use a lower-cased pattern to keep lookups database agnostic – SQLite,
        // MySQL and Postgres handle LIKE/ILIKE comparisons differently, so we
        // apply LOWER() consistently to both the database column and the
        // comparison value.
        $pattern = '%' . Str::lower($condition) . '%';

        return $query->where(function (Builder $builder) use ($pattern): void {
            $builder
                ->whereRaw('LOWER(' . $builder->qualifyColumn('condition') . ') LIKE ?', [$pattern])
                ->orWhereRaw('LOWER(' . $builder->qualifyColumn('condition_value') . ') LIKE ?', [$pattern]);
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

        $pattern = '%' . Str::lower($search) . '%';

        return $query->where(function (Builder $builder) use ($pattern): void {
            $builder
                // Ensure all comparisons share the same normalisation approach so
                // the search scope behaves consistently across supported drivers.
                ->whereRaw('LOWER(' . $builder->qualifyColumn('condition') . ') LIKE ?', [$pattern])
                ->orWhereRaw('LOWER(' . $builder->qualifyColumn('condition_value') . ') LIKE ?', [$pattern])
                ->orWhereHas('setting', function (Builder $relation) use ($pattern): void {
                    $relation
                        ->whereRaw('LOWER(' . $relation->qualifyColumn('key') . ') LIKE ?', [$pattern])
                        ->orWhereRaw('LOWER(' . $relation->qualifyColumn('name') . ') LIKE ?', [$pattern]);
                })
                // Interrogate the legacy dependsOnSettingRelation association so searches work without
                // invoking the computed dependsOnSetting attribute accessor directly.
                ->orWhereHas('dependsOnSettingRelation', function (Builder $relation) use ($pattern): void {
                    $relation
                        ->whereRaw('LOWER(' . $relation->qualifyColumn('key') . ') LIKE ?', [$pattern])
                        ->orWhereRaw('LOWER(' . $relation->qualifyColumn('name') . ') LIKE ?', [$pattern]);
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
                // Cache the resolved relation on the canonical key to avoid
                // redundant database calls when multiple comparisons are
                // evaluated against the same dependency instance.
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

        // Normalise both the actual value and the expected comparison target so that
        // downstream comparisons work consistently across strings, numbers, and
        // boolean-like payloads (e.g. "true", "false", 1, 0).
        $normalizedValue = $this->normalizeComparableValue($dependencyValue);
        $normalizedExpected = $this->normalizeComparableValue($expectedValue);

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
        // Ensure both operands use comparable scalar types so that boolean strings
        // such as "true" or "false" evaluate predictably when matched against real
        // boolean values stored in the database.
        $actual = $this->normalizeComparableValue($actual);
        $expected = $this->normalizeComparableValue($expected);

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
                $expected = array_map(trim(...), array_filter(explode(',', $expected), static fn ($item): bool => $item !== ''));
            }
        }

        if (! is_array($expected)) {
            return false;
        }

        // Align list values with the same normalisation rules used by the primary
        // comparator so that numeric and boolean entries match consistently.
        $normalizedExpected = array_map(
            $this->normalizeComparableValue(...),
            $expected
        );

        return in_array($actual, $normalizedExpected, ! is_string($actual));
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

    /**
     * Normalise incoming values prior to comparisons so mixed types can be
     * evaluated with the same semantics (trim strings, decode boolean-like
     * tokens, and recurse through arrays when required).
     */
    private function normalizeComparableValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(
                $this->normalizeComparableValue(...),
                $value
            );
        }

        if (! is_string($value)) {
            return $value;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return '';
        }

        $lowercase = strtolower($trimmed);

        if (in_array($lowercase, ['true', 'false'], true)) {
            return $lowercase === 'true';
        }

        if (is_numeric($trimmed)) {
            // Preserve integer precision where possible while still supporting
            // decimal comparisons for floating point strings.
            return str_contains($trimmed, '.') ? (float) $trimmed : (int) $trimmed;
        }

        return $trimmed;
    }
}
