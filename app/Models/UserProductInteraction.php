<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use Database\Factories\UserProductInteractionFactory;
use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use JsonException;
use JsonSerializable;
use Traversable;

/**
 * UserProductInteraction
 *
 * Bridges legacy analytics attributes (rating/count/interaction timestamps)
 * with the newer event/meta schema so both generations of code continue to
 * operate without surprises.
 *
 * @use HasFactory<UserProductInteractionFactory>
 *
 * @phpstan-use HasFactory<UserProductInteractionFactory>
 */
final class UserProductInteraction extends Model
{
    /** @phpstan-use HasFactory<UserProductInteractionFactory> */
    use HasFactory;

    use OrdersByName;

    /**
     * Column leveraged by the shared OrdersByName scope when applying
     * alphabetical sorting in grids.
     */
    protected string $nameColumn = 'event';

    /**
     * Modern attributes that can be mass assigned safely.
     */
    protected $fillable = [
        'user_id',
        'product_id',
        'product_variant_id',
        'event',
        'meta',
        'occurred_at',
    ];

    /**
     * Attribute casting rules for the consolidated payload fields.
     */
    protected $casts = [
        'meta'              => 'array',
        'occurred_at'       => 'datetime',
        'first_interaction' => 'datetime',
        'last_interaction'  => 'datetime',
        'rating'            => 'float',
        'count'             => 'integer',
    ];

    /**
     * Normalise legacy payloads before the base fill logic runs so both the
     * new and old attribute names hydrate correctly.
     *
     * @param array<string,mixed> $attributes
     */
    public function fill(array $attributes)
    {
        [$normalized, $legacyAssignments] = $this->mapLegacyAttributes($attributes);

        $model = parent::fill($normalized);

        foreach ($legacyAssignments as $key => $value) {
            // Directly assign the legacy columns so existing analytics queries
            // continue to function without needing to opt into the meta array.
            $this->setAttribute($key, $value);
        }

        return $model;
    }

    /**
     * Convert legacy attribute keys into their modern counterparts and collect
     * any column assignments that should bypass mass-assignment protections.
     *
     * @param  array<string,mixed>                                   $attributes
     * @return array{0: array<string,mixed>, 1: array<string,mixed>}
     */
    private function mapLegacyAttributes(array $attributes): array
    {
        $normalized = $attributes;

        if (array_key_exists('interaction_type', $normalized) && ! array_key_exists('event', $normalized)) {
            $normalized['event'] = $normalized['interaction_type'];
        }

        if (array_key_exists('last_interaction', $normalized) && ! array_key_exists('occurred_at', $normalized)) {
            $normalized['occurred_at'] = $normalized['last_interaction'];
        }

        $legacyAssignments = [];
        $metaPayload = $normalized['meta'] ?? [];
        $metaPayload = is_array($metaPayload) ? $metaPayload : [];

        foreach (['rating', 'count', 'first_interaction', 'last_interaction', 'notes', 'is_anonymous', 'ip_address'] as $legacyKey) {
            if (array_key_exists($legacyKey, $normalized)) {
                $legacyAssignments[$legacyKey] = $normalized[$legacyKey];
                $metaPayload[$legacyKey] ??= $normalized[$legacyKey];
                unset($normalized[$legacyKey]);
            }
        }

        if ($metaPayload !== [] && ! array_key_exists('meta', $normalized)) {
            $normalized['meta'] = $metaPayload;
        }

        unset($normalized['interaction_type']);

        return [$normalized, $legacyAssignments];
    }

    /**
     * Provide a clean interface for the event column, while falling back to the
     * legacy interaction_type attribute if a migration has not been executed yet.
     *
     * @return Attribute<?string, array{event: ?string}>
     */
    protected function event(): Attribute
    {
        return Attribute::make(
            get: static function ($value, array $attributes): ?string {
                $event = $value ?? ($attributes['interaction_type'] ?? null);

                if ($event === null) {
                    return null;
                }

                if (is_string($event)) {
                    return $event;
                }

                if (is_numeric($event)) {
                    return (string) $event;
                }

                return null;
            },
            set: static fn (?string $value): array => ['event' => $value],
        );
    }

