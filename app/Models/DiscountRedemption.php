<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\StatusScope;
use App\Models\Scopes\UserOwnedScope;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * DiscountRedemption
 * 
 * Eloquent model representing the DiscountRedemption entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $table
 * @property mixed $fillable
 * @property string $translationModel
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountRedemption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountRedemption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountRedemption query()
 * @mixin \Eloquent
 */
#[ScopedBy([UserOwnedScope::class, StatusScope::class])]
final class DiscountRedemption extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;
    protected $table = 'discount_redemptions';
    protected $fillable = ['discount_id', 'code_id', 'order_id', 'user_id', 'amount_saved', 'currency_code', 'redeemed_at', 'metadata', 'status', 'notes', 'ip_address', 'user_agent', 'created_by', 'updated_by'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['amount_saved' => 'decimal:2', 'redeemed_at' => 'datetime', 'metadata' => 'array', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'deleted_at' => 'datetime'];
    }
    protected string $translationModel = \App\Models\Translations\DiscountRedemptionTranslation::class;
    /**
     * Handle discount functionality with proper error handling.
     * @return BelongsTo
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }
    /**
     * Handle code functionality with proper error handling.
     * @return BelongsTo
     */
    public function code(): BelongsTo
    {
        return $this->belongsTo(DiscountCode::class, 'code_id');
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
     * Handle scopeForDiscount functionality with proper error handling.
     * @param mixed $query
     * @param mixed $discountId
     */
    public function scopeForDiscount($query, $discountId)
    {
        return $query->where('discount_id', $discountId);
    }
    /**
     * Handle scopeForUser functionality with proper error handling.
     * @param mixed $query
     * @param mixed $userId
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
    /**
     * Handle scopeForOrder functionality with proper error handling.
     * @param mixed $query
     * @param mixed $orderId
     */
    public function scopeForOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }
    /**
     * Handle scopeWithinDateRange functionality with proper error handling.
     * @param mixed $query
     * @param mixed $startDate
     * @param mixed $endDate
     */
    public function scopeWithinDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('redeemed_at', [$startDate, $endDate]);
    }
    /**
     * Handle getTotalSavedForDiscount functionality with proper error handling.
     * @param mixed $discountId
     * @return float
     */
    public static function getTotalSavedForDiscount($discountId): float
    {
        return self::where('discount_id', $discountId)->sum('amount_saved');
    }
    /**
     * Handle getTotalSavedForUser functionality with proper error handling.
     * @param mixed $userId
     * @return float
     */
    public static function getTotalSavedForUser($userId): float
    {
        return self::where('user_id', $userId)->sum('amount_saved');
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
     * Handle scopeForCurrency functionality with proper error handling.
     * @param Builder $query
     * @param string $currencyCode
     * @return Builder
     */
    public function scopeForCurrency(Builder $query, string $currencyCode): Builder
    {
        return $query->where('currency_code', $currencyCode);
    }
    /**
     * Handle scopeRecent functionality with proper error handling.
     * @param Builder $query
     * @param int $days
     * @return Builder
     */
    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('redeemed_at', '>=', now()->subDays($days));
    }
    /**
     * Handle scopeAboveAmount functionality with proper error handling.
     * @param Builder $query
     * @param float $amount
     * @return Builder
     */
    public function scopeAboveAmount(Builder $query, float $amount): Builder
    {
        return $query->where('amount_saved', '>', $amount);
    }
    /**
     * Handle scopeBelowAmount functionality with proper error handling.
     * @param Builder $query
     * @param float $amount
     * @return Builder
     */
    public function scopeBelowAmount(Builder $query, float $amount): Builder
    {
        return $query->where('amount_saved', '<', $amount);
    }
    /**
     * Handle getTotalRedemptionsForDiscount functionality with proper error handling.
     * @param int $discountId
     * @return int
     */
    public static function getTotalRedemptionsForDiscount(int $discountId): int
    {
        return self::where('discount_id', $discountId)->count();
    }
    /**
     * Handle getTotalRedemptionsForUser functionality with proper error handling.
     * @param int $userId
     * @return int
     */
    public static function getTotalRedemptionsForUser(int $userId): int
    {
        return self::where('user_id', $userId)->count();
    }
    /**
     * Handle getAverageSavedForDiscount functionality with proper error handling.
     * @param int $discountId
     * @return float
     */
    public static function getAverageSavedForDiscount(int $discountId): float
    {
        return self::where('discount_id', $discountId)->avg('amount_saved') ?? 0.0;
    }
    /**
     * Handle getAverageSavedForUser functionality with proper error handling.
     * @param int $userId
     * @return float
     */
    public static function getAverageSavedForUser(int $userId): float
    {
        return self::where('user_id', $userId)->avg('amount_saved') ?? 0.0;
    }
    /**
     * Handle isRecent functionality with proper error handling.
     * @return bool
     */
    public function isRecent(): bool
    {
        return $this->redeemed_at && $this->redeemed_at->isAfter(now()->subDay());
    }
    /**
     * Handle getFormattedAmountSavedAttribute functionality with proper error handling.
     * @return string
     */
    public function getFormattedAmountSavedAttribute(): string
    {
        return number_format($this->amount_saved, 2) . ' ' . ($this->currency_code ?? 'EUR');
    }
    /**
     * Handle getStatusColorAttribute functionality with proper error handling.
     * @return string
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