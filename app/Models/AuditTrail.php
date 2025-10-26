<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use JsonException;

/**
 * @property array<string, array{previous:mixed,current:mixed}>|null $diff
 */
final class AuditTrail extends Model
{
    // Enable Laravel's factory helpers for this model to simplify seeding and testing.
    use HasFactory;

    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'event',
        'actor_type',
        'actor_id',
        'reason',
        'request_id',
        'diff',
    ];

    protected $casts = [
        'diff' => 'array',
    ];

    /**
     * @return MorphTo<Model, AuditTrail>
     */
    public function auditable(): MorphTo
    {
        /** @var MorphTo<Model, AuditTrail> $relation */
        $relation = $this->morphTo();

        return $relation;
    }

    /**
     * @return MorphTo<Model, AuditTrail>
     */
    public function actor(): MorphTo
    {
        /** @var MorphTo<Model, AuditTrail> $relation */
        $relation = $this->morphTo();

        return $relation;
    }

    /**
     * Build a human-readable label for the auditable record reference.
     */
    public function getAuditableLabelAttribute(): string
    {
        $type = class_basename($this->auditable_type ?? '');

        return trim(sprintf('%s #%s', $type, $this->auditable_id));
    }

    /**
     * Resolve a displayable identifier for the actor, falling back to a localized string.
     */
    public function getActorDisplayNameAttribute(): string
    {
        if ($this->actor) {
            if (isset($this->actor->name) && is_string($this->actor->name)) {
                return $this->actor->name;
            }

            if (isset($this->actor->email) && is_string($this->actor->email)) {
                return $this->actor->email;
            }
        }

        return __('admin.audit_trails.system_actor');
    }

    public function getDiffKeysAttribute(): string
    {
        $diff = Arr::wrap($this->diff);

        if ($diff === []) {
            return '';
        }

        return implode(', ', array_keys($diff));
    }

    /**
     * Render the diff payload as a pretty-printed JSON string for logs or diagnostics.
     *
     * @throws JsonException
     */
    public function getDiffPrettyAttribute(): string
    {
        return json_encode($this->diff, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    /**
     * @param  array<string, mixed>                               $before
     * @param  array<string, mixed>                               $after
     * @return array<string, array{previous:mixed,current:mixed}>
     */
    public static function diff(array $before, array $after): array
    {
        $diff = [];
        $keys = array_values(array_unique(array_merge(array_keys($before), array_keys($after))));

        foreach ($keys as $key) {
            $previous = $before[$key] ?? null;
            $current = $after[$key] ?? null;

            if (! self::valuesDiffer($previous, $current)) {
                continue;
            }

            $diff[$key] = [
                'previous' => self::serializeValue($previous),
                'current'  => self::serializeValue($current),
            ];
        }

        return $diff;
    }

    public static function valuesDiffer(mixed $previous, mixed $current): bool
    {
        return self::normalizeValue($previous) !== self::normalizeValue($current);
    }

    /**
     * @param array<string, array{previous:mixed,current:mixed}> $diff
     *
     * @throws JsonException
     */
    public static function record(Model $auditable, array $diff, string $event, ?string $reason = null): void
    {
        if ($diff === []) {
            return;
        }

        $actor = auth('admin')->user() ?? auth()->user();

        $requestId = self::resolveRequestId();

        // Persist the diff snapshot alongside actor and correlation identifiers for traceability.
        self::query()->create([
            'auditable_type' => $auditable->getMorphClass(),
            'auditable_id'   => $auditable->getKey(),
            'event'          => $event,
            'actor_type'     => $actor?->getMorphClass(),
            'actor_id'       => $actor?->getKey(),
            'reason'         => $reason !== null ? trim($reason) : null,
            'request_id'     => $requestId,
            'diff'           => $diff,
        ]);
    }

    private static function serializeValue(mixed $value): mixed
    {
        if ($value instanceof CarbonInterface) {
            return $value->toAtomString();
        }

        if ($value instanceof Model) {
            return $value->getKey();
        }

        if (is_array($value)) {
            return array_map(static fn ($item) => self::serializeValue($item), $value);
        }

        if (is_object($value)) {
            return json_decode(json_encode($value, JSON_THROW_ON_ERROR), true);
        }

        return $value;
    }

    private static function normalizeValue(mixed $value): string
    {
        if ($value instanceof CarbonInterface) {
            return $value->toAtomString();
        }

        if ($value instanceof Model) {
            $key = $value->getKey();

            if (is_int($key) || is_float($key) || is_string($key)) {
                return (string) $key;
            }

            if ($key === null) {
                return 'null';
            }

            return json_encode(self::serializeValue($key), JSON_THROW_ON_ERROR);
        }

        if (is_array($value)) {
            return json_encode(array_map(static fn ($item) => self::serializeValue($item), $value), JSON_THROW_ON_ERROR);
        }

        if (is_object($value)) {
            return json_encode(self::serializeValue($value), JSON_THROW_ON_ERROR);
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if ($value === null) {
            return 'null';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (is_string($value)) {
            return $value;
        }

        return json_encode(self::serializeValue($value), JSON_THROW_ON_ERROR);
    }

    private static function resolveRequestId(): string
    {
        if (app()->bound('request_correlation_id')) {
            $correlation = app()->make('request_correlation_id');
            if (is_string($correlation) && $correlation !== '') {
                return $correlation;
            }
        }

        if (app()->bound('request')) {
            $request = request();
            $candidates = [
                $request->headers->get('X-Request-Id'),
                $request->headers->get('X-Correlation-ID'),
                $request->attributes->get('correlation_id'),
                $request->input('request_id'),
            ];

            foreach ($candidates as $candidate) {
                if (is_string($candidate) && $candidate !== '') {
                    return $candidate;
                }
            }
        }

        return Str::uuid()->toString();
    }
}
