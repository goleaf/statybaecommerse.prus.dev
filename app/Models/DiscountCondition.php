<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Translations\DiscountConditionTranslation;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * DiscountCondition
 *
 * Eloquent model representing the DiscountCondition entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property string $translationModel
 * @property mixed $table
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class DiscountCondition extends Model
{
    use HasFactory, HasTranslations;

    protected string $translationModel = DiscountConditionTranslation::class;

    protected $table = 'discount_conditions';

    protected $fillable = ['discount_id', 'type', 'operator', 'value', 'position', 'is_active', 'priority', 'metadata'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['value' => 'array', 'position' => 'integer', 'is_active' => 'boolean', 'priority' => 'integer', 'metadata' => 'array'];
    }

    /**
     * Handle discount functionality with proper error handling.
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    /**
     * Handle translations functionality with proper error handling.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(DiscountConditionTranslation::class);
    }

    /**
     * Handle products functionality with proper error handling.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'discount_condition_products');
    }

    /**
     * Handle categories functionality with proper error handling.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'discount_condition_categories');
    }

    /**
     * Handle scopeActive functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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
     * Handle scopeByOperator functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByOperator($query, string $operator)
    {
        return $query->where('operator', $operator);
    }

    /**
     * Handle scopeByPriority functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByPriority($query, string $direction = 'asc')
    {
        return $query->orderBy('priority', $direction);
    }

    /**
     * Handle getTranslatedNameAttribute functionality with proper error handling.
     */
    public function getTranslatedNameAttribute(): ?string
    {
        return $this->trans('name');
    }

    /**
     * Handle getTranslatedDescriptionAttribute functionality with proper error handling.
     */
    public function getTranslatedDescriptionAttribute(): ?string
    {
        return $this->trans('description');
    }

    /**
     * Handle matches functionality with proper error handling.
     *
     * @param  mixed  $testValue
     */
    public function matches($testValue): bool
    {
        if (! $this->is_active) {
            return false;
        }
        $conditionValue = $this->value;

        return match ($this->operator) {
            'equals_to' => $testValue == $conditionValue,
            'not_equals_to' => $testValue != $conditionValue,
            'less_than' => $testValue < $conditionValue,
            'greater_than' => $testValue > $conditionValue,
            'less_than_or_equal' => $testValue <= $conditionValue,
            'greater_than_or_equal' => $testValue >= $conditionValue,
            'starts_with' => str_starts_with((string) $testValue, (string) $conditionValue),
            'ends_with' => str_ends_with((string) $testValue, (string) $conditionValue),
            'contains' => str_contains((string) $testValue, (string) $conditionValue),
            'not_contains' => ! str_contains((string) $testValue, (string) $conditionValue),
            'in_array' => is_array($conditionValue) && in_array($testValue, $conditionValue),
            'not_in_array' => is_array($conditionValue) && ! in_array($testValue, $conditionValue),
            'regex' => preg_match($conditionValue, (string) $testValue),
            'not_regex' => ! preg_match($conditionValue, (string) $testValue),
            default => false,
        };
    }

    /**
     * Handle isValidForContext functionality with proper error handling.
     */
    public function isValidForContext(array $context = []): bool
    {
        if (! $this->is_active) {
            return false;
        }
        // Check if condition type is supported in context
        if (! isset($context[$this->type])) {
            return false;
        }

        return $this->matches($context[$this->type]);
    }

    /**
     * Handle getHumanReadableConditionAttribute functionality with proper error handling.
     */
    public function getHumanReadableConditionAttribute(): string
    {
        $typeLabel = $this->getTypeLabel();
        $operatorLabel = $this->getOperatorLabel();
        $value = is_array($this->value) ? implode(', ', $this->value) : $this->value;

        return "{$typeLabel} {$operatorLabel} {$value}";
    }

    /**
     * Handle getTypeLabel functionality with proper error handling.
     */
    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'product' => __('discount_conditions.types.product'),
            'category' => __('discount_conditions.types.category'),
            'brand' => __('discount_conditions.types.brand'),
            'collection' => __('discount_conditions.types.collection'),
            'attribute_value' => __('discount_conditions.types.attribute_value'),
            'cart_total' => __('discount_conditions.types.cart_total'),
            'item_qty' => __('discount_conditions.types.item_qty'),
            'channel' => __('discount_conditions.types.channel'),
            'currency' => __('discount_conditions.types.currency'),
            'customer_group' => __('discount_conditions.types.customer_group'),
            'user' => __('discount_conditions.types.user'),
            'partner_tier' => __('discount_conditions.types.partner_tier'),
            'first_order' => __('discount_conditions.types.first_order'),
            'day_time' => __('discount_conditions.types.day_time'),
            'custom_script' => __('discount_conditions.types.custom_script'),
            default => $this->type,
        };
    }

    /**
     * Handle getOperatorLabel functionality with proper error handling.
     */
    public function getOperatorLabel(): string
    {
        return match ($this->operator) {
            'equals_to' => __('discount_conditions.operators.equals_to'),
            'not_equals_to' => __('discount_conditions.operators.not_equals_to'),
            'less_than' => __('discount_conditions.operators.less_than'),
            'greater_than' => __('discount_conditions.operators.greater_than'),
            'less_than_or_equal' => __('discount_conditions.operators.less_than_or_equal'),
            'greater_than_or_equal' => __('discount_conditions.operators.greater_than_or_equal'),
            'starts_with' => __('discount_conditions.operators.starts_with'),
            'ends_with' => __('discount_conditions.operators.ends_with'),
            'contains' => __('discount_conditions.operators.contains'),
            'not_contains' => __('discount_conditions.operators.not_contains'),
            'in_array' => __('discount_conditions.operators.in_array'),
            'not_in_array' => __('discount_conditions.operators.not_in_array'),
            'regex' => __('discount_conditions.operators.regex'),
            'not_regex' => __('discount_conditions.operators.not_regex'),
            default => $this->operator,
        };
    }

    /**
     * Handle getTypes functionality with proper error handling.
     */
    public static function getTypes(): array
    {
        return ['product' => __('discount_conditions.types.product'), 'category' => __('discount_conditions.types.category'), 'brand' => __('discount_conditions.types.brand'), 'collection' => __('discount_conditions.types.collection'), 'attribute_value' => __('discount_conditions.types.attribute_value'), 'cart_total' => __('discount_conditions.types.cart_total'), 'item_qty' => __('discount_conditions.types.item_qty'), 'channel' => __('discount_conditions.types.channel'), 'currency' => __('discount_conditions.types.currency'), 'customer_group' => __('discount_conditions.types.customer_group'), 'user' => __('discount_conditions.types.user'), 'partner_tier' => __('discount_conditions.types.partner_tier'), 'first_order' => __('discount_conditions.types.first_order'), 'day_time' => __('discount_conditions.types.day_time'), 'custom_script' => __('discount_conditions.types.custom_script')];
    }

    /**
     * Handle getOperators functionality with proper error handling.
     */
    public static function getOperators(): array
    {
        return ['equals_to' => __('discount_conditions.operators.equals_to'), 'not_equals_to' => __('discount_conditions.operators.not_equals_to'), 'less_than' => __('discount_conditions.operators.less_than'), 'greater_than' => __('discount_conditions.operators.greater_than'), 'less_than_or_equal' => __('discount_conditions.operators.less_than_or_equal'), 'greater_than_or_equal' => __('discount_conditions.operators.greater_than_or_equal'), 'starts_with' => __('discount_conditions.operators.starts_with'), 'ends_with' => __('discount_conditions.operators.ends_with'), 'contains' => __('discount_conditions.operators.contains'), 'not_contains' => __('discount_conditions.operators.not_contains'), 'in_array' => __('discount_conditions.operators.in_array'), 'not_in_array' => __('discount_conditions.operators.not_in_array'), 'regex' => __('discount_conditions.operators.regex'), 'not_regex' => __('discount_conditions.operators.not_regex')];
    }

    /**
     * Handle getOperatorsForType functionality with proper error handling.
     */
    public static function getOperatorsForType(string $type): array
    {
        $numericOperators = ['equals_to' => __('discount_conditions.operators.equals_to'), 'not_equals_to' => __('discount_conditions.operators.not_equals_to'), 'less_than' => __('discount_conditions.operators.less_than'), 'greater_than' => __('discount_conditions.operators.greater_than'), 'less_than_or_equal' => __('discount_conditions.operators.less_than_or_equal'), 'greater_than_or_equal' => __('discount_conditions.operators.greater_than_or_equal')];
        $stringOperators = ['equals_to' => __('discount_conditions.operators.equals_to'), 'not_equals_to' => __('discount_conditions.operators.not_equals_to'), 'starts_with' => __('discount_conditions.operators.starts_with'), 'ends_with' => __('discount_conditions.operators.ends_with'), 'contains' => __('discount_conditions.operators.contains'), 'not_contains' => __('discount_conditions.operators.not_contains'), 'regex' => __('discount_conditions.operators.regex'), 'not_regex' => __('discount_conditions.operators.not_regex')];
        $arrayOperators = ['in_array' => __('discount_conditions.operators.in_array'), 'not_in_array' => __('discount_conditions.operators.not_in_array')];

        return match ($type) {
            'cart_total', 'item_qty', 'priority' => $numericOperators,
            'product', 'category', 'brand', 'collection', 'attribute_value', 'channel', 'currency', 'customer_group', 'user', 'partner_tier' => $stringOperators,
            'first_order', 'day_time' => array_merge($stringOperators, $arrayOperators),
            default => array_merge($numericOperators, $stringOperators, $arrayOperators),
        };
    }
}
