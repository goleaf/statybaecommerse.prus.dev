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
 * VariantCombination
 *
 * Eloquent model representing the VariantCombination entity for managing variant combinations.
 *
 * @property mixed $table
 * @property mixed $fillable
 * @property mixed $casts
 * @property mixed $appends
 *
 * @method static \Illuminate\Database\Eloquent\Builder|VariantCombination newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VariantCombination newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VariantCombination query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, EnabledScope::class])]
final class VariantCombination extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'variant_combinations';

    protected $fillable = [
        'product_id',
        'attribute_combinations',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'attribute_combinations' => 'array',
            'is_available' => 'boolean',
        ];
    }

    protected $appends = [
        'formatted_combinations',
        'combination_hash',
        'is_valid_combination',
    ];

    /**
     * Handle product functionality with proper error handling.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Handle getFormattedCombinationsAttribute functionality with proper error handling.
     */
    public function getFormattedCombinationsAttribute(): string
    {
        if (! $this->attribute_combinations) {
            return 'No combinations';
        }

        $formatted = [];
        foreach ($this->attribute_combinations as $attribute => $value) {
            $formatted[] = ucfirst($attribute).': '.$value;
        }

        return implode(', ', $formatted);
    }

    /**
     * Handle getCombinationHashAttribute functionality with proper error handling.
     */
    public function getCombinationHashAttribute(): string
    {
        if (! $this->attribute_combinations) {
            return '';
        }

        ksort($this->attribute_combinations);

        return md5(json_encode($this->attribute_combinations));
    }

    /**
     * Handle getIsValidCombinationAttribute functionality with proper error handling.
     */
    public function getIsValidCombinationAttribute(): bool
    {
        if (! $this->attribute_combinations || ! $this->product) {
            return false;
        }

        // Check if all attributes exist for this product
        $productAttributes = $this->product->attributes()->pluck('name', 'id')->toArray();

        foreach ($this->attribute_combinations as $attributeName => $value) {
            if (! in_array($attributeName, $productAttributes)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Handle scopeAvailable functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Handle scopeByProduct functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Handle scopeByAttributeValue functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByAttributeValue($query, string $attribute, string $value)
    {
        return $query->whereJsonContains('attribute_combinations->'.$attribute, $value);
    }

    /**
     * Handle scopeByCombination functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByCombination($query, array $combinations)
    {
        foreach ($combinations as $attribute => $value) {
            $query->whereJsonContains('attribute_combinations->'.$attribute, $value);
        }

        return $query;
    }

    /**
     * Generate all possible combinations for a product.
     */
    public static function generateCombinations(Product $product): array
    {
        $attributes = $product->attributes()->with('values')->get();
        $combinations = [];

        if ($attributes->isEmpty()) {
            return $combinations;
        }

        $attributeValues = [];
        foreach ($attributes as $attribute) {
            $attributeValues[$attribute->name] = $attribute->values->pluck('value')->toArray();
        }

        $combinations = self::generateCombinationsRecursive($attributeValues);

        return $combinations;
    }

    /**
     * Generate combinations recursively.
     */
    private static function generateCombinationsRecursive(array $attributeValues, array $currentCombination = [], int $depth = 0): array
    {
        $keys = array_keys($attributeValues);

        if ($depth >= count($keys)) {
            return [$currentCombination];
        }

        $currentKey = $keys[$depth];
        $combinations = [];

        foreach ($attributeValues[$currentKey] as $value) {
            $newCombination = $currentCombination;
            $newCombination[$currentKey] = $value;

            $combinations = array_merge(
                $combinations,
                self::generateCombinationsRecursive($attributeValues, $newCombination, $depth + 1)
            );
        }

        return $combinations;
    }

    /**
     * Create or update combinations for a product.
     */
    public static function createCombinationsForProduct(Product $product): void
    {
        $combinations = self::generateCombinations($product);

        foreach ($combinations as $combination) {
            $hash = md5(json_encode($combination));

            self::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'combination_hash' => $hash,
                ],
                [
                    'attribute_combinations' => $combination,
                    'is_available' => true,
                ]
            );
        }
    }

    /**
     * Find variant by combination.
     */
    public static function findVariantByCombination(Product $product, array $combination): ?ProductVariant
    {
        $hash = md5(json_encode($combination));

        $variantCombination = self::where('product_id', $product->id)
            ->where('combination_hash', $hash)
            ->first();

        if (! $variantCombination) {
            return null;
        }

        // Find the actual variant that matches this combination
        return $product->variants()
            ->whereHas('attributes', function ($query) use ($combination) {
                foreach ($combination as $attributeName => $value) {
                    $query->whereHas('attribute', function ($subQuery) use ($attributeName) {
                        $subQuery->where('name', $attributeName);
                    })->where('value', $value);
                }
            })
            ->first();
    }

    /**
     * Get available combinations for a product.
     */
    public static function getAvailableCombinations(Product $product): array
    {
        return self::where('product_id', $product->id)
            ->where('is_available', true)
            ->get()
            ->pluck('attribute_combinations')
            ->toArray();
    }

    /**
     * Check if a combination is available.
     */
    public static function isCombinationAvailable(Product $product, array $combination): bool
    {
        $hash = md5(json_encode($combination));

        return self::where('product_id', $product->id)
            ->where('combination_hash', $hash)
            ->where('is_available', true)
            ->exists();
    }
}
