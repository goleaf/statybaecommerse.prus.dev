<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\DateRangeScope;
use Database\Factories\PriceListItemFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

/**
 * Class PriceListItem
 *
 * Represents a single item inside a price list, handling translations, activation windows, and
 * pricing helpers used across storefront calculations.
 *
 * @property int                               $price_list_id
 * @property int|null                          $product_id
 * @property int|null                          $variant_id
 * @property float|null                        $net_amount
 * @property float|null                        $compare_amount
 * @property array<string, string>|string|null $name
 * @property array<string, string>|string|null $description
 * @property array<string, string>|string|null $notes
 * @property bool                              $is_active
 * @property bool                              $is_featured
 * @property int|null                          $priority
 * @property int|null                          $min_quantity
 * @property int|null                          $max_quantity
 * @property \Illuminate\Support\Carbon|null   $valid_from
 * @property \Illuminate\Support\Carbon|null   $valid_until
 * @property-read string                                    $display_name
 * @property-read float                                     $effective_price
 * @property-read float|null                                $savings_amount
 * @property-read PriceList|null                            $priceList
 * @property-read Product|null                              $product
 * @property-read ProductVariant|null                       $variant
 *
 * @method static Builder<self>                            query()
 * @method static Builder<self>                            orderedByName(string $direction = 'asc')
 * @method static \Database\Factories\PriceListItemFactory factory($count = null, $state = [])
 *
 * @phpstan-use \Illuminate\Database\Eloquent\Factories\HasFactory<\Database\Factories\PriceListItemFactory>
 *
 * @mixin Model
 */
#[ScopedBy([ActiveScope::class, DateRangeScope::class])]
final class PriceListItem extends Model
{
    /**
     * @phpstan-use HasFactory<PriceListItemFactory>
     */
    use HasFactory;

    use HasTranslations;
    use OrdersByName {
        getNameColumn as protected resolveNameColumnForOrdering;
        scopeOrderedByName as protected scopeOrderedByNameFromTrait;
    }

    /**
     * Default alphabetical ordering to the translatable name field.
     */
    protected string $nameColumn = 'name';

