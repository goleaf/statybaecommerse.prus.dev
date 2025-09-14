<?php

declare (strict_types=1);
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
/**
 * DiscountCode
 * 
 * Eloquent model representing the DiscountCode entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $table
 * @property mixed $fillable
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCode query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, DateRangeScope::class, StatusScope::class])]
final class DiscountCode extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'discount_codes';
    protected $fillable = ['discount_id', 'code', 'description_lt', 'description_en', 'starts_at', 'expires_at', 'usage_limit', 'usage_limit_per_user', 'usage_count', 'is_active', 'status', 'metadata', 'created_by', 'updated_by'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'expires_at' => 'datetime', 'usage_limit' => 'integer', 'usage_limit_per_user' => 'integer', 'usage_count' => 'integer', 'is_active' => 'boolean', 'metadata' => 'array'];
    }
    /**
     * Handle discount functionality with proper error handling.
     * @return BelongsTo
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }
    /**
     * Handle redemptions functionality with proper error handling.
     * @return HasMany
     */
    public function redemptions(): HasMany
    {
        return $this->hasMany(DiscountRedemption::class, 'code_id');
    }
    /**
     * Handle creator functionality with proper error handling.
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    /**
     * Handle updater functionality with proper error handling.
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    /**
     * Handle documents functionality with proper error handling.
     * @return MorphMany
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
    /**
     * Handle orders functionality with proper error handling.
     * @return BelongsToMany
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'discount_redemptions', 'code_id', 'order_id');
    }
    /**
     * Handle users functionality with proper error handling.
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'discount_redemptions', 'code_id', 'user_id');
    }
    /**
     * Handle scopeActive functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->where(function ($q) {
            $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
        })->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
        });
    }
    /**
     * Handle scopeExpired functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<', now());
    }
    /**
     * Handle scopeScheduled functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('starts_at', '>', now());
    }
    /**
     * Handle scopeUsageLimitReached functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeUsageLimitReached(Builder $query): Builder
    {
        return $query->whereColumn('usage_count', '>=', 'usage_limit');
    }
    /**
     * Handle scopeByStatus functionality with proper error handling.
     * @param Builder $query
     * @param string $status
     * @return Builder
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }
    /**
     * Handle scopeCreatedBy functionality with proper error handling.
     * @param Builder $query
     * @param int $userId
     * @return Builder
     */
    public function scopeCreatedBy(Builder $query, int $userId): Builder
    {
        return $query->where('created_by', $userId);
    }
    /**
     * Handle hasReachedLimit functionality with proper error handling.
     * @return bool
     */
    public function hasReachedLimit(): bool
    {
        if ($this->usage_limit !== null) {
            return $this->usage_count >= $this->usage_limit;
        }
        return false;
    }
    /**
     * Handle isValid functionality with proper error handling.
     * @return bool
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
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
     * Handle incrementUsage functionality with proper error handling.
     * @return void
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
    /**
     * Handle generateUniqueCode functionality with proper error handling.
     * @param int $length
     * @return string
     */
    public static function generateUniqueCode(int $length = 8): string
    {
        do {
            $code = strtoupper(str()->random($length));
        } while (self::where('code', $code)->exists());
        return $code;
    }
    /**
     * Handle getDescriptionAttribute functionality with proper error handling.
     * @return string
     */
    public function getDescriptionAttribute(): string
    {
        $locale = app()->getLocale();
        return $this->{"description_{$locale}"} ?? $this->description_lt ?? '';
    }
    /**
     * Handle getStatusColorAttribute functionality with proper error handling.
     * @return string
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
     * Handle getUsagePercentageAttribute functionality with proper error handling.
     * @return float
     */
    public function getUsagePercentageAttribute(): float
    {
        if ($this->usage_limit === null || $this->usage_limit === 0) {
            return 0;
        }
        return $this->usage_count / $this->usage_limit * 100;
    }
    /**
     * Handle isExpiringSoon functionality with proper error handling.
     * @return bool
     */
    public function isExpiringSoon(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        return $this->expires_at->diffInDays(now()) <= 7;
    }
    /**
     * Handle getRemainingUsesAttribute functionality with proper error handling.
     * @return int|null
     */
    public function getRemainingUsesAttribute(): ?int
    {
        if ($this->usage_limit === null) {
            return null;
        }
        return max(0, $this->usage_limit - $this->usage_count);
    }
    /**
     * Boot the service provider or trait functionality.
     * @return void
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