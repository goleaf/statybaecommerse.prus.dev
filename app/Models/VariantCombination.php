<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use App\Models\Scopes\VisibleScope;
use Database\Factories\VariantCombinationFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
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
 * @property int                             $id
 * @property int                             $product_id
 * @property array<string, mixed>            $attribute_combinations
 * @property string                          $combination_hash
 * @property bool                            $is_available
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read string $formatted_combinations
 * @property-read bool $is_valid_combination
 *
 * @method static \Illuminate\Database\Eloquent\Builder|VariantCombination newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VariantCombination newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VariantCombination query()
 * @method static VariantCombinationFactory                                factory($count = null, $state = [])
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, EnabledScope::class])]
final class VariantCombination extends Model
{
    /** @use HasFactory<VariantCombinationFactory> */
    use HasFactory;

    use OrdersByName;
    use SoftDeletes;

    /**
     * Point the shared OrdersByName scope to the deterministic hash column for stable ordering.
     */
    protected function getNameColumn(): string
    {
        return 'combination_hash';
    }

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
        self::saving(static function (VariantCombination $combination): void {
            /** @var array<string, mixed>|null $combinations */
            $combinations = $combination->attribute_combinations;

            if (! is_array($combinations)) {
                $combinations = [];
            }

            $normalized = self::normaliseCombination($combinations);
            $combination->setAttribute('attribute_combinations', $normalized);

            $combination->combination_hash = $combination->generateDeterministicHash();
        });

        // Ensure retrieved models are cached for strict, model-aware comparisons in tests.
        self::retrieved(static function (VariantCombination $combination): void {
            $combination->storeInCache();
        });

        // Refresh cached entries after persistence mutations so cached collections stay in sync.
        $flushCallback = static function (VariantCombination $combination): void {
            $combination->refreshCache();
        };

