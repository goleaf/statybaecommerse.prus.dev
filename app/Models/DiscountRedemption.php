<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class DiscountRedemption extends Model
{
    use HasFactory;

    protected $table = 'discount_redemptions';

    protected $fillable = [
        'discount_id',
        'code_id',
        'order_id',
        'user_id',
        'amount_saved',
        'currency_code',
        'redeemed_at',
        'metadata',
    ];

    protected $casts = [
        'amount_saved' => 'decimal:2',
        'redeemed_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the discount this redemption belongs to
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    /**
     * Get the discount code used for this redemption
     */
    public function code(): BelongsTo
    {
        return $this->belongsTo(DiscountCode::class, 'code_id');
    }

    /**
     * Get the user who redeemed this discount
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get redemptions for a specific discount
     */
    public function scopeForDiscount($query, $discountId)
    {
        return $query->where('discount_id', $discountId);
    }

    /**
     * Get redemptions for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get redemptions for a specific order
     */
    public function scopeForOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    /**
     * Get redemptions within a date range
     */
    public function scopeWithinDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('redeemed_at', [$startDate, $endDate]);
    }

    /**
     * Get total amount saved for a discount
     */
    public static function getTotalSavedForDiscount($discountId): float
    {
        return self::where('discount_id', $discountId)->sum('amount_saved');
    }

    /**
     * Get total amount saved for a user
     */
    public static function getTotalSavedForUser($userId): float
    {
        return self::where('user_id', $userId)->sum('amount_saved');
    }
}
