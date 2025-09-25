<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\UserOwnedScope;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

#[ScopedBy([UserOwnedScope::class])]
final class CouponUsage extends Model
{
    use HasFactory;
    use SoftDeletes;

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

    protected static function booted(): void
    {
        self::creating(static function (self $couponUsage): void {
            $couponUsage->used_at ??= now();
        });
    }

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

    /**
     * @param  Builder<self>  $query
     */
    public function scopeUsedToday(Builder $query): Builder
    {
        return $query->whereDate('used_at', today());
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeUsedThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('used_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeUsedThisMonth(Builder $query): Builder
    {
        return $query->whereBetween('used_at', [now()->startOfMonth(), now()->endOfMonth()]);
    }

    public function registerUsage(array $metadata = []): void
    {
        $this->forceFill([
            'metadata' => $metadata,
            'used_at' => now(),
        ])->save();

        $this->coupon?->increment('times_used');
        $this->notifyUser();
    }

    public function notifyUser(): void
    {
        if (! $this->relationLoaded('user')) {
            $this->load('user');
        }

        if ($this->user) {
            Notification::send($this->user, new CouponUsageNotification($this));
        }
    }

    public function getFormattedDiscountAttribute(): string
    {
        return currency($this->discount_amount, currency: 'EUR');
    }

    public function getFormattedUsedAtAttribute(): string
    {
        return $this->used_at?->format('Y-m-d H:i:s') ?? '-';
    }

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
     * @param  Builder<self>  $query
     */
    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('used_at', '>=', now()->subDays($days));
    }

    public function duplicateForOrder(Order $order): self
    {
        return $this->replicate([
            'order_id' => $order->id,
            'used_at' => Carbon::now(),
        ]);
    }
}
