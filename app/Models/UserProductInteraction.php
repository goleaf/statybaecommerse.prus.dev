<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;
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
 */
final class UserProductInteraction extends Model
{
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
        // Cast rating to float so incremental updates preserve decimal precision for analytics.
        'rating'      => 'float',
        'meta'        => 'array',
        'occurred_at' => 'datetime',
    ];

    /**
     * Automatically seed the legacy interaction timestamps when callers omit
     * them so the NOT NULL constraints introduced by the original schema stay
     * satisfied even when only the modern occurred_at value is provided.
     */
    protected static function booted(): void
    {
        parent::booted();

        self::creating(function (self $interaction): void {
            // Resolve the effective occurrence timestamp before mutating the
            // legacy attributes, allowing fallbacks for inserts that only
            // specify the modern occurred_at column.
            $occurredAt = $interaction->occurred_at ?? now();

            if ($interaction->getAttribute('first_interaction') === null) {
                // Default the first interaction to the resolved occurrence
                // timestamp so initial inserts respect the historical column
                // contract without requiring callers to set both values.
                $interaction->setAttribute('first_interaction', $occurredAt);
            }

            if ($interaction->getAttribute('last_interaction') === null) {
                // Mirror the last interaction timestamp as well, keeping the
                // legacy analytics queries aligned with the most recent event
                // even when only the consolidated occurred_at value is passed
                // through the modern API surface.
                $interaction->setAttribute('last_interaction', $occurredAt);
            }
        });
    }

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
     */
    protected function event(): Attribute
    {
        return Attribute::make(
            get: static fn ($value, array $attributes): ?string => $value ?? $attributes['interaction_type'] ?? null,
            set: static fn (?string $value): array => ['event' => $value],
        );
    }

    /**
     * Synchronise the JSON meta payload with the legacy scalar columns to keep
     * historical analytics logic functioning.
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

                foreach (['rating', 'count', 'first_interaction', 'last_interaction', 'notes', 'is_anonymous', 'ip_address'] as $legacyKey) {
                    if (! array_key_exists($legacyKey, $decoded) && array_key_exists($legacyKey, $attributes)) {
                        $decoded[$legacyKey] = $attributes[$legacyKey];
                    }
                }

                if (! array_key_exists('occurred_at', $decoded) && array_key_exists('occurred_at', $attributes)) {
                    $decoded['occurred_at'] = $attributes['occurred_at'];
                }

                // Normalise numeric fields so downstream comparisons do not break due to JSON encoding quirks.
                if (array_key_exists('rating', $decoded) && is_numeric($decoded['rating'])) {
                    $decoded['rating'] = (float) $decoded['rating'];
                }

                if (array_key_exists('count', $decoded) && is_numeric($decoded['count'])) {
                    $decoded['count'] = (int) $decoded['count'];
                }

                return $decoded;
            },
            set: function ($value): array {
                // Convert various inputs into a pure array so the JSON column stores a predictable payload.
                $metaArray = match (true) {
                    is_array($value)                   => $value,
                    $value instanceof Arrayable        => $value->toArray(),
                    $value instanceof JsonSerializable => (array) $value->jsonSerialize(),
                    $value instanceof Traversable      => iterator_to_array($value),
                    default                            => [],
                };

                // Encode the meta payload ahead of persistence to avoid SQLite array binding errors while keeping
                // downstream getters JSON aware. When encoding fails we intentionally fall back to an empty payload so
                // report generation can proceed without throwing QueryExceptions.
                try {
                    $encodedMeta = $metaArray === []
                        ? null
                        : json_encode($metaArray, JSON_THROW_ON_ERROR);
                } catch (JsonException) {
                    $encodedMeta = null;
                }

                $payload = ['meta' => $encodedMeta];

                foreach (['rating', 'count', 'first_interaction', 'last_interaction', 'notes', 'is_anonymous', 'ip_address'] as $legacyKey) {
                    if (! array_key_exists($legacyKey, $metaArray)) {
                        continue;
                    }

                    $value = $metaArray[$legacyKey];

                    if ($legacyKey === 'rating' && $value !== null) {
                        $value = (float) $value;
                    }

                    if ($legacyKey === 'count' && $value !== null) {
                        $value = (int) $value;
                    }

                    if ($legacyKey === 'is_anonymous') {
                        $value = (bool) $value;
                    }

                    $payload[$legacyKey] = $value;
                }

                if (array_key_exists('occurred_at', $metaArray)) {
                    $payload['occurred_at'] = $metaArray['occurred_at'];
                }

                return $payload;
            }
        );
    }

    /**
     * Keep the occurred_at attribute aligned with the legacy last_interaction
     * timestamp so chronological reports remain accurate.
     */
    protected function occurredAt(): Attribute
    {
        return Attribute::make(
            get: static function ($value, array $attributes): ?Carbon {
                $raw = $value ?? $attributes['last_interaction'] ?? null;

                if ($raw === null) {
                    return null;
                }

                return $raw instanceof Carbon ? $raw : Carbon::parse($raw);
            },
            set: static function ($value): array {
                $carbon = match (true) {
                    $value instanceof Carbon            => $value,
                    $value instanceof DateTimeInterface => Carbon::instance($value),
                    $value === null || $value === ''    => null,
                    default                             => Carbon::make($value) ?? Carbon::parse((string) $value),
                };

                return [
                    'occurred_at'      => $carbon,
                    'last_interaction' => $carbon,
                ];
            }
        );
    }

    /**
     * Backwards-compatible accessor for the legacy interaction_type attribute.
     */
    public function getInteractionTypeAttribute(): ?string
    {
        return $this->event;
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
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: the product that was interacted with.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withoutGlobalScopes([
            Scopes\ActiveScope::class,
            Scopes\PublishedScope::class,
            Scopes\VisibleScope::class,
        ]);
    }

    /**
     * Relationship: the specific product variant when available.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Scope helper: limit results to a specific event name.
     */
    public function scopeByType($query, string $event)
    {
        return $query->where('event', $event);
    }

    /**
     * Scope helper: filter interactions by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope helper: filter interactions by product.
     */
    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope helper: ensure interactions meet the minimum count requirement.
     */
    public function scopeWithMinCount($query, int $minCount)
    {
        return $query->where('count', '>=', $minCount);
    }

    /**
     * Scope helper: limit to interactions at or above the requested rating.
     */
    public function scopeWithMinRating($query, float $minRating)
    {
        return $query->where('rating', '>=', $minRating);
    }

    /**
     * Scope helper: limit to interactions occurring on or after the provided
     * threshold.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->whereDate('occurred_at', '>=', now()->subDays($days));
    }

    /**
     * Increment helper that keeps the legacy counters in sync with the new
     * occurred_at/meta payloads.
     */
    public function incrementInteraction(?float $rating = null): void
    {
        // Compute the next counter value locally so the JSON meta payload can
        // stay in sync with the atomic increment executed against the table.
        $nextCount = (int) $this->count + 1;

        // Bump the persisted counter column first, ensuring concurrent updates
        // do not lose the legacy `count` value that downstream reports expect.
        $this->increment('count');

        // Rebuild the consolidated meta structure with the refreshed counter,
        // rating information, and interaction timestamp mirror.
        $meta = $this->meta;
        $meta['count'] = $nextCount;

        $resolvedRating = $rating ?? ($meta['rating'] ?? $this->rating);
        $meta['rating'] = $resolvedRating !== null ? (float) $resolvedRating : null;

        $meta['last_interaction'] = now();

        // Force fill the legacy columns so fillable restrictions do not block
        // the synchronous update of the mirrored analytics fields.
        $this->forceFill([
            'count'            => $nextCount,
            'occurred_at'      => $meta['last_interaction'],
            'last_interaction' => $meta['last_interaction'],
            'meta'             => $meta,
            'rating'           => $meta['rating'],
        ])->save();
    }
}
