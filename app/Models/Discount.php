<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * Discount
 * 
 * Eloquent model representing the Discount entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $table
 * @property mixed $fillable
 * @method static \Illuminate\Database\Eloquent\Builder|Discount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Discount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Discount query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, EnabledScope::class])]
final class Discount extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'discounts';
    protected $fillable = ['name', 'slug', 'description', 'type', 'value', 'is_active', 'is_enabled', 'starts_at', 'ends_at', 'usage_limit', 'usage_count', 'minimum_amount', 'maximum_amount', 'zone_id', 'status', 'scope', 'stacking_policy', 'metadata', 'priority', 'exclusive', 'applies_to_shipping', 'free_shipping', 'first_order_only', 'per_customer_limit', 'per_code_limit', 'per_day_limit', 'channel_restrictions', 'currency_restrictions', 'weekday_mask', 'time_window'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['value' => 'float', 'minimum_amount' => 'decimal:2', 'maximum_amount' => 'decimal:2', 'starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_active' => 'boolean', 'is_enabled' => 'boolean', 'scope' => 'array', 'metadata' => 'array', 'exclusive' => 'boolean', 'applies_to_shipping' => 'boolean', 'free_shipping' => 'boolean', 'first_order_only' => 'boolean', 'channel_restrictions' => 'array', 'currency_restrictions' => 'array', 'time_window' => 'array', 'usage_limit' => 'integer', 'usage_count' => 'integer', 'per_customer_limit' => 'integer', 'per_code_limit' => 'integer', 'per_day_limit' => 'integer', 'priority' => 'integer'];
    }
    /**
     * Handle scopeActive functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')->where(function ($q) {
            $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
        })->where(function ($q) {
            $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
        });
    }
    /**
     * Handle scopeScheduled functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled');
    }
    /**
     * Handle scopeExpired functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', 'expired')->orWhere(function ($q) {
            $q->whereNotNull('ends_at')->where('ends_at', '<', now());
        });
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
        if ($this->status !== 'active') {
            return false;
        }
        if ($this->hasReachedLimit()) {
            return false;
        }
        $now = now();
        if ($this->starts_at && $this->starts_at->gt($now)) {
            return false;
        }
        if ($this->ends_at && $this->ends_at->lt($now)) {
            return false;
        }
        return true;
    }
    /**
     * Handle conditions functionality with proper error handling.
     * @return HasMany
     */
    public function conditions(): HasMany
    {
        return $this->hasMany(DiscountCondition::class);
    }
    /**
     * Handle codes functionality with proper error handling.
     * @return HasMany
     */
    public function codes(): HasMany
    {
        return $this->hasMany(DiscountCode::class);
    }
    /**
     * Handle redemptions functionality with proper error handling.
     * @return HasMany
     */
    public function redemptions(): HasMany
    {
        return $this->hasMany(DiscountRedemption::class);
    }
    /**
     * Handle zone functionality with proper error handling.
     * @return BelongsTo
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
    /**
     * Handle brands functionality with proper error handling.
     * @return BelongsToMany
     */
    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'discount_brands');
    }
    /**
     * Handle categories functionality with proper error handling.
     * @return BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'discount_categories');
    }
    /**
     * Handle collections functionality with proper error handling.
     * @return BelongsToMany
     */
    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'discount_collections');
    }
    /**
     * Handle customers functionality with proper error handling.
     * @return BelongsToMany
     */
    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'discount_customers', 'discount_id', 'user_id');
    }
    /**
     * Handle zones functionality with proper error handling.
     * @return BelongsToMany
     */
    public function zones(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'discount_zones');
    }
    /**
     * Handle campaigns functionality with proper error handling.
     * @return BelongsToMany
     */
    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campaign::class, 'campaign_discount');
    }
    /**
     * Handle products functionality with proper error handling.
     * @return BelongsToMany
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'discount_products');
    }
    /**
     * Handle customerGroups functionality with proper error handling.
     * @return BelongsToMany
     */
    public function customerGroups(): BelongsToMany
    {
        return $this->belongsToMany(CustomerGroup::class, 'discount_customer_groups');
    }
    /**
     * Handle scopeByType functionality with proper error handling.
     * @param Builder $query
     * @param string $type
     * @return Builder
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }
    /**
     * Handle scopeExclusive functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeExclusive(Builder $query): Builder
    {
        return $query->where('exclusive', true);
    }
    /**
     * Handle scopeStackable functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeStackable(Builder $query): Builder
    {
        return $query->where('exclusive', false);
    }
    /**
     * Handle scopeByPriority functionality with proper error handling.
     * @param Builder $query
     * @param string $direction
     * @return Builder
     */
    public function scopeByPriority(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('priority', $direction);
    }
    /**
     * Handle getDiscountAmountAttribute functionality with proper error handling.
     * @return float
     */
    public function getDiscountAmountAttribute(): float
    {
        return match ($this->type) {
            'percentage' => $this->value,
            'fixed' => $this->value,
            'free_shipping' => 0,
            default => 0,
        };
    }
    /**
     * Handle isExclusive functionality with proper error handling.
     * @return bool
     */
    public function isExclusive(): bool
    {
        return (bool) $this->exclusive;
    }
    /**
     * Handle canStackWith functionality with proper error handling.
     * @param Discount $other
     * @return bool
     */
    public function canStackWith(Discount $other): bool
    {
        return !$this->isExclusive() && !$other->isExclusive();
    }
    /**
     * Handle isCurrentlyActive functionality with proper error handling.
     * @return bool
     */
    public function isCurrentlyActive(): bool
    {
        if (!(bool) ($this->is_active ?? false)) {
            return false;
        }
        $now = now();
        if ($this->starts_at && $this->starts_at->gt($now)) {
            return false;
        }
        if ($this->ends_at && $this->ends_at->lt($now)) {
            return false;
        }
        return true;
    }
    /**
     * Handle calculateDiscountAmount functionality with proper error handling.
     * @param float $amount
     * @return float
     */
    public function calculateDiscountAmount(float $amount): float
    {
        return match ($this->type) {
            'percentage' => round($amount * (float) $this->value / 100, 2),
            'fixed' => (float) min((float) $this->value, $amount),
            default => 0.0,
        };
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
     * Handle isUsageLimitReached functionality with proper error handling.
     * @return bool
     */
    public function isUsageLimitReached(): bool
    {
        if ($this->usage_limit === null) {
            return false;
        }
        return (int) $this->usage_count >= (int) $this->usage_limit;
    }
}