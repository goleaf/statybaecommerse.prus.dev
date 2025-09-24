<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * VariantPricingRule
 *
 * Eloquent model representing the VariantPricingRule entity for dynamic variant pricing.
 *
 * @property mixed $table
 * @property mixed $fillable
 * @property mixed $casts
 * @property mixed $appends
 *
 * @method static \Illuminate\Database\Eloquent\Builder|VariantPricingRule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VariantPricingRule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VariantPricingRule query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, EnabledScope::class])]
final class VariantPricingRule extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'variant_pricing_rules';

    protected $fillable = [
        'name',
        'type',
        'value',
        'product_variant_id',
        'customer_group_id',
        'min_quantity',
        'max_quantity',
        'priority',
        'is_active',
        'is_cumulative',
        'valid_from',
        'valid_until',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_quantity' => 'integer',
            'max_quantity' => 'integer',
            'priority' => 'integer',
            'is_active' => 'boolean',
            'is_cumulative' => 'boolean',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
        ];
    }

    protected $appends = [
        'is_currently_active',
    ];

    /**
     * Handle productVariant functionality with proper error handling.
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Handle customerGroup functionality with proper error handling.
     */
    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    /**
     * Handle isCurrentlyActive functionality with proper error handling.
     */
    public function getIsCurrentlyActiveAttribute(): bool
    {
        $now = now();

        if (! $this->is_active) {
            return false;
        }

        if ($this->valid_from && $this->valid_from->isFuture()) {
            return false;
        }

        if ($this->valid_until && $this->valid_until->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Handle scopeActive functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', now());
            });
    }

    /**
     * Handle scopeByType functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Handle scopeByPriority functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByPriority($query, int $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Handle scopeOrderedByPriority functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeOrderedByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    /**
     * Calculate price modifier for a given variant.
     */
    public function calculatePriceModifier(ProductVariant $variant): float
    {
        if (! $this->is_currently_active) {
            return 0.0;
        }

        if ($this->product_variant_id && $this->product_variant_id !== $variant->id) {
            return 0.0;
        }

        return match ($this->type) {
            'percentage' => $variant->price * ($this->value / 100),
            'fixed' => $this->value,
            'tier' => $this->calculateTierModifier($variant),
            'bulk' => $this->calculateBulkModifier($variant),
            default => 0.0,
        };
    }

    /**
     * Calculate tier-based modifier.
     */
    private function calculateTierModifier(ProductVariant $variant): float
    {
        // Simple tier calculation - can be extended based on business logic
        return $this->value;
    }

    /**
     * Calculate bulk-based modifier.
     */
    private function calculateBulkModifier(ProductVariant $variant): float
    {
        // Simple bulk calculation - can be extended based on business logic
        return $this->value;
    }
}
