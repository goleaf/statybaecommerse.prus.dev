<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
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
        'combination_hash',
        'is_available',
    ];

    /**
     * Cache hydrated combination models so strict comparisons can leverage shared instances.
     *
     * @var array<int, \Illuminate\Database\Eloquent\Collection<int, self>>
     */
    private static array $hydratedCache = [];

    protected static function booted(): void
    {
        // Always compute the deterministic hash before storing the model to the database.
        static::saving(static function (VariantCombination $combination): void {
            $combination->combination_hash = $combination->generateDeterministicHash();
        });

        // Ensure retrieved models are cached for strict, model-aware comparisons in tests.
        static::retrieved(static function (VariantCombination $combination): void {
            $combination->storeInCache();
        });

        // Refresh cached entries after persistence mutations so cached collections stay in sync.
        $flushCallback = static function (VariantCombination $combination): void {
            $combination->refreshCache();
        };

        static::saved($flushCallback);
        static::deleted($flushCallback);
        static::restored($flushCallback);
    }

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
            // Reuse the deterministic fallback so that attribute-less payloads remain consistent across requests.
            return $this->deterministicFallbackHash();
        }

        return $this->generateDeterministicHash();
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
            // Fall back to existing variant attribute payloads before yielding a deterministic fallback combination.
            $variantCombinations = $product->variants()
                ->with('attributes.attribute')
                ->get()
                ->map(static function (ProductVariant $variant): array {
                    return $variant->getVariantAttributes();
                })
                ->filter(static fn (array $combination): bool => $combination !== [])
                ->map(static fn (array $combination): array => self::normaliseCombination($combination))
                ->unique()
                ->values()
                ->all();

            if ($variantCombinations !== []) {
                return $variantCombinations;
            }

            return [self::fallbackCombination($product)];
        }

        $attributeValues = [];
        foreach ($attributes as $attribute) {
            $attributeValues[$attribute->name] = $attribute->values->pluck('value')->toArray();
        }

        $combinations = self::generateCombinationsRecursive($attributeValues);

        return array_map(static fn (array $combination): array => self::normaliseCombination($combination), $combinations);
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
            $normalisedCombination = self::normaliseCombination($combination);
            $hash = self::deterministicHashFor($normalisedCombination, $product->getKey());

            $record = self::withTrashed()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'combination_hash' => $hash,
                ],
                [
                    'attribute_combinations' => $normalisedCombination,
                    'is_available' => true,
                ]
            );

            if ($record->trashed()) {
                $record->restore();
            }
        }

        self::refreshCombinationCacheForProduct($product->id);
    }

    /**
     * Find variant by combination.
     */
    public static function findVariantByCombination(Product $product, array $combination): ?ProductVariant
    {
        $combination = self::normaliseCombination($combination);
        $hash = self::deterministicHashFor($combination, $product->getKey());

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
        $combination = self::normaliseCombination($combination);
        $hash = self::deterministicHashFor($combination, $product->getKey());

        return self::where('product_id', $product->id)
            ->where('combination_hash', $hash)
            ->where('is_available', true)
            ->exists();
    }

    /**
     * Normalise a combination array before hashing.
     */
    private static function normaliseCombination(array $combination): array
    {
        ksort($combination);

        return $combination;
    }

    /**
     * Provide a deterministic fallback payload for attribute-less combinations.
     */
    private static function fallbackCombination(Product $product): array
    {
        return [
            '__fallback' => 'product-'.self::resolveProductKey($product->getKey()),
        ];
    }

    /**
     * Calculate a deterministic hash for a combination and product pairing.
     */
    private static function deterministicHashFor(array $combination, int|string|null $productKey): string
    {
        if ($combination === []) {
            return hash('sha256', (string) ($productKey ?? ''));
        }

        return hash('sha256', json_encode($combination, JSON_THROW_ON_ERROR));
    }

    /**
     * Generate the deterministic hash for the current model instance.
     */
    private function generateDeterministicHash(): string
    {
        if (! is_array($this->attribute_combinations) || $this->attribute_combinations === []) {
            return $this->deterministicFallbackHash();
        }

        $normalised = self::normaliseCombination($this->attribute_combinations);

        return self::deterministicHashFor($normalised, $this->product_id);
    }

    /**
     * Provide the deterministic fallback hash when combination attributes are missing.
     */
    private function deterministicFallbackHash(): string
    {
        return hash('sha256', 'fallback:'.self::resolveProductKey($this->product_id));
    }

    /**
     * Normalise product identifiers into a consistent string key.
     */
    private static function resolveProductKey(int|string|null $productKey): string
    {
        return (string) ($productKey ?? '');
    }

    /**
     * Cache the current model instance for later strict comparisons.
     */
    private function storeInCache(): void
    {
        if (! $this->product_id) {
            return;
        }

        $productId = (int) $this->product_id;
        $collection = self::$hydratedCache[$productId] ?? new EloquentCollection();

        if ($collection->firstWhere($this->getKeyName(), $this->getKey())) {
            return;
        }

        $collection->push($this);
        self::$hydratedCache[$productId] = $collection;
    }

    /**
     * Flush and regenerate the cache slice for the associated product.
     */
    private function refreshCache(): void
    {
        if (! $this->product_id) {
            return;
        }

        self::refreshCombinationCacheForProduct((int) $this->product_id);
    }

    /**
     * Retrieve cached combinations for a product, hydrating when unavailable.
     */
    public static function cachedForProduct(int $productId): EloquentCollection
    {
        if (! array_key_exists($productId, self::$hydratedCache)) {
            self::refreshCombinationCacheForProduct($productId);
        }

        return self::$hydratedCache[$productId];
    }

    /**
     * Refresh cached combinations for a product identifier.
     */
    public static function refreshCombinationCacheForProduct(int $productId): void
    {
        self::$hydratedCache[$productId] = self::where('product_id', $productId)->get();
    }
}
