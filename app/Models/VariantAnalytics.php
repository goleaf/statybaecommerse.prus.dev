<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * VariantAnalytics
 *
 * Model for tracking analytics and performance metrics of product variants.
 */
final class VariantAnalytics extends Model
{
    use HasFactory;

    /**
     * Metrics that can be recorded for analytics entries.
     *
     * @var array<int, string>
     */
    private const METRIC_FIELDS = [
        'views',
        'clicks',
        'add_to_cart',
        'purchases',
        'revenue',
        'conversion_rate',
    ];

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
        string|\DateTimeInterface $date,
        array $data = []
    ): self {
        $dateKey = Carbon::parse($date)->toDateString();

        return DB::transaction(
            static function () use ($variantId, $dateKey, $data): self {
                $metrics = Arr::only($data, self::METRIC_FIELDS);

                $analytics = self::query()
                    ->lockForUpdate()
                    ->firstOrNew([
                        'variant_id' => $variantId,
                        'date' => $dateKey,
                    ]);

                if (! $analytics->exists) {
                    foreach (self::METRIC_FIELDS as $field) {
                        $analytics->{$field} = $analytics->{$field} ?? 0;
                    }
                }

                foreach ($metrics as $field => $value) {
                    $analytics->{$field} = $value;
                }

                $analytics->variant_id = $variantId;
                $analytics->date = $dateKey;
                $analytics->save();

                return $analytics;
            }
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
