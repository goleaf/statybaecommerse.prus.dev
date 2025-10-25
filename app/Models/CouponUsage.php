<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\UserOwnedScope;
use App\Notifications\CouponUsageNotification;
use Carbon\CarbonInterface;
use Database\Factories\CouponUsageFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Number;
use Stringable;

/**
 * CouponUsage
 *
 * Eloquent model capturing a single usage of a coupon by a user within an order context.
 *
 * @property int                       $id
 * @property int                       $coupon_id
 * @property int                       $user_id
 * @property int|null                  $order_id
 * @property string                    $discount_amount
 * @property CarbonInterface|null      $used_at
 * @property array<string, mixed>|null $metadata
 *
 * @method static Builder<self> usedToday()
 * @method static Builder<self> usedThisWeek()
 * @method static Builder<self> usedThisMonth()
 * @method static Builder<self> recent(int $days = 7)
 *
 * @mixin \Eloquent
 */
#[ScopedBy([UserOwnedScope::class])]
final class CouponUsage extends Model
{
    /** @use HasFactory<CouponUsageFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'coupon_id',
        'user_id',
        'order_id',
        'discount_amount',
        'used_at',
        'metadata',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'discount_amount' => 'decimal:2',
        'used_at'         => 'datetime',
        'metadata'        => 'array',
    ];

    /**
     * Seed sensible defaults when the model is first persisted.
     */
    protected static function booted(): void
    {
        // Default the usage timestamp to now when none is provided explicitly.
        self::creating(static function (self $couponUsage): void {
            $couponUsage->used_at ??= now();
        });
    }

    /**
     * Provide convenient access to the parent coupon relation.
     *
     * @return BelongsTo<Coupon, self>
     */
    public function coupon(): BelongsTo
    {
        /** @var BelongsTo<Coupon, self> $relation */
        $relation = $this->belongsTo(Coupon::class);

        return $relation;
    }

    /**
     * Provide convenient access to the owning user relation.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        /** @var BelongsTo<User, self> $relation */
        $relation = $this->belongsTo(User::class);

        return $relation;
    }

    /**
     * Provide convenient access to the related order relation.
     *
     * @return BelongsTo<Order, self>
     */
    public function order(): BelongsTo
    {
        /** @var BelongsTo<Order, self> $relation */
        $relation = $this->belongsTo(Order::class);

        return $relation;
    }

    /**
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeUsedToday(Builder $query): Builder
    {
        return $query->whereDate('used_at', today());
    }

    /**
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeUsedThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('used_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeUsedThisMonth(Builder $query): Builder
    {
        return $query->whereBetween('used_at', [now()->startOfMonth(), now()->endOfMonth()]);
    }

    /**
     * Register the coupon usage, update metadata, and notify stakeholders.
     *
     * @param array<string, mixed> $metadata
     */
    public function registerUsage(array $metadata = []): void
    {
        $this->forceFill([
            'metadata' => $metadata,
            'used_at'  => now(),
        ])->save();

        // Keep the coupon usage counter accurate for analytics dashboards.
        $this->coupon?->increment('used_count');
        $this->notifyUser();
    }

    /**
     * Notify the associated user that their coupon has been consumed.
     */
    public function notifyUser(): void
    {
        if (! $this->relationLoaded('user')) {
            $this->load('user');
        }

        if ($this->user) {
            // Dispatch the notification using the facade so queued channels are respected.
            Notification::send($this->user, new CouponUsageNotification($this));
        }
    }

    /**
     * Present the discount amount using the shared currency helper.
     */
    public function getFormattedDiscountAttribute(): string
    {
        if (function_exists('currency')) {
            // Prefer the global helper when available so formatting stays consistent with the storefront.
            /** @var string|Stringable|false $formatted */
            $formatted = currency($this->discount_amount, currency: 'EUR');

            if ($formatted !== false) {
                return (string) $formatted;
            }
        }

        // Fallback to the framework helper for CLI environments where the currency helper is unavailable.
        $fallback = Number::currency((float) $this->discount_amount, 'EUR');

        return is_string($fallback) ? $fallback : (string) $fallback;
    }

    /**
     * Present the usage timestamp or a placeholder when unavailable.
     */
    public function getFormattedUsedAtAttribute(): string
    {
        return $this->used_at?->format('Y-m-d H:i:s') ?? '-';
    }

    /**
     * Resolve a friendly label representing when the usage occurred.
     */
    public function getUsagePeriodAttribute(): string
    {
        $usedAt = $this->used_at;

        if (! $usedAt instanceof CarbonInterface) {
            return __('admin.coupon_usages.periods.older');
        }

        if ($usedAt->isToday()) {
            return __('admin.coupon_usages.periods.today');
        }

        if ($usedAt->between(now()->startOfWeek(), now()->endOfWeek())) {
            return __('admin.coupon_usages.periods.this_week');
        }

        if ($usedAt->isSameMonth(now())) {
            return __('admin.coupon_usages.periods.this_month');
        }

        return __('admin.coupon_usages.periods.older');
    }

    /**
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('used_at', '>=', now()->subDays($days));
    }

    /**
     * Produce a detached clone of the usage for another order context.
     */
    public function duplicateForOrder(Order $order): self
    {
        // Replicate while overriding the foreign key and timestamp on the detached clone.
        $duplicate = $this->replicate();
        $duplicate->order_id = $order->id;
        $duplicate->used_at = Carbon::now();

        return $duplicate;
    }
}
