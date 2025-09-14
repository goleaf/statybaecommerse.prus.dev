<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\DateRangeScope;
use App\Models\Scopes\StatusScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([ActiveScope::class, DateRangeScope::class, StatusScope::class])]
final /**
 * DiscountCode
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class DiscountCode extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'discount_codes';

    protected $fillable = [
        'discount_id',
        'code',
        'description_lt',
        'description_en',
        'starts_at',
        'expires_at',
        'usage_limit',
        'usage_limit_per_user',
        'usage_count',
        'is_active',
        'status',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'usage_limit' => 'integer',
            'usage_limit_per_user' => 'integer',
            'usage_count' => 'integer',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

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
     * Get the user who created this code
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this code
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get documents related to this discount code
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Get orders that used this discount code
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'discount_redemptions', 'code_id', 'order_id');
    }

    /**
     * Get users who have used this discount code
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'discount_redemptions', 'code_id', 'user_id');
    }

    /**
     * Scope to get active codes
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
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
     * Scope to get scheduled codes (not yet started)
     */
    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('starts_at', '>', now());
    }

    /**
     * Scope to get codes with usage limit reached
     */
    public function scopeUsageLimitReached(Builder $query): Builder
    {
        return $query->whereColumn('usage_count', '>=', 'usage_limit');
    }

    /**
     * Scope to get codes by status
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get codes created by user
     */
    public function scopeCreatedBy(Builder $query, int $userId): Builder
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Check if code has reached its usage limit
     */
    public function hasReachedLimit(): bool
    {
        if ($this->usage_limit !== null) {
            return $this->usage_count >= $this->usage_limit;
        }

        return false;
    }

    /**
     * Check if code is currently valid
     */
    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->hasReachedLimit()) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $this->starts_at->gt($now)) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->lt($now)) {
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

    /**
     * Get description in current locale
     */
    public function getDescriptionAttribute(): string
    {
        $locale = app()->getLocale();

        return $this->{"description_{$locale}"} ?? $this->description_lt ?? '';
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'success',
            'inactive' => 'gray',
            'expired' => 'danger',
            'scheduled' => 'warning',
            default => 'gray',
        };
    }

    /**
     * Get usage percentage
     */
    public function getUsagePercentageAttribute(): float
    {
        if ($this->usage_limit === null || $this->usage_limit === 0) {
            return 0;
        }

        return ($this->usage_count / $this->usage_limit) * 100;
    }

    /**
     * Check if code is expiring soon (within 7 days)
     */
    public function isExpiringSoon(): bool
    {
        if (! $this->expires_at) {
            return false;
        }

        return $this->expires_at->diffInDays(now()) <= 7;
    }

    /**
     * Get remaining uses
     */
    public function getRemainingUsesAttribute(): ?int
    {
        if ($this->usage_limit === null) {
            return null;
        }

        return max(0, $this->usage_limit - $this->usage_count);
    }

    /**
     * Boot method to set created_by and updated_by
     */
    protected static function boot(): void
    {
        parent::boot();

        self::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });

        self::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }
}
