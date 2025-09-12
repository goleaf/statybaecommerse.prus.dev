<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Discount extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'discounts';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'value',
        'is_active',
        'is_enabled',
        'starts_at',
        'ends_at',
        'usage_limit',
        'usage_count',
        'minimum_amount',
        'maximum_amount',
        'zone_id',
        'status',
        'scope',
        'stacking_policy',
        'metadata',
        'priority',
        'exclusive',
        'applies_to_shipping',
        'free_shipping',
        'first_order_only',
        'per_customer_limit',
        'per_code_limit',
        'per_day_limit',
        'channel_restrictions',
        'currency_restrictions',
        'weekday_mask',
        'time_window',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'float',
            'minimum_amount' => 'decimal:2',
            'maximum_amount' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
            'is_enabled' => 'boolean',
            'scope' => 'array',
            'metadata' => 'array',
            'exclusive' => 'boolean',
            'applies_to_shipping' => 'boolean',
            'free_shipping' => 'boolean',
            'first_order_only' => 'boolean',
            'channel_restrictions' => 'array',
            'currency_restrictions' => 'array',
            'time_window' => 'array',
            'usage_limit' => 'integer',
            'usage_count' => 'integer',
            'per_customer_limit' => 'integer',
            'per_code_limit' => 'integer',
            'per_day_limit' => 'integer',
            'priority' => 'integer',
        ];
    }

    /**
     * Scope to get active discounts
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', 'active')
            ->where(function ($q) {
                $q
                    ->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q
                    ->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }

    /**
     * Scope to get scheduled discounts
     */
    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope to get expired discounts
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query
            ->where('status', 'expired')
            ->orWhere(function ($q) {
                $q
                    ->whereNotNull('ends_at')
                    ->where('ends_at', '<', now());
            });
    }

    /**
     * Check if discount has reached its usage limit
     */
    public function hasReachedLimit(): bool
    {
        if ($this->usage_limit !== null) {
            return $this->usage_count >= $this->usage_limit;
        }

        return false;
    }

    /**
     * Check if discount is currently valid
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
     * Get discount conditions
     */
    public function conditions(): HasMany
    {
        return $this->hasMany(DiscountCondition::class);
    }

    /**
     * Get discount codes
     */
    public function codes(): HasMany
    {
        return $this->hasMany(DiscountCode::class);
    }

    /**
     * Get discount redemptions
     */
    public function redemptions(): HasMany
    {
        return $this->hasMany(DiscountRedemption::class);
    }

    /**
     * Get the zone this discount belongs to
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'discount_brands');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'discount_categories');
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'discount_collections');
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'discount_customers', 'discount_id', 'user_id');
    }

    public function zones(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'discount_zones');
    }

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campaign::class, 'campaign_discount');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'discount_products');
    }

    public function customerGroups(): BelongsToMany
    {
        return $this->belongsToMany(CustomerGroup::class, 'discount_customer_groups');
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeExclusive(Builder $query): Builder
    {
        return $query->where('exclusive', true);
    }

    public function scopeStackable(Builder $query): Builder
    {
        return $query->where('exclusive', false);
    }

    public function scopeByPriority(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('priority', $direction);
    }

    public function getDiscountAmountAttribute(): float
    {
        return match ($this->type) {
            'percentage' => $this->value,
            'fixed' => $this->value,
            'free_shipping' => 0,
            default => 0,
        };
    }

    public function isExclusive(): bool
    {
        return (bool) $this->exclusive;
    }

    public function canStackWith(Discount $other): bool
    {
        return !$this->isExclusive() && !$other->isExclusive();
    }

    /**
     * Check if the discount is currently active based on is_active and date window.
     */
    public function isCurrentlyActive(): bool
    {
        if (! (bool) ($this->is_active ?? false)) {
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
     * Calculate discount amount for a given subtotal/order total.
     */
    public function calculateDiscountAmount(float $amount): float
    {
        return match ($this->type) {
            'percentage' => round(($amount * ((float) $this->value)) / 100, 2),
            'fixed' => (float) min((float) $this->value, $amount),
            default => 0.0,
        };
    }

    /**
     * Increase usage_count by 1 and persist.
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Whether usage limit has been reached.
     */
    public function isUsageLimitReached(): bool
    {
        if ($this->usage_limit === null) {
            return false;
        }
        return (int) $this->usage_count >= (int) $this->usage_limit;
    }
}
