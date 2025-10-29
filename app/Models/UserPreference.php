<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\UserOwnedScope;
use Database\Factories\UserPreferenceFactory;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use JsonSerializable;

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
    protected $casts = [
        // Persist the canonical attributes with casts so Carbon instances and floats are returned consistently.
        'preference_score' => 'float',
        'last_updated'     => 'datetime',
        // Treat metadata as JSON so arrays are serialised correctly instead of triggering SQLite array-to-string errors.
        'metadata' => 'array',
    ];

    /**
     * Translate legacy attribute names into the modern aliases so existing factories continue to work.
     */
    public function fill(array $attributes)
    {
        // Bridge legacy column names onto the streamlined aliases expected by tests and modern code paths.
        $aliases = [
            'preference_type'  => 'name',
            'preference_key'   => 'key',
            'preference_score' => 'value',
        ];

        foreach ($aliases as $legacy => $alias) {
            // Copy the legacy value only when the alias is not already provided to avoid overwriting explicit input.
            if (array_key_exists($legacy, $attributes) && ! array_key_exists($alias, $attributes)) {
                $attributes[$alias] = $attributes[$legacy];
            }
        }

        // Ensure metadata assignments work for both the alias (`meta`) and the original (`metadata`) key.
        if (array_key_exists('metadata', $attributes) && ! array_key_exists('meta', $attributes)) {
            $attributes['meta'] = $attributes['metadata'];
        }

        // Capture manual timestamps before letting the parent implementation discard guarded attributes.
        $lastUpdated = $attributes['last_updated'] ?? null;

        $model = parent::fill($attributes);

        if ($lastUpdated !== null) {
            // Apply the timestamp directly so factories and fixtures can override it deterministically.
            $this->setAttribute('last_updated', $lastUpdated);
        }

        return $model;
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
            get: self::normaliseScore(...),
            set: self::normaliseScore(...),
        );
    }

    /**
     * Bridge the friendly value alias onto the stored preference_score column.
     *
     * @return Attribute<float|null, array<string, float|null>>
     */
    protected function value(): Attribute
    {
        // Link the friendly alias onto the stored `preference_score` column so existing queries keep working.
        return Attribute::make(
            get: static fn (mixed $value, array $attributes): ?float => self::normaliseScore($attributes['preference_score'] ?? $value),
            set: static fn (mixed $value): array => ['preference_score' => self::normaliseScore($value)],
        );
    }

    /**
     * Map the shorthand name alias onto the persisted preference_type column.
     */
    /**
     * @return Attribute<string|null, array<string, string|null>>
     */
    protected function name(): Attribute
    {
        // Provide read/write access to the `preference_type` column using the concise alias while guarding the string cast for static analysis.
        return Attribute::make(
            /** @return string|null */
            get: static function (mixed $value, array $attributes): ?string {
                $resolved = $attributes['preference_type'] ?? $value;

                return is_string($resolved) ? $resolved : null;
            },
            /** @return array<string, string|null> */
            set: static fn (mixed $value): array => ['preference_type' => is_string($value) ? $value : null],
        );
    }

    /**
     * Map the shorthand key alias onto the persisted preference_key column.
     */
    /**
     * @return Attribute<string|null, array<string, string|null>>
     */
    protected function key(): Attribute
    {
        // Keep the accessor focused on mapping the alias back to the persisted key column while validating the resolved payload.
        return Attribute::make(
            /** @return string|null */
            get: static function (mixed $value, array $attributes): ?string {
                $resolved = $attributes['preference_key'] ?? $value;

                return is_string($resolved) ? $resolved : null;
            },
            /** @return array<string, string|null> */
            set: static fn (mixed $value): array => ['preference_key' => is_string($value) ? $value : null],
        );
    }

    /**
     * Ensure metadata aliasing keeps JSON payloads in sync with the stored column.
     *
     * @return Attribute<array<string, mixed>|null, array<string, mixed>|null>
     */
    protected function meta(): Attribute
    {
        // Normalise JSON payloads so callers always interact with PHP arrays when reading or writing metadata.
        return Attribute::make(
            get: static function (mixed $value, array $attributes): ?array {
                $payload = $attributes['metadata'] ?? $value;

                if ($payload === null) {
                    return null;
                }

                if ($payload instanceof Arrayable) {
                    return $payload->toArray();
                }

                if ($payload instanceof JsonSerializable) {
                    $payload = $payload->jsonSerialize();
                }

                if (is_string($payload)) {
                    $decoded = json_decode($payload, true);

                    return is_array($decoded) ? $decoded : null;
                }

                return is_array($payload) ? $payload : null;
            },
            set: static function (mixed $value): array {
                if ($value instanceof JsonSerializable) {
                    $value = $value->jsonSerialize();
                }

                if ($value instanceof Arrayable) {
                    $value = $value->toArray();
                }

                if (is_string($value)) {
                    $decoded = json_decode($value, true);

                    if (json_last_error() === JSON_ERROR_NONE) {
                        $value = $decoded;
                    }
                }

                if ($value === []) {
                    // Encode empty arrays explicitly so SQLite bindings receive a JSON string rather than a bare array.
                    return ['metadata' => json_encode([], JSON_THROW_ON_ERROR)];
                }

                if (! is_array($value)) {
                    return ['metadata' => null];
                }

                // Encode structured arrays to JSON strings to keep the database payload compliant across drivers.
                return ['metadata' => json_encode($value, JSON_THROW_ON_ERROR)];
            },
        );
    }

    /**
     * Resolve the ordering column by translating aliases back into legacy column names.
     */
    protected function getNameColumn(): string
    {
        // Translate the alias used within the model into the actual database column leveraged for ordering scopes.
        return match ($this->nameColumn) {
            'name'  => 'preference_type',
            'key'   => 'preference_key',
            default => $this->getOrdersByNameColumn(),
        };
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
        // Rely on the stored precision to filter interactions above the requested score.
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
        // Default to descending order so higher scores are surfaced first in listings.
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
        // Apply a simple range filter against the timestamp column to surface recently updated preferences.
        return $query->where('last_updated', '>=', now()->subDays($days));
    }
}
