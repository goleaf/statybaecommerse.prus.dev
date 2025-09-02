<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DiscountCode extends Model
{
    use HasFactory;

    protected $table = 'discount_codes';

    protected $fillable = [
        'discount_id',
        'code',
        'expires_at',
        'max_uses',
        'usage_count',
        'metadata',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'max_uses' => 'integer',
        'usage_count' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the discount this code belongs to
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    /**
     * Get redemptions for this code
     */
    public function redemptions(): HasMany
    {
        return $this->hasMany(DiscountRedemption::class, 'code_id');
    }

    /**
     * Scope to get active codes
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>=', now());
        });
    }

    /**
     * Scope to get expired codes
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Check if code has reached its usage limit
     */
    public function hasReachedLimit(): bool
    {
        if ($this->max_uses !== null) {
            return $this->usage_count >= $this->max_uses;
        }

        return false;
    }

    /**
     * Check if code is currently valid
     */
    public function isValid(): bool
    {
        if ($this->hasReachedLimit()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->lt(now())) {
            return false;
        }

        return true;
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Generate a unique code
     */
    public static function generateUniqueCode(int $length = 8): string
    {
        do {
            $code = strtoupper(str()->random($length));
        } while (self::where('code', $code)->exists());

        return $code;
    }
}
