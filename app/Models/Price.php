<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Price
 *
 * Eloquent model representing the Price entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Price newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Price newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Price query()
 *
 * @mixin \Eloquent
 */
final class Price extends Model
{
    use HasFactory;

    public const ALLOWED_TYPES = [
        'retail',
        'wholesale',
        'special',
        'sale',
    ];

    protected $table = 'prices';

    protected $fillable = ['priceable_id', 'priceable_type', 'currency_id', 'amount', 'cost_amount', 'type', 'starts_at', 'ends_at', 'is_enabled', 'metadata'];

    protected static function booted(): void
    {
        self::creating(static function (self $price): void {
            $price->type = self::normalizeType($price->type);
        });

        self::updating(static function (self $price): void {
            $price->type = self::normalizeType($price->type);
        });
    }

    private static function normalizeType(mixed $value): string
    {
        if (! is_string($value)) {
            return 'retail';
        }

        $type = trim($value);

        if ($type === '' || ! in_array($type, self::ALLOWED_TYPES, true)) {
            return 'retail';
        }

        return $type;
    }

    /**
     * Handle casts functionality with proper error handling.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        // Guarantee that all monetary values preserve precision while dates remain Carbon instances.
        return [
            'amount'      => 'decimal:4',
            'cost_amount' => 'decimal:4',
            'starts_at'   => 'datetime',
            'ends_at'     => 'datetime',
            'is_enabled'  => 'boolean',
            'metadata'    => 'array',
        ];
    }

    /**
     * Handle priceable functionality with proper error handling.
     *
     * @return MorphTo<Model, self>
     */
    public function priceable(): MorphTo
    {
        // Delegate to Laravel's morphTo helper to support products, variants, and other priceable entities.
        return $this->morphTo();
    }

    /**
     * Handle currency functionality with proper error handling.
     *
     * @return BelongsTo<Currency, self>
     */
    public function currency(): BelongsTo
    {
        // Connect each price to the currency record responsible for formatting and exchange rate handling.
        return $this->belongsTo(Currency::class);
    }

    /**
     * Expose the owning product when the price record represents a product-specific entry.
     *
     * @return BelongsTo<Product, self>
     */
    public function product(): BelongsTo
    {
        // Connect to the owning product when the price record represents a product-specific entry.
        return $this->belongsTo(Product::class, 'priceable_id');
    }

    /**
     * Handle scopeEnabled functionality with proper error handling.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeEnabled(Builder $query): Builder
    {
        // Filter to prices explicitly flagged as enabled while leaving time-based
        // concerns to the dedicated `active()` scope that callers can append as
        // needed when narrowing to currently valid entries.
        return $query->where('is_enabled', true);
    }

    /**
     * Handle scopeActive functionality with proper error handling.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        // Keep the comparison moment consistent across the query evaluation.
        $now = now();

        return $query
            ->where('is_enabled', true)
            ->where(static function (Builder $builder) use ($now): void {
                // Allow records that have already started or do not have a start constraint.
                $builder->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(static function (Builder $builder) use ($now): void {
                // Allow records that have not yet ended or do not have an end constraint.
                $builder->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });
    }

    /**
     * Handle scopeForCurrency functionality with proper error handling.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeForCurrency(Builder $query, string $currencyCode): Builder
    {
        // Filter by ISO code without altering other scope combinations so the
        // caller retains control over enabled or active constraints.
        return $query->whereHas('currency', static function (Builder $builder) use ($currencyCode): void {
            $builder->where('code', $currencyCode);
        });
    }

    /**
     * Handle isActive functionality with proper error handling.
     */
    public function isActive(): bool
    {
        if (! $this->is_enabled) {
            return false;
        }
        $now = now();
        if ($this->starts_at && $this->starts_at->gt($now)) {
            return false;
        }

        return ! ($this->ends_at && $this->ends_at->lt($now));
    }
}
