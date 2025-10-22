<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * VariantAnalytics
 *
 * Model for tracking analytics and performance metrics of product variants.
 */
final class VariantAnalytics extends Model
{
    use HasFactory;

    public const BUCKET_DAILY = 'daily';

    public const BUCKET_WEEKLY = 'weekly';

    private const SUPPORTED_GRANULARITIES = [self::BUCKET_DAILY, self::BUCKET_WEEKLY];

    protected $fillable = [
        'product_id',
        'variant_id',
        'date',
        'date_bucket',
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
            'product_id'      => 'integer',
            'date'            => 'date',
            'date_bucket'     => 'string',
            'views'           => 'integer',
            'clicks'          => 'integer',
            'add_to_cart'     => 'integer',
            'purchases'       => 'integer',
            'revenue'         => 'decimal:4',
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
     * Get the product that this analytics row refers to.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
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
     * Scope to filter records for a specific granularity bucket.
     */
    public function scopeForGranularity($query, string $granularity)
    {
        return $query->where('date_bucket', 'like', sprintf('%s:%%', $granularity));
    }

    /**
     * Scope to filter daily bucket rows.
     */
    public function scopeDaily($query)
    {
        return $query->forGranularity(self::BUCKET_DAILY);
    }

    /**
     * Scope to filter weekly bucket rows.
     */
    public function scopeWeekly($query)
    {
        return $query->forGranularity(self::BUCKET_WEEKLY);
    }

    /**
     * Scope to get recent analytics.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->daily()->where('date', '>=', now()->subDays($days));
    }

    /**
     * Scope to get top performing variants.
     */
    public function scopeTopPerforming($query, int $limit = 10)
    {
        return $query->daily()->orderBy('conversion_rate', 'desc')
            ->orderBy('revenue', 'desc')
            ->limit($limit);
    }

    /**
     * Scope to get variants by performance metric.
     */
    public function scopeByMetric($query, string $metric, string $direction = 'desc')
    {
        return $query->daily()->orderBy($metric, $direction);
    }

    /**
     * Record analytics data for a variant.
     */
    public static function recordAnalytics(
        int $variantId,
        string|DateTimeInterface $date,
        array $data = [],
        string $granularity = self::BUCKET_DAILY,
        ?int $productId = null
    ): self {
        $granularity = strtolower($granularity);
        self::ensureValidGranularity($granularity);

        $productId ??= self::resolveProductId($variantId);
        $normalizedDate = self::normalizeDate($date, $granularity);
        $bucket = self::buildDateBucket($granularity, $normalizedDate);
        $now = now();

        // Ensure the conversion rate always has a safe default because the database column is non-nullable.
        $conversionRate = array_key_exists('conversion_rate', $data)
            ? round((float) $data['conversion_rate'], 4)
            : 0.0;

        $payload = [
            'product_id'  => $productId,
            'variant_id'  => $variantId,
            'date'        => $normalizedDate,
            'date_bucket' => $bucket,
            'views'       => (int) ($data['views'] ?? 0),
            'clicks'      => (int) ($data['clicks'] ?? 0),
            'add_to_cart' => (int) ($data['add_to_cart'] ?? 0),
            'purchases'   => (int) ($data['purchases'] ?? 0),
            'revenue'     => (float) ($data['revenue'] ?? 0),
            // Persist the normalized conversion rate value so inserts never violate the NOT NULL constraint.
            'conversion_rate' => $conversionRate,
            'created_at'      => $now,
            'updated_at'      => $now,
        ];

        $updates = [
            'updated_at'  => $now,
            'date'        => $normalizedDate,
            'views'       => self::incrementExpression('views'),
            'clicks'      => self::incrementExpression('clicks'),
            'add_to_cart' => self::incrementExpression('add_to_cart'),
            'purchases'   => self::incrementExpression('purchases'),
            'revenue'     => self::incrementExpression('revenue'),
        ];

        if (array_key_exists('conversion_rate', $data)) {
            // When an explicit conversion rate is provided we replace the stored value instead of incrementing.
            $updates['conversion_rate'] = self::replacementExpression('conversion_rate');
        }

        self::query()->upsert(
            [$payload],
            ['product_id', 'variant_id', 'date_bucket'],
            $updates
        );

        return self::query()
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->where('date_bucket', $bucket)
            ->firstOrFail();
    }

    private static function normalizeDate(string|DateTimeInterface $date, string $granularity = self::BUCKET_DAILY): string
    {
        $carbon = $date instanceof DateTimeInterface
            ? Carbon::instance($date)
            : Carbon::parse($date);

        if ($granularity === self::BUCKET_WEEKLY) {
            return $carbon->copy()->startOfWeek()->toDateString();
        }

        return $carbon->toDateString();
    }

    private static function buildDateBucket(string $granularity, string $normalizedDate): string
    {
        return sprintf('%s:%s', $granularity, $normalizedDate);
    }

    private static function ensureValidGranularity(string $granularity): void
    {
        if (! in_array($granularity, self::SUPPORTED_GRANULARITIES, true)) {
            throw new InvalidArgumentException(sprintf('Unsupported analytics granularity [%s].', $granularity));
        }
    }

    private static function resolveProductId(int $variantId): int
    {
        $productId = ProductVariant::query()->whereKey($variantId)->value('product_id');

        if (! $productId) {
            throw new InvalidArgumentException(sprintf('Unable to resolve product for variant [%d].', $variantId));
        }

        return (int) $productId;
    }

    private static function incrementExpression(string $column): Expression
    {
        return self::connectionDriver() === 'sqlite'
            ? DB::raw(sprintf('%1$s + excluded.%1$s', $column))
            : DB::raw(sprintf('%1$s + VALUES(%1$s)', $column));
    }

    private static function replacementExpression(string $column): Expression
    {
        return self::connectionDriver() === 'sqlite'
            ? DB::raw(sprintf('excluded.%s', $column))
            : DB::raw(sprintf('VALUES(%s)', $column));
    }

    private static function connectionDriver(): string
    {
        return self::query()->getConnection()->getDriverName();
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
