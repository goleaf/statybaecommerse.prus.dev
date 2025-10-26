<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\UserOwnedScope;
use Database\Factories\UserPreferenceFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * UserPreference
 *
 * Eloquent model representing the UserPreference entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property int                             $id
 * @property int                             $user_id
 * @property string                          $preference_type
 * @property string                          $preference_key
 * @property float|null                      $preference_score
 * @property array<string, mixed>|null       $metadata
 * @property \Illuminate\Support\Carbon      $last_updated
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|UserPreference newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPreference newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPreference query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPreference byType(string $type)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPreference withMinScore(float $minScore)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPreference orderedByScore()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPreference recent(int $days = 30)
 *
 * @mixin \Eloquent
 */

/**
 * @use HasFactory<UserPreferenceFactory>
 */
#[ScopedBy([UserOwnedScope::class])]
final class UserPreference extends Model
{
    /** @use HasFactory<UserPreferenceFactory> */
    use HasFactory;

    use OrdersByName {
        // Alias the trait helper so we can translate friendly aliases back to persisted columns.
        OrdersByName::getNameColumn as protected getOrdersByNameColumn;
    }

    /**
     * Keep a friendly alias for ordered-by-name lookups; this will later be mapped to the stored column.
     */
    protected string $nameColumn = 'key';

    /**
     * Surface the streamlined attribute aliases that downstream code expects to mass-assign.
     *
     * @var list<string>
     */
    protected $fillable = ['user_id', 'name', 'key', 'value', 'meta'];

    /**
     * Ensure the metadata alias keeps behaving like an array even though it is persisted as JSON.
     *
     * @var array<string, string>
     */
    protected $casts = ['meta' => 'array'];

    /**
     * Translate legacy attribute names into the modern aliases so existing factories continue to work.
     */
    public function fill(array $attributes)
    {
        // Provide a deterministic mapping from the historical columns to their new alias-based counterparts.
        $aliases = [
            'preference_type'  => 'name',
            'preference_key'   => 'key',
            'preference_score' => 'value',
            'metadata'         => 'meta',
        ];

        foreach ($aliases as $legacy => $alias) {
            if (array_key_exists($legacy, $attributes) && ! array_key_exists($alias, $attributes)) {
                $attributes[$alias] = $attributes[$legacy];
            }

            unset($attributes[$legacy]);
        }

        // Respect manual timestamp overrides without exposing the column to mass-assignment directly.
        $lastUpdated = $attributes['last_updated'] ?? null;
        unset($attributes['last_updated']);

        $result = parent::fill($attributes);

        if ($lastUpdated !== null) {
            $this->setAttribute('last_updated', $lastUpdated);
        }

        return $result;
    }

    /**
     * Normalise floating point scores so both the legacy attribute and the new alias stay aligned.
     */
    private static function normaliseScore(mixed $value): ?float
    {
        // Round to six decimals to mirror the database definition and avoid subtle precision drift.
        return is_numeric($value) ? round((float) $value, 6) : null;
    }

    /**
     * Keep direct access to the legacy preference_score column precise for any existing call sites.
     *
     * @return Attribute<float|null, float|null>
     */
    protected function preferenceScore(): Attribute
    {
        return Attribute::make(
            get: static fn (mixed $value): ?float => self::normaliseScore($value),
            set: static fn (mixed $value): ?float => self::normaliseScore($value),
        );
    }

    /**
     * Bridge the friendly value alias onto the stored preference_score column.
     *
     * @return Attribute<float|null, array<string, float|null>>
     */
    protected function value(): Attribute
    {
        return Attribute::make(
            get: static fn (mixed $value, array $attributes): ?float => self::normaliseScore($attributes['preference_score'] ?? $value),
            set: static fn (mixed $value): array => ['preference_score' => self::normaliseScore($value)],
        );
    }

    /**
     * Map the shorthand name alias onto the persisted preference_type column.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: static fn (mixed $value, array $attributes): ?string => $attributes['preference_type'] ?? ($value !== null ? (string) $value : null),
            set: static fn (mixed $value): array => ['preference_type' => $value],
        );
    }

    /**
     * Map the shorthand key alias onto the persisted preference_key column.
     */
    protected function key(): Attribute
    {
        return Attribute::make(
            get: static fn (mixed $value, array $attributes): ?string => $attributes['preference_key'] ?? ($value !== null ? (string) $value : null),
            set: static fn (mixed $value): array => ['preference_key' => $value],
        );
    }

    /**
     * Ensure metadata aliasing keeps JSON payloads in sync with the stored column.
     *
     * @return Attribute<array<string, mixed>|null, array<string, mixed>|null>
     */
    protected function meta(): Attribute
    {
        return Attribute::make(
            get: static function (mixed $value, array $attributes): ?array {
                // Use the persisted JSON payload whenever available and decode string representations safely.
                $payload = $attributes['metadata'] ?? $value;

                if (is_string($payload)) {
                    $decoded = json_decode($payload, true);

                    return is_array($decoded) ? $decoded : null;
                }

                return is_array($payload) ? $payload : null;
            },
            set: static fn (mixed $value): array => ['metadata' => is_array($value) ? $value : null],
        );
    }

    /**
     * Resolve the ordering column by translating aliases back into legacy column names.
     */
    protected function getNameColumn(): string
    {
        return match ($this->nameColumn) {
            'name'  => 'preference_type',
            'key'   => 'preference_key',
            default => $this->getOrdersByNameColumn(),
        };
    }

    /**
     * Keep the last_updated attribute Carbon-backed without expanding the casts array.
     *
     * @return Attribute<Carbon|null, string|Carbon|null>
     */
    protected function lastUpdated(): Attribute
    {
        return Attribute::make(
            get: static fn (mixed $value): ?Carbon => $value === null ? null : Carbon::parse((string) $value),
            set: static fn (mixed $value): ?string => match (true) {
                $value instanceof Carbon => $value->toDateTimeString(),
                is_string($value)        => $value,
                $value === null          => null,
                default                  => (string) $value,
            },
        );
    }

    /**
     * Handle user functionality with proper error handling.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        // Delegate to Eloquent's belongsTo helper to wire the inverse user relationship.
        return $this->belongsTo(User::class);
    }

    /**
     * Handle scopeByType functionality with proper error handling.
     *
     * @param  Builder<UserPreference> $query
     * @return Builder<UserPreference>
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('preference_type', $type);
    }

    /**
     * Handle scopeWithMinScore functionality with proper error handling.
     *
     * @param  Builder<UserPreference> $query
     * @return Builder<UserPreference>
     */
    public function scopeWithMinScore(Builder $query, float $minScore): Builder
    {
        return $query->where('preference_score', '>=', $minScore);
    }

    /**
     * Handle scopeOrderedByScore functionality with proper error handling.
     *
     * @param  Builder<UserPreference> $query
     * @return Builder<UserPreference>
     */
    public function scopeOrderedByScore(Builder $query): Builder
    {
        return $query->orderByDesc('preference_score');
    }

    /**
     * Handle scopeRecent functionality with proper error handling.
     *
     * @param  Builder<UserPreference> $query
     * @return Builder<UserPreference>
     */
    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('last_updated', '>=', now()->subDays($days));
    }
}
