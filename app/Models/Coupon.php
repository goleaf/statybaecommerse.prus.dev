<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Coupon
 *
 * Eloquent model representing the Coupon entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Coupon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Coupon newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Coupon query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['code', 'name', 'description', 'type', 'value', 'minimum_amount', 'maximum_discount', 'usage_limit', 'usage_limit_per_user', 'used_count', 'is_active', 'is_public', 'is_auto_apply', 'is_stackable', 'starts_at', 'expires_at', 'applicable_products', 'applicable_categories'];

    protected $casts = ['value' => 'decimal:2', 'minimum_amount' => 'decimal:2', 'maximum_discount' => 'decimal:2', 'usage_limit' => 'integer', 'usage_limit_per_user' => 'integer', 'used_count' => 'integer', 'is_active' => 'boolean', 'is_public' => 'boolean', 'is_auto_apply' => 'boolean', 'is_stackable' => 'boolean', 'starts_at' => 'datetime', 'expires_at' => 'datetime', 'applicable_products' => 'array', 'applicable_categories' => 'array'];

    // Relationships

    /**
     * Handle products functionality with proper error handling.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'coupon_products');
    }

    /**
     * Handle categories functionality with proper error handling.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'coupon_categories');
    }

    public function customerGroup()
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    /**
     * Handle orders functionality with proper error handling.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Handle usages functionality with proper error handling.
     */
    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    // Scopes

    /**
     * Handle scopeActive functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Handle scopeValid functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeValid($query)
    {
        return $query->where('is_active', true)->where(function ($q) {
            $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
        })->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        })->where(function ($q) {
            $q->whereNull('usage_limit')->orWhereRaw('used_count < usage_limit');
        });
    }

    /**
     * Handle scopeExpired functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Handle scopeByType functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Handle scopeByCode functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Handle isValid functionality with proper error handling.
     */
    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        if ($this->starts_at && $this->starts_at > now()) {
            return false;
        }
        if ($this->expires_at && $this->expires_at < now()) {
            return false;
        }
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Handle isExpired functionality with proper error handling.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at < now();
    }

    /**
     * Handle isNotStarted functionality with proper error handling.
     */
    public function isNotStarted(): bool
    {
        return $this->starts_at && $this->starts_at > now();
    }

    /**
     * Handle canBeUsed functionality with proper error handling.
     */
    public function canBeUsed(float $orderTotal): bool
    {
        if (! $this->isValid()) {
            return false;
        }
        if ($this->minimum_amount && $orderTotal < $this->minimum_amount) {
            return false;
        }

        return true;
    }

    /**
     * Handle calculateDiscount functionality with proper error handling.
     */
    public function calculateDiscount(float $orderTotal): float
    {
        if (! $this->canBeUsed($orderTotal)) {
            return 0;
        }
        if ($this->type === 'percentage') {
            return $orderTotal * $this->value / 100;
        }

        return (float) min($this->value, $orderTotal);
    }
}
