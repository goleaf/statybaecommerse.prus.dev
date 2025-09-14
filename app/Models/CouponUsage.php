<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\UserOwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy([UserOwnedScope::class])]
final /**
 * CouponUsage
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class CouponUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'coupon_id',
        'user_id',
        'order_id',
        'discount_amount',
        'used_at',
        'metadata',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'used_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeByCoupon($query, int $couponId)
    {
        return $query->where('coupon_id', $couponId);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByOrder($query, int $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    public function scopeUsedToday($query)
    {
        return $query->whereDate('used_at', today());
    }

    public function scopeUsedThisWeek($query)
    {
        return $query->whereBetween('used_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeUsedThisMonth($query)
    {
        return $query->whereBetween('used_at', [now()->startOfMonth(), now()->endOfMonth()]);
    }
}
