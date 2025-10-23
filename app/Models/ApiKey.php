<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class ApiKey extends Model
{
    use HasFactory;

    public const KEY_LENGTH = 56;

    public const KEY_PREFIX = 'sk';

    protected $table = 'api_keys';

    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'key',
        'secret',
        'permissions',
        'rate_limits',
        'rate_limit',
        'user_id',
        'last_used_at',
        'expires_at',
        'is_active',
    ];

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
     * Generate a hashed API key alongside the plain text representation.
     *
     * @return array{plain_text: string, hashed: string}
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
     * Generate a plain text API key that can be shared with the consumer.
     */
    public static function generatePlainTextKey(?string $prefix = null): string
    {
        $prefix ??= self::KEY_PREFIX;
        $random = Str::upper(Str::random(self::KEY_LENGTH));

        return sprintf('%s_%s', $prefix, $random);
    }

    /**
     * Hash a plain text key for storage in the database.
     */
    public static function hashKey(string $plainText): string
    {
        return hash('sha256', $plainText);
    }

    /**
     * Build credential payload for an existing plain text key.
     *
     * @return array{plain_text: string, hashed: string}
     */
    public static function credentialsFromPlainText(string $plainText): array
    {
        return [
            'plain_text' => $plainText,
            'hashed' => self::hashKey($plainText),
        ];
    }

    /**
     * Normalize a rate limit value coming from user input.
     */
    public static function normalizeRateLimit(int|string|null $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $limit = (int) $value;

        return $limit > 0 ? $limit : null;
    }

    /**
     * Retrieve the normalized rate limit label for display.
     */
    public function formattedRateLimit(): string
    {
        $limit = $this->rate_limit;

        return $limit === null || $limit <= 0
            ? __('api_keys.rate_limit.unlimited')
            : (string) $limit;
    }

    /**
     * Determine if the API key has the given scope.
     */
    public function hasScope(string $scope): bool
    {
        $scopes = $this->resolvedScopes();

    /**
     * @var list<string>
     */
    protected $hidden = [
        'secret',
    ];

    public static function booted(): void
    {
        self::creating(static function (self $apiKey): void {
            if (blank($apiKey->key)) {
                $apiKey->key = self::generatePlainTextKey();
            }

            if (blank($apiKey->secret)) {
                $apiKey->secret = self::generatePlainTextSecret();
            }
        });
    }
    public static function generatePlainTextSecret(): string
    {
        return Str::random(64);
    }

    public function getRateLimitAttribute(): ?int
    {
        $rateLimits = $this->rate_limits;

        if ($rateLimits === null) {
            return null;
        }

        return in_array('*', $scopes, true) || in_array($scope, $scopes, true);
    }

    /**
     * Determine if the API key has any of the provided scopes.
     *
     * @param  array<int, string>  $scopes
     */
    public function user(): BelongsTo
    {
        /** @var BelongsTo<User, ApiKey> $relation */
        $relation = $this->belongsTo(User::class);

        return $relation;
    }

    public function maskKey(): string
    {
        $key = (string) $this->key;

        if (strlen($key) <= 8) {
            return Str::mask($key, '*', 0);
        }

        $assignedScopes = $this->resolvedScopes();

        if (in_array('*', $assignedScopes, true)) {
            return true;
        }

        return array_intersect($assignedScopes, $scopes) !== [];
    }

    /**
     * Retrieve the sanitised scopes assigned to the API key.
     *
     * @return array<int, string>
     */
    public function resolvedScopes(): array
    {
        return Collection::make(Arr::wrap($this->scopes))
            ->filter(static fn ($scope): bool => is_string($scope) && $scope !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Determine if the API key meets its configured rate limit for the given request count.
     */
    public function regenerateCredentials(): array
    {
        $plainKey = self::generatePlainTextKey();
        $plainSecret = self::generatePlainTextSecret();

        $this->forceFill([
            'key' => $plainKey,
            'secret' => $plainSecret,
        ])->save();

        return [
            'key' => $plainKey,
            'secret' => $plainSecret,
        ];
    }

    /**
     * Retrieve the scopes as a collection for easier handling in Filament.
     *
     * @return Collection<int, string>
     */
    public function scopesAsCollection(): Collection
    {
        return Collection::make(Arr::wrap($this->scopes))->filter()->values();
    }

    /**
     * Retrieve the scopes as a collection for easier handling in Filament.
     *
     * @return Collection<int, string>
     */
    /**
     * Build a unique cache key for partner API rate limiting.
     */
    public function rateLimiterKey(): string
    {
        return 'partner_api:key:'.$this->getKey();
    }
}
