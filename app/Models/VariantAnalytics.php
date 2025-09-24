<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * VariantAnalytics
 *
 * Model for tracking analytics and performance metrics of product variants.
 */
final class VariantAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'variant_id',
        'date',
        'views',
        'clicks',
        'add_to_cart',
        'purchases',
        'revenue',
        'conversion_rate',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'views' => 'integer',
            'clicks' => 'integer',
            'add_to_cart' => 'integer',
            'purchases' => 'integer',
            'revenue' => 'decimal:4',
            'conversion_rate' => 'decimal:4',
        ];
    }

    /**
     * Get the variant that owns the analytics.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Get the click-through rate (CTR).
     */
    public function getClickThroughRateAttribute(): float
    {
        if ($this->views <= 0) {
            return 0;
        }

        return ($this->clicks / $this->views) * 100;
    }

    /**
     * Get the add-to-cart rate.
     */
    public function getAddToCartRateAttribute(): float
    {
        if ($this->clicks <= 0) {
            return 0;
        }

        return ($this->add_to_cart / $this->clicks) * 100;
    }

    /**
     * Get the purchase rate.
     */
    public function getPurchaseRateAttribute(): float
    {
        if ($this->add_to_cart <= 0) {
            return 0;
        }

        return ($this->purchases / $this->add_to_cart) * 100;
    }

    /**
     * Get the average revenue per purchase.
     */
    public function getAverageRevenuePerPurchaseAttribute(): float
    {
        if ($this->purchases <= 0) {
            return 0;
        }

        return $this->revenue / $this->purchases;
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to get recent analytics.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('date', '>=', now()->subDays($days));
    }

    /**
     * Scope to get top performing variants.
     */
    public function scopeTopPerforming($query, int $limit = 10)
    {
        return $query->orderBy('conversion_rate', 'desc')
            ->orderBy('revenue', 'desc')
            ->limit($limit);
    }

    /**
     * Scope to get variants by performance metric.
     */
    public function scopeByMetric($query, string $metric, string $direction = 'desc')
    {
        return $query->orderBy($metric, $direction);
    }

    /**
     * Record analytics data for a variant.
     */
    public static function recordAnalytics(
        int $variantId,
        string|\DateTime $date,
        array $data = []
    ): self {
        $defaultData = [
            'variant_id' => $variantId,
            'date' => $date,
            'views' => $data['views'] ?? 0,
            'clicks' => $data['clicks'] ?? 0,
            'add_to_cart' => $data['add_to_cart'] ?? 0,
            'purchases' => $data['purchases'] ?? 0,
            'revenue' => $data['revenue'] ?? 0,
            'conversion_rate' => $data['conversion_rate'] ?? 0,
        ];

        return self::updateOrCreate(
            ['variant_id' => $variantId, 'date' => $date],
            $defaultData
        );
    }

    /**
     * Increment a specific metric.
     */
    public function incrementMetric(string $metric, int $amount = 1): bool
    {
        return (bool) $this->increment($metric, $amount);
    }

    /**
     * Update conversion rate based on current metrics.
     */
    public function updateConversionRate(): bool
    {
        $conversionRate = 0;

        if ($this->views > 0) {
            $conversionRate = ($this->purchases / $this->views) * 100;
        }

        return $this->update(['conversion_rate' => $conversionRate]);
    }
}
