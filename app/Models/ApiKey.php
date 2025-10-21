<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'key',
        'name',
        'scopes',
        'rate_limit',
        'active',
        'last_used_at',
    ];

    protected $casts = [
        'scopes' => 'array',
        'rate_limit' => 'integer',
        'active' => 'boolean',
        'last_used_at' => 'datetime',
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

        if ($scope === '*') {
            return true;
        }

        return in_array('*', $scopes, true) || in_array($scope, $scopes, true);
    }

    /**
     * Determine if the API key has any of the provided scopes.
     *
     * @param  array<int, string>  $scopes
     */
    public function hasAnyScope(array $scopes): bool
    {
        if ($scopes === []) {
            return true;
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
    public function withinRateLimit(int $requestedCalls = 1): bool
    {
        $limit = $this->rate_limit;

        if ($limit === null || $limit <= 0) {
            return true;
        }

        return $requestedCalls <= $limit;
    }

    /**
     * Determine if the API key has a finite rate limit configured.
     */
    public function hasRateLimit(): bool
    {
        $limit = $this->rate_limit;

        return $limit !== null && $limit > 0;
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
}
