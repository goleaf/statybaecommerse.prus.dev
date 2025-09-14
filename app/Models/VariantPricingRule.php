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
 * @method static \Illuminate\Database\Eloquent\Builder|VariantPricingRule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VariantPricingRule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VariantPricingRule query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, EnabledScope::class])]
final class VariantPricingRule extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'variant_pricing_rules';
    
    protected $fillable = [
        'product_id',
        'rule_name',
        'rule_type',
        'conditions',
        'pricing_modifiers',
        'is_active',
        'priority',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'pricing_modifiers' => 'array',
            'is_active' => 'boolean',
            'priority' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    protected $appends = [
        'is_currently_active',
        'formatted_conditions',
        'formatted_modifiers',
    ];

    /**
     * Handle product functionality with proper error handling.
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Handle isCurrentlyActive functionality with proper error handling.
     * @return bool
     */
    public function getIsCurrentlyActiveAttribute(): bool
    {
        $now = now();
        
        if (!$this->is_active) {
            return false;
        }
        
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }
        
        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }
        
        return true;
    }

    /**
     * Handle getFormattedConditionsAttribute functionality with proper error handling.
     * @return string
     */
    public function getFormattedConditionsAttribute(): string
    {
        if (!$this->conditions) {
            return 'No conditions';
        }
        
        $formatted = [];
        foreach ($this->conditions as $condition) {
            $formatted[] = $condition['attribute'] . ' ' . $condition['operator'] . ' ' . $condition['value'];
        }
        
        return implode(' AND ', $formatted);
    }

    /**
     * Handle getFormattedModifiersAttribute functionality with proper error handling.
     * @return string
     */
    public function getFormattedModifiersAttribute(): string
    {
        if (!$this->pricing_modifiers) {
            return 'No modifiers';
        }
        
        $formatted = [];
        foreach ($this->pricing_modifiers as $modifier) {
            $formatted[] = $modifier['type'] . ': ' . $modifier['value'] . ($modifier['type'] === 'percentage' ? '%' : '€');
        }
        
        return implode(', ', $formatted);
    }

    /**
     * Handle scopeActive functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                  ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')
                  ->orWhere('ends_at', '>=', now());
            });
    }

    /**
     * Handle scopeByType functionality with proper error handling.
     * @param mixed $query
     * @param string $type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('rule_type', $type);
    }

    /**
     * Handle scopeByPriority functionality with proper error handling.
     * @param mixed $query
     * @param int $priority
     */
    public function scopeByPriority($query, int $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Handle scopeOrderedByPriority functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeOrderedByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    /**
     * Calculate price modifier for a given variant.
     * @param ProductVariant $variant
     * @return float
     */
    public function calculatePriceModifier(ProductVariant $variant): float
    {
        if (!$this->is_currently_active) {
            return 0.0;
        }

        $modifier = 0.0;
        
        foreach ($this->pricing_modifiers as $pricingModifier) {
            if ($this->matchesConditions($variant, $pricingModifier)) {
                $modifier += $this->applyModifier($variant->price, $pricingModifier);
            }
        }
        
        return $modifier;
    }

    /**
     * Check if variant matches the rule conditions.
     * @param ProductVariant $variant
     * @param array $modifier
     * @return bool
     */
    private function matchesConditions(ProductVariant $variant, array $modifier): bool
    {
        if (!isset($modifier['conditions'])) {
            return true;
        }

        foreach ($modifier['conditions'] as $condition) {
            if (!$this->evaluateCondition($variant, $condition)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Evaluate a single condition against the variant.
     * @param ProductVariant $variant
     * @param array $condition
     * @return bool
     */
    private function evaluateCondition(ProductVariant $variant, array $condition): bool
    {
        $attribute = $condition['attribute'];
        $operator = $condition['operator'];
        $value = $condition['value'];
        
        $variantValue = $this->getVariantAttributeValue($variant, $attribute);
        
        return match ($operator) {
            'equals' => $variantValue == $value,
            'not_equals' => $variantValue != $value,
            'greater_than' => $variantValue > $value,
            'less_than' => $variantValue < $value,
            'contains' => str_contains((string) $variantValue, (string) $value),
            'not_contains' => !str_contains((string) $variantValue, (string) $value),
            default => false,
        };
    }

    /**
     * Get variant attribute value by attribute name.
     * @param ProductVariant $variant
     * @param string $attribute
     * @return mixed
     */
    private function getVariantAttributeValue(ProductVariant $variant, string $attribute): mixed
    {
        return match ($attribute) {
            'size' => $variant->size,
            'variant_type' => $variant->variant_type,
            'price' => $variant->price,
            'weight' => $variant->weight,
            default => $variant->getAttribute($attribute),
        };
    }

    /**
     * Apply modifier to base price.
     * @param float $basePrice
     * @param array $modifier
     * @return float
     */
    private function applyModifier(float $basePrice, array $modifier): float
    {
        $value = (float) $modifier['value'];
        
        return match ($modifier['type']) {
            'percentage' => $basePrice * ($value / 100),
            'fixed_amount' => $value,
            'multiplier' => $basePrice * $value,
            default => 0.0,
        };
    }
}