    /**
     * Synchronise the JSON meta payload with the legacy scalar columns to keep
     * historical analytics logic functioning.
     *
     * @return Attribute<array<string, mixed>, array<string, mixed>>
     */
    protected function meta(): Attribute
    {
        return Attribute::make(
            get: function ($value, array $attributes): array {
                $decoded = [];

                if (is_string($value) && $value !== '') {
                    try {
                        $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR) ?: [];
                    } catch (JsonException) {
                        $decoded = [];
                    }
                } elseif (is_array($value)) {
                    $decoded = $value;
                }

                /** @var array<string, mixed> $decoded */
                $decoded = is_array($decoded) ? $decoded : [];

                foreach (['rating', 'count', 'first_interaction', 'last_interaction', 'notes', 'is_anonymous', 'ip_address'] as $legacyKey) {
                    if (! array_key_exists($legacyKey, $decoded) && array_key_exists($legacyKey, $attributes)) {
                        $decoded[$legacyKey] = $attributes[$legacyKey];
                    }
                }

                if (! array_key_exists('occurred_at', $decoded) && array_key_exists('occurred_at', $attributes)) {
                    $decoded['occurred_at'] = $attributes['occurred_at'];
                }

                if (array_key_exists('rating', $decoded) && $decoded['rating'] !== null) {
                    // Ensure the rating remains a float regardless of whether it came from JSON
                    // decoding or the legacy scalar columns.
                    $decoded['rating'] = is_numeric($decoded['rating'])
                        ? (float) $decoded['rating']
                        : null;
                }

                if (array_key_exists('count', $decoded) && $decoded['count'] !== null) {
                    $decoded['count'] = is_numeric($decoded['count'])
                        ? (int) $decoded['count']
                        : null;
                }

                if (array_key_exists('is_anonymous', $decoded)) {
                    $decoded['is_anonymous'] = (bool) $decoded['is_anonymous'];
                }

                return $decoded;
            },
            set: function ($value): array {
                // Convert the provided value into a simple array so we can safely encode
                // it to JSON before persisting it to the database.
                $metaArray = $this->resolveMetaArray($value);

                try {
                    // Encode to JSON so SQLite/MySQL receive a string payload instead of a raw array.
                    $encodedMeta = json_encode($metaArray, JSON_THROW_ON_ERROR);
                } catch (JsonException) {
                    // Fall back to an empty JSON object if encoding fails; this prevents
                    // database binding errors while keeping the attribute predictable.
                    $encodedMeta = json_encode([], JSON_THROW_ON_ERROR);
                }

                $payload = ['meta' => $encodedMeta];

                foreach (['rating', 'count', 'first_interaction', 'last_interaction', 'notes', 'is_anonymous', 'ip_address'] as $legacyKey) {
                    if (! array_key_exists($legacyKey, $metaArray)) {
                        continue;
                    }

                    $value = $metaArray[$legacyKey];

                    if ($legacyKey === 'rating' && $value !== null) {
                        $value = is_numeric($value) ? (float) $value : null;
                    }

                    if ($legacyKey === 'count' && $value !== null) {
                        $value = is_numeric($value) ? (int) $value : null;
                    }

                    if ($legacyKey === 'is_anonymous') {
                        $value = (bool) $value;
                    }

                    $payload[$legacyKey] = $value;
                }

                $existingOccurredAt = $this->prepareTemporalValue($this->getAttribute('occurred_at'));
                $existingFirstInteraction = $this->prepareTemporalValue($this->getAttribute('first_interaction'));
                $existingLastInteraction = $this->prepareTemporalValue($this->getAttribute('last_interaction'));

                $metaOccurredAt = $this->prepareTemporalValue($metaArray['occurred_at'] ?? null);
                $metaFirstInteraction = $this->prepareTemporalValue($metaArray['first_interaction'] ?? null);
                $metaLastInteraction = $this->prepareTemporalValue($metaArray['last_interaction'] ?? null);

                $occurredAtFromMeta = $metaOccurredAt !== null
                    ? $this->normaliseTimestamp($metaOccurredAt)
                    : ($existingOccurredAt !== null ? $this->normaliseTimestamp($existingOccurredAt) : null);

                if ($occurredAtFromMeta instanceof \Illuminate\Support\Carbon) {
                    $payload['occurred_at'] = $occurredAtFromMeta;
                }

                $firstInteractionSource = $metaFirstInteraction
                    ?? $existingFirstInteraction
                    ?? $metaOccurredAt
                    ?? $existingLastInteraction
                    ?? now();

                $lastInteractionSource = $metaLastInteraction
                    ?? $existingLastInteraction
                    ?? $metaOccurredAt
                    ?? $firstInteractionSource;

                $payload['first_interaction'] = $this->normaliseTimestamp($firstInteractionSource);
                $payload['last_interaction'] = $this->normaliseTimestamp($lastInteractionSource);

                return $payload;
            }
        );
    }

    /**
     * Keep the occurred_at attribute aligned with the legacy last_interaction
     * timestamp so chronological reports remain accurate.
     */
    /**
     * @return Attribute<?Carbon, array{occurred_at: ?Carbon, last_interaction: ?Carbon}>
     */
    protected function occurredAt(): Attribute
    {
        return Attribute::make(
            get: function ($value, array $attributes): ?Carbon {
                $raw = $value ?? $attributes['last_interaction'] ?? null;

                $temporal = $this->prepareTemporalValue($raw);

                if ($temporal === null) {
                    return null;
                }

                return $this->normaliseTimestamp($temporal);
            },
            set: function ($value): array {
                $carbon = $this->normaliseTimestamp($this->prepareTemporalValue($value));

                return [
                    'occurred_at'      => $carbon,
                    'last_interaction' => $carbon,
                ];
            }
        );
    }

    /**
     * Helper to coerce various datetime inputs into Carbon instances so the
     * timestamp columns stay consistent regardless of caller payload shape.
     */
    private function normaliseTimestamp(DateTimeInterface|float|int|string|null $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value);
        }

        if ($value === null || $value === '') {
            return null;
        }

        $date = Carbon::make($value);

        if ($date instanceof Carbon) {
            return $date;
        }

        if (is_int($value) || is_float($value)) {
            return Carbon::createFromTimestamp((int) $value);
        }

        return Carbon::parse($value);
    }

    /**
     * Normalise arbitrary temporal values to a subset that Carbon can parse reliably.
     */
    private function prepareTemporalValue(mixed $value): DateTimeInterface|float|int|string|null
    {
        if ($value instanceof DateTimeInterface) {
            return $value;
        }

        if (is_int($value) || is_float($value) || is_string($value)) {
            return $value;
        }

        return null;
    }

    /**
     * Convert incoming meta payloads into a consistent array structure.
     *
     * @return array<string, mixed>
     */
    private function resolveMetaArray(mixed $value): array
    {
        if (is_array($value)) {
            return $this->stringifyKeys($value);
        }

        if ($value instanceof Arrayable) {
            return $this->stringifyKeys($value->toArray());
        }

        if ($value instanceof JsonSerializable) {
            $serialised = $value->jsonSerialize();

            return $this->stringifyKeys(is_array($serialised) ? $serialised : (array) $serialised);
        }

        if ($value instanceof Traversable) {
            return $this->stringifyKeys(iterator_to_array($value));
        }

        return [];
    }

    /**
     * Ensure array keys are consistently strings for downstream casting helpers.
     *
     * @param  array<mixed, mixed>  $payload
     * @return array<string, mixed>
     */
    private function stringifyKeys(array $payload): array
    {
        $normalised = [];

        foreach ($payload as $key => $item) {
            $normalised[(string) $key] = $item;
        }

        return $normalised;
    }

    /**
     * Backwards-compatible accessor for the legacy interaction_type attribute.
     */
    public function getInteractionTypeAttribute(): ?string
    {
        $event = $this->getAttributeValue('event');

        if ($event === null) {
            return null;
        }

        if (is_string($event)) {
            return $event;
        }

        if (is_numeric($event)) {
            return (string) $event;
        }

        return null;
    }

    /**
     * Backwards-compatible mutator for the legacy interaction_type attribute.
     */
    public function setInteractionTypeAttribute(?string $value): void
    {
        $this->setAttribute('event', $value);
    }

    /**
     * Relationship: the user associated with the interaction.
     *
     * @return BelongsTo<User, self>
     *
     * @phpstan-return BelongsTo<User, UserProductInteraction>
     */
    public function user(): BelongsTo
    {
        /** @var BelongsTo<User, UserProductInteraction> $relation */
        $relation = $this->belongsTo(User::class);

        return $relation;
    }

    /**
     * Relationship: the product that was interacted with.
     *
     * @return BelongsTo<Product, self>
     *
     * @phpstan-return BelongsTo<Product, UserProductInteraction>
     */
    public function product(): BelongsTo
    {
        /** @var BelongsTo<Product, UserProductInteraction> $relation */
        $relation = $this->belongsTo(Product::class)->withoutGlobalScopes([
            Scopes\ActiveScope::class,
            Scopes\PublishedScope::class,
            Scopes\VisibleScope::class,
        ]);

        return $relation;
    }

    /**
     * Relationship: the specific product variant when available.
     *
     * @return BelongsTo<ProductVariant, self>
     *
     * @phpstan-return BelongsTo<ProductVariant, UserProductInteraction>
     */
    public function variant(): BelongsTo
    {
        /** @var BelongsTo<ProductVariant, UserProductInteraction> $relation */
        $relation = $this->belongsTo(ProductVariant::class, 'product_variant_id');

        return $relation;
    }

    /**
     * Scope helper: limit results to a specific event name.
     */
    /**
     * @param  Builder<UserProductInteraction> $query
     * @return Builder<UserProductInteraction>
     */
    public function scopeByType(Builder $query, string $event): Builder
    {
        return $query->where('event', $event);
    }

    /**
     * Scope helper: filter interactions by user.
     */
    /**
     * @param  Builder<UserProductInteraction> $query
     * @return Builder<UserProductInteraction>
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope helper: filter interactions by product.
     */
    /**
     * @param  Builder<UserProductInteraction> $query
     * @return Builder<UserProductInteraction>
     */
    public function scopeByProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope helper: ensure interactions meet the minimum count requirement.
     */
    /**
     * @param  Builder<UserProductInteraction> $query
     * @return Builder<UserProductInteraction>
     */
    public function scopeWithMinCount(Builder $query, int $minCount): Builder
    {
        return $query->where('count', '>=', $minCount);
    }

    /**
     * Scope helper: limit to interactions at or above the requested rating.
     */
    /**
     * @param  Builder<UserProductInteraction> $query
     * @return Builder<UserProductInteraction>
     */
    public function scopeWithMinRating(Builder $query, float $minRating): Builder
    {
        return $query->where('rating', '>=', $minRating);
    }

    /**
     * Scope helper: limit to interactions occurring on or after the provided
     * threshold.
     */
    /**
     * @param  Builder<UserProductInteraction> $query
     * @return Builder<UserProductInteraction>
     */
    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->whereDate('occurred_at', '>=', now()->subDays($days));
    }

    /**
     * Increment helper that keeps the legacy counters in sync with the new
     * occurred_at/meta payloads.
     */
    public function incrementInteraction(?float $rating = null): void
    {
        $newCount = ((int) $this->count) + 1;

        /** @var array<string, mixed> $meta */
        $meta = $this->meta;
        $meta['rating'] = $rating ?? $this->rating;
        $meta['count'] = $newCount;
        $meta['last_interaction'] = now();

        $this->update([
            'count'            => $newCount,
            'rating'           => $meta['rating'],
            'last_interaction' => $meta['last_interaction'],
            'occurred_at'      => $meta['last_interaction'],
            'meta'             => $meta,
        ]);
    }
}
