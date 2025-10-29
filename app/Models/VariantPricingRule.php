<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use Carbon\CarbonInterface;
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
    use HasFactory;
    use OrdersByName;
    use SoftDeletes;

    protected $table = 'variant_pricing_rules';

    protected $fillable = [
        'product_id',
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

    /**
     * Prefer the public-facing name column when applying shared alphabetical ordering.
     */
    protected string $nameColumn = 'name';

    protected function casts(): array
    {
        return [
            'value'         => 'decimal:2',
            'min_quantity'  => 'integer',
            'max_quantity'  => 'integer',
            'priority'      => 'integer',
            'is_active'     => 'boolean',
            'is_cumulative' => 'boolean',
            'valid_from'    => 'datetime',
            'valid_until'   => 'datetime',
        ];
    }

    protected $appends = [
        'is_currently_active',
    ];

    /**
     * Keep the denormalised product reference synchronised with the selected variant.
     */
    protected static function booted(): void
    {
        self::saving(function (self $rule): void {
            // Ensure that all pricing rules point at a concrete product so the foreign key stays valid.
            if ($rule->product_variant_id === null) {
                return;
            }

            $variant = $rule->relationLoaded('productVariant')
                ? $rule->productVariant
                : ProductVariant::query()->find($rule->product_variant_id);

            if ($variant === null) {
                return;
            }

            // Always mirror the variant's product, even when the variant assignment changes during edits.
            $rule->product_id = $variant->product_id;
        });
    }

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
        // Delegate to the reusable helper so runtime checks and attribute access stay consistent.
        return $this->isActiveAt(now());
    }

    /**
     * Determine if the rule is active at a specific moment, respecting validity windows.
     */
    public function isActiveAt(?CarbonInterface $moment = null): bool
    {
        $moment ??= now();

        if (! $this->is_active) {
            return false;
        }

        if ($this->valid_from && $this->valid_from->gt($moment)) {
            return false;
        }
        return !($this->valid_until && $this->valid_until->lt($moment));
    }

    /**
     * Handle scopeActive functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q): void {
                $q->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', now());
            })
            ->where(function ($q): void {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', now());
            });
    }

    /**
     * Handle scopeByType functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Handle scopeByPriority functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeByPriority($query, int $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Handle scopeOrderedByPriority functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeOrderedByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    /**
     * Calculate price modifier for a given variant.
     */
    public function calculatePriceModifier(
        ProductVariant $variant,
        int $quantity = 1,
        ?CarbonInterface $moment = null
    ): float {
        // Reuse the moment-aware helper so tests and runtime code can inject custom clocks.
        if (! $this->isActiveAt($moment)) {
            return 0.0;
        }

        if ($this->product_variant_id && $this->product_variant_id !== $variant->id) {
            return 0.0;
        }

        if ($this->min_quantity && $quantity < $this->min_quantity) {
            return 0.0;
        }

        if ($this->max_quantity && $quantity > $this->max_quantity) {
            return 0.0;
        }

        return match ($this->type) {
            'percentage' => $this->calculatePercentageModifier($variant),
            'fixed'      => (float) $this->value,
            'tier'       => $this->calculateTierModifier($quantity),
            'bulk'       => $this->calculateBulkModifier($quantity),
            default      => 0.0,
        };
    }

    /**
     * Translate percentage-based modifiers into concrete price adjustments.
     */
    private function calculatePercentageModifier(ProductVariant $variant): float
    {
        // Allow negative percentages (discounts) and positive surcharges.
        return (float) $variant->price * ((float) $this->value / 100);
    }

    /**
     * Calculate tier-based modifier.
     */
    private function calculateTierModifier(int $quantity): float
    {
        // Tier pricing can scale based on configured value while keeping hooks for richer logic later on.
        $base = (float) $this->value;

        if ($this->customer_group_id !== null) {
            // Apply a lightweight incentive for matching customer groups.
            return $base;
        }

        // Default to a modest quantity-sensitive adjustment.
        return $base * max(1, $quantity / 10);
    }

    /**
     * Calculate bulk-based modifier.
     */
    private function calculateBulkModifier(int $quantity): float
    {
        // Scale the configured value by the quantity so bulk purchases receive predictable incentives.
        return (float) $this->value * max(1, $quantity);
    }
}
