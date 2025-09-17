<?php

declare (strict_types=1);
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * ReferralCodeStatistics
 * 
 * Eloquent model representing the ReferralCodeStatistics entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $fillable
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralCodeStatistics newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralCodeStatistics newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralCodeStatistics query()
 * @mixin \Eloquent
 */
final class ReferralCodeStatistics extends Model
{
    use HasFactory;
    protected $fillable = ['referral_code_id', 'date', 'total_views', 'total_clicks', 'total_signups', 'total_conversions', 'total_revenue', 'metadata'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['date' => 'date', 'total_revenue' => 'decimal:2', 'metadata' => 'array'];
    }
    /**
     * Handle referralCode functionality with proper error handling.
     * @return BelongsTo
     */
    public function referralCode(): BelongsTo
    {
        return $this->belongsTo(ReferralCode::class);
    }
    /**
     * Handle scopeByDateRange functionality with proper error handling.
     * @param Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return Builder
     */
    public function scopeByDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }
    /**
     * Handle scopeByReferralCode functionality with proper error handling.
     * @param Builder $query
     * @param int $referralCodeId
     * @return Builder
     */
    public function scopeByReferralCode(Builder $query, int $referralCodeId): Builder
    {
        return $query->where('referral_code_id', $referralCodeId);
    }
    /**
     * Handle getConversionRateAttribute functionality with proper error handling.
     * @return float
     */
    public function getConversionRateAttribute(): float
    {
        if ($this->total_clicks === 0) {
            return 0;
        }
        return $this->total_conversions / $this->total_clicks * 100;
    }
    /**
     * Handle getClickThroughRateAttribute functionality with proper error handling.
     * @return float
     */
    public function getClickThroughRateAttribute(): float
    {
        if ($this->total_views === 0) {
            return 0;
        }
        return $this->total_clicks / $this->total_views * 100;
    }
}