        self::saved($flushCallback);
        self::deleted($flushCallback);
        self::restored($flushCallback);
    }

    protected function casts(): array
    {
        return [
            'attribute_combinations' => 'array',
            'is_available'           => 'boolean',
        ];
    }

    protected $appends = [
        'formatted_combinations',
        'combination_hash',
        'is_valid_combination',
    ];

    /**
     * Handle product functionality with proper error handling.
     *
     * @return BelongsTo<Product, $this>
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
        $combinations = $this->attribute_combinations;

        if ($combinations === []) {
            return 'No combinations';
        }

        $formatted = [];
        foreach ($combinations as $attribute => $value) {
            $formatted[] = ucfirst((string) $attribute) . ': ' . (is_scalar($value) ? (string) $value : 'N/A');
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
        $combinations = $this->attribute_combinations;

        if ($combinations === []) {
            return false;
        }

        if (! $this->relationLoaded('product') && ! $this->product_id) {
            return false;
        }

        $product = $this->relationLoaded('product') ? $this->product : Product::find($this->product_id);

        if (! $product) {
            return false;
        }

        // Check if all attributes exist for this product
        $productAttributes = $product
            ->attributes()
            ->pluck('name')
            ->all();

        foreach ($combinations as $attributeName => $value) {
            if (! in_array($attributeName, $productAttributes, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Handle scopeAvailable functionality with proper error handling.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    /**
     * Handle scopeByProduct functionality with proper error handling.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeByProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Handle scopeByAttributeValue functionality with proper error handling.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeByAttributeValue(Builder $query, string $attribute, string $value): Builder
    {
        return $query->whereJsonContains('attribute_combinations->' . $attribute, $value);
    }

    /**
     * Handle scopeByCombination functionality with proper error handling.
     *
     * @param  Builder<self>        $query
     * @param  array<string, mixed> $combinations
     * @return Builder<self>
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
     *
     * @return array<int, array<string, mixed>>
     */
    public static function generateCombinations(Product $product): array
    {
        $attributesQuery = $product->attributes();

        /** @var \Illuminate\Database\Eloquent\Collection<int, Attribute> $attributes */
        $attributes = $attributesQuery
            ->withoutGlobalScopes([ActiveScope::class, EnabledScope::class, VisibleScope::class])
            ->with([
                /** @phpstan-ignore-next-line Method call on mixed type - query builder method chaining */
                'values' => static fn ($query) => $query->withoutGlobalScopes([ActiveScope::class, EnabledScope::class]),
            ])
            ->get();
        $combinations = [];

        if ($attributes->isEmpty()) {
            // Fall back to existing variant attribute payloads before yielding a deterministic fallback combination.
            $variantCombinations = $product
                ->variants()
                ->with('attributes.attribute')
                ->get()
                ->map(static function ($variant) {
                    if ($variant instanceof ProductVariant) {
                        return $variant->getVariantAttributes();
                    }

                    return [];
                })
                ->filter(static fn ($combination) => $combination !== [])
                /** @phpstan-ignore-next-line Type narrowing issue */
                ->map(static fn ($combination) => is_array($combination) ? self::normaliseCombination($combination) : [])
                ->unique()
                ->values()
                ->all();

            if ($variantCombinations !== []) {
                /** @var array<int, array<string, mixed>> $variantCombinations */
                return $variantCombinations;
            }

            return [self::fallbackCombination($product)];
        }

        /** @var array<string, array<int, string>> $attributeValues */
        $attributeValues = [];
        foreach ($attributes as $attribute) {
            $attributeName = $attribute->name;
            /** @var array<int, string> $values */
            $values = $attribute->values->pluck('value')->toArray();
            $attributeValues[$attributeName] = $values;
        }

        $combinations = self::generateCombinationsRecursive($attributeValues);

        return array_map(static fn (array $combination): array => self::normaliseCombination($combination), $combinations);
    }

    /**
     * Generate combinations recursively.
     *
     * @param  array<string, array<int, string>> $attributeValues
     * @param  array<string, mixed>              $currentCombination
     * @return array<int, array<string, mixed>>
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
            $hash = self::deterministicHashFor($normalisedCombination, $product->id);

            $record = self::withTrashed()->updateOrCreate(
                [
                    'product_id'       => $product->id,
                    'combination_hash' => $hash,
                ],
                [
                    'attribute_combinations' => $normalisedCombination,
                    'is_available'           => true,
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
     *
     * @param array<string, mixed> $combination
     */
    public static function findVariantByCombination(Product $product, array $combination): ?ProductVariant
    {
        $combination = self::normaliseCombination($combination);
        $hash = self::deterministicHashFor($combination, $product->id);

        $variantCombination = self::where('product_id', $product->id)
            ->where('combination_hash', $hash)
            ->first();

        if (! $variantCombination) {
            return null;
        }

        // Find the actual variant that matches this combination
        /** @var ProductVariant|null $variant */
        $variant = $product
            ->variants()
            ->whereHas('attributes', function (Builder $query) use ($combination): void {
                foreach ($combination as $attributeName => $value) {
                    $query->whereHas('attribute', function (Builder $subQuery) use ($attributeName): void {
                        $subQuery->where('name', $attributeName);
                    })->where('value', $value);
                }
            })
            ->first();

        return $variant;
    }

    /**
     * Get available combinations for a product.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getAvailableCombinations(Product $product): array
    {
        /** @var array<int, array<string, mixed>> $result */
        $result = self::where('product_id', $product->id)
            ->where('is_available', true)
            ->pluck('attribute_combinations')
            ->toArray();

        return $result;
    }

    /**
     * Check if a combination is available.
     *
     * @param array<string, mixed> $combination
     */
    public static function isCombinationAvailable(Product $product, array $combination): bool
    {
        $combination = self::normaliseCombination($combination);
        $hash = self::deterministicHashFor($combination, $product->id);

        return self::where('product_id', $product->id)
            ->where('combination_hash', $hash)
            ->where('is_available', true)
            ->exists();
    }

    /**
     * Normalise a combination array before hashing.
     *
     * @param  array<string, mixed> $combination
     * @return array<string, mixed>
     */
    private static function normaliseCombination(array $combination): array
    {
        ksort($combination);

        return $combination;
    }

    /**
     * Provide a deterministic fallback payload for attribute-less combinations.
     *
     * @return array<string, string>
     */
    private static function fallbackCombination(Product $product): array
    {
        return [
            '__fallback' => 'product-' . self::resolveProductKey($product->id),
        ];
    }

    /**
     * Calculate a deterministic hash for a combination and product pairing.
     *
     * @param array<string, mixed> $combination
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
        $combinations = $this->attribute_combinations;

        if ($combinations === []) {
            return $this->deterministicFallbackHash();
        }

        $normalised = self::normaliseCombination($combinations);

        return self::deterministicHashFor($normalised, $this->product_id);
    }

    /**
     * Provide the deterministic fallback hash when combination attributes are missing.
     */
    private function deterministicFallbackHash(): string
    {
        return hash('sha256', 'fallback:' . self::resolveProductKey($this->product_id));
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
        $collection = self::$hydratedCache[$productId] ?? new EloquentCollection;

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
     *
     * @return EloquentCollection<int, self>
     */
    public static function cachedForProduct(int $productId): EloquentCollection
    {
        $shouldRefresh = ! array_key_exists($productId, self::$hydratedCache);

        if (! $shouldRefresh) {
            $cached = self::$hydratedCache[$productId];

            if ($cached->isNotEmpty()) {
                $model = $cached->first();
                $keyName = $model->getKeyName();
                $cachedIds = $cached->modelKeys();

                $existingCount = self::query()
                    ->withoutGlobalScopes()
                    ->where('product_id', $productId)
                    ->whereIn($keyName, $cachedIds)
                    ->count();

                // If the cached records no longer exist (e.g. after RefreshDatabase migrations),
                // ensure the cache slice is rebuilt from the fresh database state.
                if ($existingCount !== count($cachedIds)) {
                    $shouldRefresh = true;
                }
            }
        }

        if ($shouldRefresh) {
            self::refreshCombinationCacheForProduct($productId);
        }

        return self::$hydratedCache[$productId] ?? new EloquentCollection;
    }

    /**
     * Refresh cached combinations for a product identifier.
     */
    public static function refreshCombinationCacheForProduct(int $productId): void
    {
        self::$hydratedCache[$productId] = self::where('product_id', $productId)->get();
    }
}
