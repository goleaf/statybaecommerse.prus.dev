<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $key
 * @property string|null $secret
 * @property array|null $permissions
 * @property array|null $rate_limits
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $last_used_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property bool $is_active
 */
final class ApiKey extends Model
{
    use HasFactory;

    /**
     * Default max attempts for partner API requests when no custom limit is provided.
     */
    private const DEFAULT_MAX_ATTEMPTS = 60;

    /**
     * Default decay window (in seconds) for partner API requests when no custom limit is provided.
     */
    private const DEFAULT_DECAY_SECONDS = 60;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'permissions' => 'array',
        'rate_limits' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'key',
        'secret',
        'permissions',
        'rate_limits',
        'user_id',
        'last_used_at',
        'expires_at',
        'is_active',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function markAsUsed(): void
    {
        $this->forceFill([
            'last_used_at' => Carbon::now(),
        ])->save();
    }

    public function isExpired(): bool
    {
        return $this->expires_at instanceof Carbon && $this->expires_at->isPast();
    }

    /**
     * @return array<int, string>
     */
    public function resolvedAbilities(): array
    {
        $abilities = \is_array($this->permissions) ? $this->permissions : [];

        return array_values(array_filter(
            array_map(
                static fn ($ability) => \is_string($ability) ? trim($ability) : null,
                $abilities,
            ),
        ));
    }

    public function hasAbility(string $ability): bool
    {
        $abilities = $this->resolvedAbilities();

        return \in_array('*', $abilities, true) || \in_array($ability, $abilities, true);
    }

    /**
     * Resolve the rate limiter definition for this API key.
     */
    public function toRateLimit(): Limit
    {
        $maxAttempts = (int) ($this->resolveRateLimitValue([
            'max_attempts',
            'requests_per_minute',
            'per_minute',
            'limit',
        ]) ?? self::DEFAULT_MAX_ATTEMPTS);

        $decaySeconds = (int) ($this->resolveRateLimitValue([
            'decay_seconds',
            'period',
            'window',
        ]) ?? self::DEFAULT_DECAY_SECONDS);

        if ($decaySeconds <= 0) {
            $decaySeconds = self::DEFAULT_DECAY_SECONDS;
        }

        if ($maxAttempts <= 0) {
            $maxAttempts = self::DEFAULT_MAX_ATTEMPTS;
        }

        $decayMinutes = (int) max(1, (int) ceil($decaySeconds / 60));

        return Limit::perMinutes($decayMinutes, $maxAttempts);
    }

    /**
     * @param array<int, string> $keys
     */
    private function resolveRateLimitValue(array $keys): ?int
    {
        $limits = $this->rate_limits;

        if (! \is_array($limits)) {
            return null;
        }

        foreach ($keys as $key) {
            $value = Arr::get($limits, $key);

            if ($value !== null) {
                return (int) $value;
            }
        }

        return null;
    }
}
