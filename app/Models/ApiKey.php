<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class ApiKey extends Model
{
    use HasFactory;

    public const KEY_PREFIX = 'sk';
    public const KEY_LENGTH = 56;

    protected $table = 'api_keys';

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'scopes' => 'array',
        'permissions' => 'array',
        'rate_limits' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'secret',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
    ];

    protected static function booted(): void
    {
        static::creating(static function (ApiKey $apiKey): void {
            if (! isset($apiKey->attributes['key']) || $apiKey->attributes['key'] === '') {
                $credentials = self::generateCredentials();
                $apiKey->key = $credentials['hashed'];
            }

            if (! isset($apiKey->attributes['secret']) || $apiKey->attributes['secret'] === '') {
                $apiKey->secret = self::generatePlainTextSecret();
            }
        });
    }

    /**
     * Provide backwards-compatible access to the legacy "active" attribute.
     */
    protected function active(): Attribute
    {
        return Attribute::make(
            get: fn (?bool $value, array $attributes): bool => $value ?? (bool) ($attributes['is_active'] ?? true),
            set: fn (mixed $value): array => ['is_active' => (bool) $value],
        );
    }

    /**
     * Generate the plain text and hashed pair used when provisioning API keys.
     *
     * @return array{plain_text:string, hashed:string}
     */
    public static function generateCredentials(?string $prefix = null): array
    {
        $plainText = self::generatePlainTextKey($prefix);

        return [
            'plain_text' => $plainText,
            'hashed' => self::hashKey($plainText),
        ];
    }

    /**
     * Generate a new API key string suitable for sharing with integrators.
     */
    public static function generatePlainTextKey(?string $prefix = null): string
    {
        $resolvedPrefix = $prefix !== null && $prefix !== '' ? $prefix : self::KEY_PREFIX;

        return sprintf('%s_%s', $resolvedPrefix, Str::upper(Str::random(self::KEY_LENGTH)));
    }

    /**
     * Create a new secret token that can be paired with an API key.
     */
    public static function generatePlainTextSecret(): string
    {
        return Str::random(64);
    }

    /**
     * Hash a plain text key for persistent storage.
     */
    public static function hashKey(string $plainText): string
    {
        return hash('sha256', $plainText);
    }

    /**
     * Rehydrate credentials for a known plain text key.
     *
     * @return array{plain_text:string, hashed:string}
     */
    public static function credentialsFromPlainText(string $plainText): array
    {
        return [
            'plain_text' => $plainText,
            'hashed' => self::hashKey($plainText),
        ];
    }

    /**
     * Normalise values captured from form inputs or APIs into a nullable integer.
     */
    public static function normalizeRateLimit(int|string|null $value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value) && trim($value) === '') {
            return null;
        }

        $limit = (int) $value;

        return $limit > 0 ? $limit : null;
    }

    /**
     * Present the rate limit as a human-readable label.
     */
    public function formattedRateLimit(): string
    {
        $limit = $this->rate_limit;

        return $limit === null
            ? (string) __('api_keys.rate_limit.unlimited')
            : (string) $limit;
    }

    /**
     * Determine whether the API key grants the provided scope.
     */
    public function hasScope(string $scope): bool
    {
        return in_array('*', $this->resolvedScopes(), true)
            || in_array($scope, $this->resolvedScopes(), true);
    }

    /**
     * Determine whether the API key grants any of the requested scopes.
     *
     * @param  array<int, string>  $scopes
     */
    public function hasAnyScope(array $scopes): bool
    {
        if ($scopes === []) {
            return true;
        }

        $resolved = $this->resolvedScopes();

        if (in_array('*', $resolved, true)) {
            return true;
        }

        foreach ($scopes as $candidate) {
            if (in_array($candidate, $resolved, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve the sanitised list of scopes assigned to the API key.
     *
     * @return array<int, string>
     */
    public function resolvedScopes(): array
    {
        return Collection::make(
            Arr::wrap($this->scopes ?? $this->permissions ?? [])
        )
            ->filter(static fn ($scope): bool => is_string($scope) && $scope !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Retrieve the scopes as a collection for convenient chaining.
     *
     * @return Collection<int, string>
     */
    public function scopesAsCollection(): Collection
    {
        return Collection::make($this->resolvedScopes());
    }

    /**
     * Resolve the effective rate limit, falling back to aggregated metadata.
     */
    protected function rateLimit(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value): ?int {
                $normalized = self::normalizeRateLimit($value);

                if ($normalized !== null) {
                    return $normalized;
                }

                if (! is_array($this->rate_limits)) {
                    return null;
                }

                $fallback = $this->rate_limits['*'] ?? Arr::get($this->rate_limits, 'global');

                return self::normalizeRateLimit($fallback);
            }
        );
    }

    /**
     * Build the partner API rate limiter key for this credential.
     */
    public function rateLimiterKey(): string
    {
        return sprintf('partner_api:key:%s', $this->getKey());
    }

    /**
     * Rotate credentials for an existing key, returning the newly generated values.
     *
     * @return array{plain_text_key:string, plain_text_secret:string}
     */
    public function regenerateCredentials(): array
    {
        $credentials = self::generateCredentials();
        $secret = self::generatePlainTextSecret();

        $this->forceFill([
            'key' => $credentials['hashed'],
            'secret' => $secret,
            'last_used_at' => null,
        ])->save();

        return [
            'plain_text_key' => $credentials['plain_text'],
            'plain_text_secret' => $secret,
        ];
    }

    /**
     * Mask the hashed key for display within administrative tooling.
     */
    public function maskKey(int $visible = 4): string
    {
        $key = (string) $this->key;

        if ($key === '') {
            return '';
        }

        $visible = max(0, $visible);

        if ($visible === 0) {
            return str_repeat('*', strlen($key));
        }

        $prefix = substr($key, 0, $visible);
        $suffix = substr($key, -$visible);
        $maskedLength = max(0, strlen($key) - ($visible * 2));

        return sprintf('%s%s%s', $prefix, str_repeat('*', $maskedLength), $suffix);
    }

    /**
     * Relationship to the owning user when applicable.
     *
     * @return BelongsTo<User, ApiKey>
     */
    public function user(): BelongsTo
    {
        /** @var BelongsTo<User, ApiKey> $relation */
        $relation = $this->belongsTo(User::class);

        return $relation;
    }
}
