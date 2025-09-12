<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

final class ReferralCodeStatistics extends Model
{
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
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total_revenue' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the referral code this statistic belongs to
     */
    public function referralCode(): BelongsTo
    {
        return $this->belongsTo(ReferralCode::class);
    }

    /**
     * Scope for statistics by date range
     */
    public function scopeByDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope for statistics by referral code
     */
    public function scopeByReferralCode(Builder $query, int $referralCodeId): Builder
    {
        return $query->where('referral_code_id', $referralCodeId);
    }

    /**
     * Get conversion rate
     */
    public function getConversionRateAttribute(): float
    {
        if ($this->total_clicks === 0) {
            return 0;
        }

        return ($this->total_conversions / $this->total_clicks) * 100;
    }

    /**
     * Get click-through rate
     */
    public function getClickThroughRateAttribute(): float
    {
        if ($this->total_views === 0) {
            return 0;
        }

        return ($this->total_clicks / $this->total_views) * 100;
    }
}

