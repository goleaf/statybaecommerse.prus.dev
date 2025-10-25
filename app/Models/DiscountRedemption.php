<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\StatusScope;
use App\Models\Scopes\UserOwnedScope;
use App\Traits\HasTranslations;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * DiscountRedemption encapsulates the lifecycle of a redeemed discount code and the
 * relationships it maintains with discounts, codes, orders, and users.
 */
#[ScopedBy([UserOwnedScope::class, StatusScope::class])]
final class DiscountRedemption extends Model
{
    use HasFactory;
    use HasTranslations;
    use SoftDeletes;

    /**
     * @var string Explicitly define the backing table so refactors remain safe.
     */
    protected $table = 'discount_redemptions';

    /**
     * @var array<int, string> Mass-assignment whitelist for safe data hydration.
     */
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
        'created_by_name',
        'updated_by_name',
    ];

    /**
     * @var class-string Link the HasTranslations trait with the translation model.
     */
    protected string $translationModel = \App\Models\Translations\DiscountRedemptionTranslation::class;

    /**
     * Cast persisted attributes to keep business logic predictable.
     */
    protected function casts(): array
    {
        return [
            'amount_saved' => 'decimal:2',
            'redeemed_at'  => 'datetime',
            'metadata'     => 'array',
            'created_at'   => 'datetime',
            'updated_at'   => 'datetime',
            'deleted_at'   => 'datetime',
        ];
    }

    /**
     * Discount relationship enabling quick access to promotion metadata.
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    /**
     * Discount code relationship for tracking the redeemed code details.
     */
    public function code(): BelongsTo
    {
        return $this->belongsTo(DiscountCode::class, 'code_id');
    }

    /**
     * User relationship tying the redemption back to a customer account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Order relationship linking the redemption to the completed purchase.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Creator relationship for audit trails when admins create redemptions.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Updater relationship for audit trails when admins modify redemptions.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope helper narrowing results to a specific discount identifier.
     */
    public function scopeForDiscount(Builder $query, int|string $discountId): Builder
    {
        return $query->where('discount_id', $discountId);
    }

    /**
     * Scope helper returning redemptions owned by the provided user identifier.
     */
    public function scopeForUser(Builder $query, int|string $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope helper limiting results to a specific order identifier.
     */
    public function scopeForOrder(Builder $query, int|string $orderId): Builder
    {
        return $query->where('order_id', $orderId);
    }

    /**
     * Scope helper constraining the redemption date to the supplied range.
     */
    public function scopeWithinDateRange(
        Builder $query,
        CarbonInterface|string $startDate,
        CarbonInterface|string $endDate,
    ): Builder {
        $start = $startDate instanceof CarbonInterface ? $startDate : Carbon::parse($startDate);
        $end = $endDate instanceof CarbonInterface ? $endDate : Carbon::parse($endDate);

        return $query->whereBetween('redeemed_at', [$start, $end]);
    }

    /**
     * Aggregate helper summarising total savings for a discount across all redemptions.
     */
    public static function getTotalSavedForDiscount(int|string $discountId): float
    {
        return (float) self::query()
            ->where('discount_id', $discountId)
            ->sum('amount_saved');
    }

    /**
     * Aggregate helper summarising total savings attributed to a specific user.
     */
    public static function getTotalSavedForUser(int|string $userId): float
    {
        return (float) self::query()
            ->where('user_id', $userId)
            ->sum('amount_saved');
    }

    /**
     * Scope helper to retrieve redemptions by status keyword.
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope helper to limit redemptions to a specific currency code.
     */
    public function scopeForCurrency(Builder $query, string $currencyCode): Builder
    {
        return $query->where('currency_code', $currencyCode);
    }

    /**
     * Scope helper returning redemptions from the most recent rolling window.
     */
    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('redeemed_at', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Scope helper selecting redemptions with an amount above the provided threshold.
     */
    public function scopeAboveAmount(Builder $query, float $amount): Builder
    {
        return $query->where('amount_saved', '>', $amount);
    }

    /**
     * Scope helper selecting redemptions with an amount below the provided threshold.
     */
    public function scopeBelowAmount(Builder $query, float $amount): Builder
    {
        return $query->where('amount_saved', '<', $amount);
    }

    /**
     * Aggregate helper counting redemptions for a given discount identifier.
     */
    public static function getTotalRedemptionsForDiscount(int $discountId): int
    {
        return self::query()->where('discount_id', $discountId)->count();
    }

    /**
     * Aggregate helper counting redemptions for a given user identifier.
     */
    public static function getTotalRedemptionsForUser(int $userId): int
    {
        return self::query()->where('user_id', $userId)->count();
    }

    /**
     * Aggregate helper calculating the average saving amount for a discount.
     */
    public static function getAverageSavedForDiscount(int $discountId): float
    {
        return (float) (self::query()->where('discount_id', $discountId)->avg('amount_saved') ?? 0.0);
    }

    /**
     * Aggregate helper calculating the average saving amount for a user.
     */
    public static function getAverageSavedForUser(int $userId): float
    {
        return (float) (self::query()->where('user_id', $userId)->avg('amount_saved') ?? 0.0);
    }

    /**
     * Convenience helper to determine if the redemption happened within the last day.
     */
    public function isRecent(): bool
    {
        return $this->redeemed_at instanceof CarbonInterface
            && $this->redeemed_at->isAfter(Carbon::now()->subDay());
    }

    /**
     * Presenter helper returning the amount saved in a user-friendly format.
     */
    public function getFormattedAmountSavedAttribute(): string
    {
        $currency = $this->currency_code ?? 'EUR';

        return number_format((float) $this->amount_saved, 2) . ' ' . $currency;
    }

    /**
     * Presenter helper providing a colour hint for UI badges based on status.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'success',
            'pending'   => 'warning',
            'cancelled' => 'danger',
            'refunded'  => 'info',
            default     => 'secondary',
        };
    }
}
