<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class DiscountRedemption extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

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
        'status',
        'notes',
        'ip_address',
        'user_agent',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'amount_saved' => 'decimal:2',
            'redeemed_at' => 'datetime',
            'metadata' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    protected string $translationModel = \App\Models\Translations\DiscountRedemptionTranslation::class;

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
     * Get the order this redemption belongs to
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user who created this redemption
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this redemption
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
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

    /**
     * Get redemptions by status
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Get redemptions for a specific currency
     */
    public function scopeForCurrency(Builder $query, string $currencyCode): Builder
    {
        return $query->where('currency_code', $currencyCode);
    }

    /**
     * Get recent redemptions
     */
    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('redeemed_at', '>=', now()->subDays($days));
    }

    /**
     * Get redemptions with amount above threshold
     */
    public function scopeAboveAmount(Builder $query, float $amount): Builder
    {
        return $query->where('amount_saved', '>', $amount);
    }

    /**
     * Get redemptions with amount below threshold
     */
    public function scopeBelowAmount(Builder $query, float $amount): Builder
    {
        return $query->where('amount_saved', '<', $amount);
    }

    /**
     * Get total redemptions count for a discount
     */
    public static function getTotalRedemptionsForDiscount(int $discountId): int
    {
        return self::where('discount_id', $discountId)->count();
    }

    /**
     * Get total redemptions count for a user
     */
    public static function getTotalRedemptionsForUser(int $userId): int
    {
        return self::where('user_id', $userId)->count();
    }

    /**
     * Get average amount saved for a discount
     */
    public static function getAverageSavedForDiscount(int $discountId): float
    {
        return self::where('discount_id', $discountId)->avg('amount_saved') ?? 0.0;
    }

    /**
     * Get average amount saved for a user
     */
    public static function getAverageSavedForUser(int $userId): float
    {
        return self::where('user_id', $userId)->avg('amount_saved') ?? 0.0;
    }

    /**
     * Check if redemption is recent (within last 24 hours)
     */
    public function isRecent(): bool
    {
        return $this->redeemed_at && $this->redeemed_at->isAfter(now()->subDay());
    }

    /**
     * Get formatted amount saved with currency
     */
    public function getFormattedAmountSavedAttribute(): string
    {
        return number_format($this->amount_saved, 2).' '.($this->currency_code ?? 'EUR');
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'success',
            'pending' => 'warning',
            'cancelled' => 'danger',
            'refunded' => 'info',
            default => 'secondary',
        };
    }
}
