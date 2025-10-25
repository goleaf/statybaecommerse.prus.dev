<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ReferralCodeStatisticsFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ReferralCodeStatistics
 *
 * Eloquent model representing daily aggregated statistics for referral codes
 * tracking views, clicks, signups, conversions, and revenue metrics.
 *
 * @property int                             $id
 * @property int                             $referral_code_id
 * @property \Illuminate\Support\Carbon      $date
 * @property int                             $total_views
 * @property int                             $total_clicks
 * @property int                             $total_signups
 * @property int                             $total_conversions
 * @property float                           $total_revenue
 * @property array<string, mixed>|null       $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read ReferralCode $referralCode
 * @property-read float $conversion_rate
 * @property-read float $click_through_rate
 * @property-read float $signup_rate
 * @property-read float $average_revenue_per_conversion
 *
 * @method static Builder<self>                 byDateRange(string $startDate, string $endDate)
 * @method static Builder<self>                 byReferralCode(int $referralCodeId)
 * @method static Builder<self>                 today()
 * @method static Builder<self>                 thisWeek()
 * @method static Builder<self>                 thisMonth()
 * @method static Builder<self>                 withConversions()
 * @method static Builder<self>                 withRevenue()
 * @method static Builder<self>                 highPerforming(int $minConversions = 5, float $minRevenue = 100.0)
 * @method static Builder<self>                 orderByDate(string $direction = 'asc')
 * @method static ReferralCodeStatisticsFactory factory($count = null, $state = [])
 * @method static Builder<self>                 newModelQuery()
 * @method static Builder<self>                 newQuery()
 * @method static Builder<self>                 query()
 *
 * @mixin \Eloquent
 */
final class ReferralCodeStatistics extends Model
{
    /** @use HasFactory<ReferralCodeStatisticsFactory> */
    use HasFactory;

    protected $fillable = [
        'referral_code_id',
        'date',
        'total_views',
        'total_clicks',
        'total_signups',
        'total_conversions',
        'total_revenue',
        'metadata',
        'meta',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date'              => 'date',
            'total_views'       => 'integer',
            'total_clicks'      => 'integer',
            'total_signups'     => 'integer',
            'total_conversions' => 'integer',
            'total_revenue'     => 'decimal:2',
            'metadata'          => 'array',
            'meta'              => 'array',
        ];
    }

    // Relationships

    /**
     * Get the referral code that owns these statistics.
     */
    public function referralCode(): BelongsTo
    {
        return $this->belongsTo(ReferralCode::class);
    }

    // Query Scopes

    /**
     * Scope a query to filter statistics within a date range.
     *
     * @param Builder<self> $query
     */
    public function scopeByDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter statistics by referral code.
     *
     * @param Builder<self> $query
     */
    public function scopeByReferralCode(Builder $query, int $referralCodeId): Builder
    {
        return $query->where('referral_code_id', $referralCodeId);
    }

    /**
     * Scope a query to filter statistics for today.
     *
     * @param Builder<self> $query
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('date', today());
    }

    /**
     * Scope a query to filter statistics for this week.
     *
     * @param Builder<self> $query
     */
    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('date', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    /**
     * Scope a query to filter statistics for this month.
     *
     * @param Builder<self> $query
     */
    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereBetween('date', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ]);
    }

    /**
     * Scope a query to only include statistics with conversions.
     *
     * @param Builder<self> $query
     */
    public function scopeWithConversions(Builder $query): Builder
    {
        return $query->where('total_conversions', '>', 0);
    }

    /**
     * Scope a query to only include statistics with revenue.
     *
     * @param Builder<self> $query
     */
    public function scopeWithRevenue(Builder $query): Builder
    {
        return $query->where('total_revenue', '>', 0);
    }

    /**
     * Scope a query to filter high performing statistics.
     *
     * @param Builder<self> $query
     */
    public function scopeHighPerforming(Builder $query, int $minConversions = 5, float $minRevenue = 100.0): Builder
    {
        return $query
            ->where('total_conversions', '>=', $minConversions)
            ->where('total_revenue', '>=', $minRevenue);
    }

    /**
     * Scope a query to order statistics by date.
     *
     * @param Builder<self> $query
     */
    public function scopeOrderByDate(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('date', $direction);
    }

    // Accessors

    /**
     * Calculate the conversion rate (conversions / clicks * 100).
     */
    protected function conversionRate(): Attribute
    {
        return Attribute::make(
            get: function (): float {
                if ($this->total_clicks === 0) {
                    return 0.0;
                }

                return round(($this->total_conversions / $this->total_clicks) * 100, 2);
            }
        );
    }

    /**
     * Calculate the click-through rate (clicks / views * 100).
     */
    protected function clickThroughRate(): Attribute
    {
        return Attribute::make(
            get: function (): float {
                if ($this->total_views === 0) {
                    return 0.0;
                }

                return round(($this->total_clicks / $this->total_views) * 100, 2);
            }
        );
    }

    /**
     * Calculate the signup rate (signups / views * 100).
     */
    protected function signupRate(): Attribute
    {
        return Attribute::make(
            get: function (): float {
                if ($this->total_views === 0) {
                    return 0.0;
                }

                return round(($this->total_signups / $this->total_views) * 100, 2);
            }
        );
    }

    /**
     * Calculate the average revenue per conversion.
     */
    protected function averageRevenuePerConversion(): Attribute
    {
        return Attribute::make(
            get: function (): float {
                if ($this->total_conversions === 0) {
                    return 0.0;
                }

                return round($this->total_revenue / $this->total_conversions, 2);
            }
        );
    }

    // Helper Methods

    /**
     * Get the conversion rate as a float.
     */
    public function getConversionRate(): float
    {
        return $this->conversion_rate;
    }

    /**
     * Get the click-through rate as a float.
     */
    public function getClickThroughRate(): float
    {
        return $this->click_through_rate;
    }

    /**
     * Get the signup rate as a float.
     */
    public function getSignupRate(): float
    {
        return $this->signup_rate;
    }

    /**
     * Get the average revenue per conversion as a float.
     */
    public function getAverageRevenuePerConversion(): float
    {
        return $this->average_revenue_per_conversion;
    }

    /**
     * Increment view count.
     */
    public function incrementViews(int $count = 1): int
    {
        return $this->increment('total_views', $count);
    }

    /**
     * Increment click count.
     */
    public function incrementClicks(int $count = 1): int
    {
        return $this->increment('total_clicks', $count);
    }

    /**
     * Increment signup count.
     */
    public function incrementSignups(int $count = 1): int
    {
        return $this->increment('total_signups', $count);
    }

    /**
     * Increment conversion count and revenue.
     */
    public function incrementConversions(int $count = 1, float $revenue = 0.0): bool
    {
        $this->increment('total_conversions', $count);

        if ($revenue > 0) {
            $this->increment('total_revenue', $revenue);
        }

        return true;
    }

    /**
     * Check if statistics are performing well.
     */
    public function isHighPerforming(int $minConversions = 5, float $minRevenue = 100.0): bool
    {
        return $this->total_conversions >= $minConversions &&
            $this->total_revenue >= $minRevenue;
    }
}
