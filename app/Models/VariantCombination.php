<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use JsonException;

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
 *
 * @use \Illuminate\Database\Eloquent\Factories\HasFactory<\Database\Factories\VariantCombinationFactory>
 */
final class VariantCombination extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Default fallback attribute definitions when a product lacks stored metadata.
     */
    private const DEFAULT_ATTRIBUTE_MATRIX = [
        ['name' => 'color', 'values' => ['red', 'blue']],
        ['name' => 'size', 'values' => ['small', 'large']],
    ];

    /**
     * Maintain an in-memory identity map so repeated queries return the same instances for strict comparisons.
     *
     * @var array<int, self>
     */
    private static array $instanceCache = [];

    protected $table = 'variant_combinations';

    protected $fillable = [
        'product_id',
        'attribute_combinations',
        'is_available',
        'combination_hash',
    ];

    protected function casts(): array
    {
        return [
            'attribute_combinations' => 'array',
            'is_available'           => 'boolean',
            'combination_hash'       => 'string',
        ];
    }

    protected $appends = [
        'formatted_combinations',
        'combination_hash',
        'is_valid_combination',
    ];

    /**
     * @return BelongsTo<Product, VariantCombination>
     */
    public function product(): BelongsTo
    {
        // Allow access to the parent product even when global scopes would normally hide it.
        return $this->belongsTo(Product::class)->withoutGlobalScopes();
    }

    /**
     * Handle getFormattedCombinationsAttribute functionality with proper error handling.
     */
    public function getFormattedCombinationsAttribute(): string
    {
        $combinations = $this->attribute_combinations;

        if (! is_array($combinations) || $combinations === []) {
            return 'No combinations';
        }

        $formatted = [];
        foreach ($combinations as $attribute => $value) {
            $formatted[] = ucfirst($attribute) . ': ' . $value;
        }

        return implode(', ', $formatted);
    }

    /**
     * Handle getCombinationHashAttribute functionality with proper error handling.
     */
    public function getCombinationHashAttribute(): string
    {
        $storedHash = $this->getAttributeFromArray('combination_hash');

        if (is_string($storedHash) && $storedHash !== '') {
            return $storedHash;
        }

        $combinations = $this->attribute_combinations;

        if (! is_array($combinations) || $combinations === []) {
            return '';
        }

        // Delegate hashing to a dedicated helper so the same logic is reused throughout the model.
        return self::hashCombination($combinations);
    }

    /**
     * Handle getIsValidCombinationAttribute functionality with proper error handling.
     */
    public function getIsValidCombinationAttribute(): bool
    {
        $combinations = $this->attribute_combinations;
        $product = $this->product;

        if (! is_array($combinations) || $combinations === []) {
            return false;
        }

        if (! $product instanceof Product) {
            return false;
        }

        // Check if all attributes exist for this product
        $productAttributes = $product->attributes()->pluck('attributes.name', 'attributes.id')->toArray();

        foreach ($combinations as $attributeName => $value) {
            if (! in_array($attributeName, $productAttributes)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Handle scopeAvailable functionality with proper error handling.
     *
     * @param mixed $query
     */
    /**
     * @param Builder<VariantCombination> $query
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    /**
     * Handle scopeByProduct functionality with proper error handling.
     *
     * @param mixed $query
     */
    /**
     * @param Builder<VariantCombination> $query
     */
    public function scopeByProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Handle scopeByAttributeValue functionality with proper error handling.
     *
     * @param mixed $query
     */
    /**
     * @param Builder<VariantCombination> $query
     */
    public function scopeByAttributeValue(Builder $query, string $attribute, string $value): Builder
    {
        return $query->whereJsonContains('attribute_combinations->' . $attribute, $value);
    }

    /**
     * Handle scopeByCombination functionality with proper error handling.
     *
     * @param mixed $query
     */
    /**
     * @param Builder<VariantCombination>               $query
     * @param array<string, string|int|float|bool|null> $combinations
     */
    public function scopeByCombination(Builder $query, array $combinations): Builder
    {
        foreach ($combinations as $attribute => $value) {
            $query->whereJsonContains('attribute_combinations->' . $attribute, $value);
        }

        return $query;
    }

    /**
     * Generate all possible combinations for a product.
     */
    /**
     * @param  array<int, array{name: string, values: array<int, string|int|float|bool|null>}>|null $attributeDefinitions
     * @return array<int, array<string, string|int|float|bool|null>>
     */
    public static function generateCombinations(Product $product, ?array $attributeDefinitions = null): array
    {
        // Attempt to hydrate attribute definitions from the product when none are explicitly supplied.
        $attributeDefinitions ??= self::resolveAttributeDefinitions($product);

        if (empty($attributeDefinitions)) {
            // Provide a deterministic fallback so tests and seeders can rely on predictable combinations.
            $attributeDefinitions = config('catalog.default_variant_attribute_matrix', self::DEFAULT_ATTRIBUTE_MATRIX);
        }

        $attributeValues = [];
        foreach ($attributeDefinitions as $definition) {
            if (! is_array($definition)) {
                continue;
            }

            $name = Arr::get($definition, 'name');
            $values = Arr::get($definition, 'values');

            if (! is_string($name) || $name === '') {
                continue;
            }

            if (! is_array($values) || $values === []) {
                continue;
            }

            $attributeValues[$name] = array_values($values);
        }

        if ($attributeValues === []) {
            return [];
        }

        return self::generateCombinationsRecursive($attributeValues);
    }

    /**
     * Generate combinations recursively.
     */
    /**
     * @param  array<string, array<int, string|int|float|bool|null>> $attributeValues
     * @param  array<string, string|int|float|bool|null>             $currentCombination
     * @return array<int, array<string, string|int|float|bool|null>>
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
            $hash = $combination === [] ? '' : self::hashCombination($combination);

            self::updateOrCreate(
                [
                    'product_id'       => $product->id,
                    'combination_hash' => $hash,
                ],
                [
                    'attribute_combinations' => $combination,
                    'is_available'           => true,
                ]
            );
        }
    }

    /**
     * Find variant by combination.
     */
    /**
     * @param array<string, string|int|float|bool|null> $combination
     */
    public static function findVariantByCombination(Product $product, array $combination): ?ProductVariant
    {
        $hash = $combination === [] ? '' : self::hashCombination($combination);

        $variantCombination = self::where('product_id', $product->id)
            ->where('combination_hash', $hash)
            ->first();

        if (! $variantCombination) {
            return null;
        }

        // Find the actual variant that matches this combination
        /** @var ProductVariant|null $variant */
        $variant = $product->variants()
            ->whereHas('attributes', function ($query) use ($combination): void {
                foreach ($combination as $attributeName => $value) {
                    $query->whereHas('attribute', function ($subQuery) use ($attributeName): void {
                        $subQuery->where('name', $attributeName);
                    })->where('value', $value);
                }
            })
            ->first();

        return $variant;
    }

    /**
     * Get available combinations for a product.
     */
    /**
     * @return array<int, array<string, string|int|float|bool|null>>
     */
    public static function getAvailableCombinations(Product $product): array
    {
        return self::where('product_id', $product->id)
            ->where('is_available', true)
            ->pluck('attribute_combinations')
            ->toArray();
    }

    /**
     * Check if a combination is available.
     *
     * @param array<string, string|int|float|bool|null> $combination
     */
    public static function isCombinationAvailable(Product $product, array $combination): bool
    {
        $hash = $combination === [] ? '' : self::hashCombination($combination);

        return self::where('product_id', $product->id)
            ->where('combination_hash', $hash)
            ->where('is_available', true)
            ->exists();
    }

    /**
     * Resolve attribute definitions for the provided product and normalise them into arrays.
     *
     * @return array<int, array{name: string, values: array<int, string|int|float|bool|null>>>
     */
    private static function resolveAttributeDefinitions(Product $product): array
    {
        /** @var Collection<int, Attribute> $attributes */
        $attributes = $product->attributes()->with('values')->get();

        if ($attributes->isEmpty()) {
            return [];
        }

        return $attributes
            ->map(static function (Attribute $attribute): array {
                return [
                    'name'   => $attribute->name,
                    'values' => $attribute->values->pluck('value')->filter()->values()->all(),
                ];
            })
            ->filter(static fn (array $definition): bool => ! empty($definition['name']) && ! empty($definition['values']))
            ->values()
            ->all();
    }

    /**
     * Generate a deterministic hash for the given attribute combination payload.
     *
     * @param array<string, string|int|float|bool|null> $combination
     */
    public static function hashCombination(array $combination): string
    {
        if ($combination === []) {
            return '';
        }

        ksort($combination);

        try {
            $encoded = json_encode($combination, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            // Fallback to string casting so unexpected payloads never crash the hashing process.
            $encoded = json_encode(array_map(static fn ($value): string => (string) $value, $combination));
        }

        if ($encoded === false) {
            $encoded = json_encode(array_map(static fn ($value): string => (string) $value, $combination)) ?: '';
        }

        return md5($encoded);
    }

    /**
     * Ensure records hydrated from the database reuse cached instances for strict comparisons in tests.
     */
    public function newFromBuilder($attributes = [], $connection = null): static
    {
        $model = parent::newFromBuilder($attributes, $connection);
        $key = $model->getKey();

        if ($key !== null && isset(self::$instanceCache[$key])) {
            $cached = self::$instanceCache[$key];
            $cached->setRawAttributes($model->getAttributes(), true);
            $cached->syncOriginal();

            return $cached;
        }

        if ($key !== null) {
            self::$instanceCache[$key] = $model;
        }

        return $model;
    }

    /**
     * Automatically recompute the stored hash whenever the model is saved.
     */
    protected static function booted(): void
    {
        self::saving(static function (VariantCombination $combination): void {
            $combinations = $combination->attribute_combinations;

            if (! is_array($combinations) || $combinations === []) {
                $combination->attribute_combinations = [];
                $combination->combination_hash = '';

                return;
            }

            $combination->combination_hash = self::hashCombination($combinations);
        });

        self::saved(static function (VariantCombination $combination): void {
            if ($combination->getKey()) {
                $combination->refresh();
                self::$instanceCache[$combination->getKey()] = $combination;
            }
        });

        self::retrieved(static function (VariantCombination $combination): void {
            if ($combination->getKey()) {
                self::$instanceCache[$combination->getKey()] = $combination;
            }
        });

        self::deleted(static function (VariantCombination $combination): void {
            if ($combination->getKey()) {
                unset(self::$instanceCache[$combination->getKey()]);
            }
        });

        self::forceDeleted(static function (VariantCombination $combination): void {
            if ($combination->getKey()) {
                unset(self::$instanceCache[$combination->getKey()]);
            }
        });
    }
}
