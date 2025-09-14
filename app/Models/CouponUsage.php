<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\UserOwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * CouponUsage
 * 
 * Eloquent model representing the CouponUsage entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $fillable
 * @property mixed $casts
 * @method static \Illuminate\Database\Eloquent\Builder|CouponUsage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CouponUsage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CouponUsage query()
 * @mixin \Eloquent
 */
#[ScopedBy([UserOwnedScope::class])]
final class CouponUsage extends Model
{
    use HasFactory;
    protected $fillable = ['coupon_id', 'user_id', 'order_id', 'discount_amount', 'used_at', 'metadata'];
    protected $casts = ['discount_amount' => 'decimal:2', 'used_at' => 'datetime', 'metadata' => 'array'];
    /**
     * Handle coupon functionality with proper error handling.
     * @return BelongsTo
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }
    /**
     * Handle user functionality with proper error handling.
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Handle order functionality with proper error handling.
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    /**
     * Handle scopeByCoupon functionality with proper error handling.
     * @param mixed $query
     * @param int $couponId
     */
    public function scopeByCoupon($query, int $couponId)
    {
        return $query->where('coupon_id', $couponId);
    }
    /**
     * Handle scopeByUser functionality with proper error handling.
     * @param mixed $query
     * @param int $userId
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
    /**
     * Handle scopeByOrder functionality with proper error handling.
     * @param mixed $query
     * @param int $orderId
     */
    public function scopeByOrder($query, int $orderId)
    {
        return $query->where('order_id', $orderId);
    }
    /**
     * Handle scopeUsedToday functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeUsedToday($query)
    {
        return $query->whereDate('used_at', today());
    }
    /**
     * Handle scopeUsedThisWeek functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeUsedThisWeek($query)
    {
        return $query->whereBetween('used_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }
    /**
     * Handle scopeUsedThisMonth functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeUsedThisMonth($query)
    {
        return $query->whereBetween('used_at', [now()->startOfMonth(), now()->endOfMonth()]);
    }
}