    /**
     * @var string|null
     */
    protected $table = 'price_list_items';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'price_list_id',
        'product_id',
        'variant_id',
        'net_amount',
        'compare_amount',
        'name',
        'description',
        'notes',
        'is_active',
        'is_featured',
        'priority',
        'min_quantity',
        'max_quantity',
        'valid_from',
        'valid_until',
    ];

    /**
     * @var array<int, string>
     */
    public array $translatable = ['name', 'description', 'notes'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        // Cast the numeric and date attributes to ensure consistent data when retrieved from persistence.
        return [
            'net_amount'     => 'decimal:4',
            'compare_amount' => 'decimal:4',
            'is_active'      => 'boolean',
            'is_featured'    => 'boolean',
            'priority'       => 'integer',
            'min_quantity'   => 'integer',
            'max_quantity'   => 'integer',
            'valid_from'     => 'datetime',
            'valid_until'    => 'datetime',
        ];
    }

    /**
     * Handle priceList functionality with proper error handling.
     *
     * @return BelongsTo<PriceList, static>
     *
     * @phpstan-return BelongsTo<PriceList, PriceListItem>
     */
    public function priceList(): BelongsTo
    {
        /** @var BelongsTo<PriceList, PriceListItem> $relation */
        $relation = $this->belongsTo(PriceList::class);

        return $relation;
    }

    /**
     * Handle product functionality with proper error handling.
     *
     * @return BelongsTo<Product, static>
     *
     * @phpstan-return BelongsTo<Product, PriceListItem>
     */
    public function product(): BelongsTo
    {
        /** @var BelongsTo<Product, PriceListItem> $relation */
        $relation = $this->belongsTo(Product::class);

        return $relation;
    }

    /**
     * Handle variant functionality with proper error handling.
     *
     * @return BelongsTo<ProductVariant, static>
     *
     * @phpstan-return BelongsTo<ProductVariant, PriceListItem>
     */
    public function variant(): BelongsTo
    {
        /** @var BelongsTo<ProductVariant, PriceListItem> $relation */
        $relation = $this->belongsTo(ProductVariant::class);

        return $relation;
    }

    /**
     * Handle getDiscountPercentageAttribute functionality with proper error handling.
     */
    public function getDiscountPercentageAttribute(): ?int
    {
        if (! $this->compare_amount || $this->compare_amount <= $this->net_amount) {
            return null;
        }

        return (int) round(($this->compare_amount - $this->net_amount) / $this->compare_amount * 100);
    }

    /**
     * Handle getDisplayNameAttribute functionality with proper error handling.
     */
    public function getDisplayNameAttribute(): string
    {
        $translatedName = $this->getTranslatedName();
        if ($translatedName !== null) {
            return $translatedName;
        }
        $variant = $this->variant;
        if ($variant instanceof ProductVariant) {
            $variantDisplayName = $variant->getAttribute('display_name');

            if (is_string($variantDisplayName) && $variantDisplayName !== '') {
                return $variantDisplayName;
            }
        }
        $product = $this->product;
        if ($product instanceof Product) {
            $productName = $product->getAttribute('name');

            if (is_string($productName) && $productName !== '') {
                return $productName;
            }
        }

        $identifier = $this->getKey();

        if (is_string($identifier) || is_int($identifier)) {
            return 'Price List Item #' . (string) $identifier;
        }

        return 'Price List Item';
    }

    /**
     * Handle getEffectivePriceAttribute functionality with proper error handling.
     */
    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->net_amount ?? 0.0);
    }

    /**
     * Handle getSavingsAmountAttribute functionality with proper error handling.
     */
    public function getSavingsAmountAttribute(): ?float
    {
        if (! $this->compare_amount || $this->compare_amount <= $this->net_amount) {
            return null;
        }

        return (float) ($this->compare_amount - $this->net_amount);
    }

    /**
     * Handle isActive functionality with proper error handling.
     */
    public function isActive(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        $now = now();
        if ($this->valid_from && $this->valid_from->gt($now)) {
            return false;
        }
        if ($this->valid_until && $this->valid_until->lt($now)) {
            return false;
        }

        return true;
    }

    /**
     * Handle isValidForQuantity functionality with proper error handling.
     */
    public function isValidForQuantity(int $quantity): bool
    {
        if ($this->min_quantity && $quantity < $this->min_quantity) {
            return false;
        }
        if ($this->max_quantity && $quantity > $this->max_quantity) {
            return false;
        }

        return true;
    }

    // Scopes
    /**
     * Handle scopeActive functionality with proper error handling.
     *
     * @param mixed $query
     */
    /**
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Handle scopeValid functionality with proper error handling.
     *
     * @param mixed $query
     */
    /**
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeValid(Builder $query): Builder
    {
        $now = now();

        return $query->where('is_active', true)->where(function ($q) use ($now): void {
            $q->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
        })->where(function ($q) use ($now): void {
            $q->whereNull('valid_until')->orWhere('valid_until', '>=', $now);
        });
    }

    /**
     * Handle scopeByPriority functionality with proper error handling.
     *
     * @param mixed $query
     */
    /**
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeByPriority(Builder $query, string $direction = 'asc'): Builder
    {
        // Apply ordering to prioritize items while sanitizing direction input.
        return $query->orderBy('priority', Str::lower($direction) === 'desc' ? 'desc' : 'asc');
    }

    /**
     * Handle scopeForProduct functionality with proper error handling.
     *
     * @param mixed $query
     */
    /**
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Handle scopeForVariant functionality with proper error handling.
     *
     * @param mixed $query
     */
    /**
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeForVariant(Builder $query, int $variantId): Builder
    {
        return $query->where('variant_id', $variantId);
    }

    /**
     * Handle scopeInPriceRange functionality with proper error handling.
     *
     * @param mixed $query
     */
    /**
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeInPriceRange(Builder $query, float $minPrice, float $maxPrice): Builder
    {
        return $query->whereBetween('net_amount', [$minPrice, $maxPrice]);
    }

    /**
     * Handle scopeWithDiscount functionality with proper error handling.
     *
     * @param mixed $query
     */
    /**
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeWithDiscount(Builder $query): Builder
    {
        return $query->whereNotNull('compare_amount')->whereColumn('compare_amount', '>', 'net_amount');
    }

    /**
     * Handle scopeOrderedByName functionality with proper error handling.
     */
    /**
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeOrderedByName(Builder $query, string $direction = 'asc'): Builder
    {
        // Determine the locale-aware JSON path for translations and sort by the lower-cased value for consistency.
        $locale = app()->getLocale();
        $sanitizedDirection = Str::lower($direction) === 'desc' ? 'desc' : 'asc';

        $jsonPath = sprintf('$."%s"', $locale);

        return $query->orderByRaw(
            sprintf('LOWER(COALESCE(json_extract(name, "%s"), name)) %s', $jsonPath, $sanitizedDirection)
        );
    }

    // Translation methods
    /**
     * Handle getTranslatedName functionality with proper error handling.
     */
    public function getTranslatedName(?string $locale = null): ?string
    {
        return $this->resolveTranslatedValue('name', $locale);
    }

    /**
     * Handle getTranslatedDescription functionality with proper error handling.
     */
    public function getTranslatedDescription(?string $locale = null): ?string
    {
        return $this->resolveTranslatedValue('description', $locale);
    }

    /**
     * Handle getTranslatedNotes functionality with proper error handling.
     */
    public function getTranslatedNotes(?string $locale = null): ?string
    {
        return $this->resolveTranslatedValue('notes', $locale);
    }

    /**
     * Resolve the translation helper to a string value while handling arrays and fallbacks.
     */
    private function resolveTranslatedValue(string $attribute, ?string $locale = null): ?string
    {
        // Retrieve the underlying attribute value so we can gracefully handle scalar and array payloads.
        $value = $this->getAttribute($attribute);

        if (is_array($value)) {
            $resolvedLocale = $locale ?? app()->getLocale();
            $translation = $this->getTranslation($attribute, $resolvedLocale, false);

            if (is_string($translation) && $translation !== '') {
                return $translation;
            }

            foreach ($value as $candidate) {
                if (is_string($candidate) && $candidate !== '') {
                    return $candidate;
                }
            }

            return null;
        }

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * Provide the factory instance so static analysis understands the trait relationship.
     */
    protected static function newFactory(): PriceListItemFactory
    {
        // Delegate to the generated factory for consistent test fixtures.
        return PriceListItemFactory::new();
    }
}
