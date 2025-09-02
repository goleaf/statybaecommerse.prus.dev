<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class DiscountCondition extends Model
{
    use HasFactory;

    protected $table = 'discount_conditions';

    protected $fillable = [
        'discount_id',
        'type',
        'operator',
        'value',
        'position',
    ];

    protected $casts = [
        'value' => 'array',
        'position' => 'integer',
    ];

    /**
     * Get the discount this condition belongs to
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    /**
     * Check if the condition matches the given value
     */
    public function matches($testValue): bool
    {
        $conditionValue = $this->value;

        return match ($this->operator) {
            'equals_to' => $testValue == $conditionValue,
            'not_equals_to' => $testValue != $conditionValue,
            'less_than' => $testValue < $conditionValue,
            'greater_than' => $testValue > $conditionValue,
            'starts_with' => str_starts_with((string) $testValue, (string) $conditionValue),
            'ends_with' => str_ends_with((string) $testValue, (string) $conditionValue),
            'contains' => str_contains((string) $testValue, (string) $conditionValue),
            'not_contains' => !str_contains((string) $testValue, (string) $conditionValue),
            default => false,
        };
    }

    /**
     * Get available condition types
     */
    public static function getTypes(): array
    {
        return [
            'product',
            'category',
            'brand',
            'collection',
            'attribute_value',
            'cart_total',
            'item_qty',
            'zone',
            'channel',
            'currency',
            'customer_group',
            'user',
            'partner_tier',
            'first_order',
            'day_time',
            'custom_script',
        ];
    }

    /**
     * Get available operators
     */
    public static function getOperators(): array
    {
        return [
            'equals_to',
            'not_equals_to',
            'less_than',
            'greater_than',
            'starts_with',
            'ends_with',
            'contains',
            'not_contains',
        ];
    }
}
