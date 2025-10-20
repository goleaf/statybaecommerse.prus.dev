<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class ApiKey extends Model
{
    /**
     * @var list<string>
     */
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
     * @var list<string>
     */
    protected $appends = [
        'rate_limit',
    ];

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

    public static function generatePlainTextKey(): string
    {
        return Str::random(40);
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

        $value = Arr::get($rateLimits, 'default');

        return is_numeric($value) ? (int) $value : null;
    }

    public function setRateLimitAttribute(?int $value): void
    {
        if ($value === null) {
            $this->rate_limits = null;

            return;
        }

        $rateLimits = $this->rate_limits ?? [];
        $rateLimits['default'] = $value;

        $this->rate_limits = $rateLimits;
    }

    /**
     * @return BelongsTo<User, ApiKey>
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

        return Str::mask($key, '*', 4, -4);
    }

    /**
     * @return array{key: string, secret: string}
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
}
