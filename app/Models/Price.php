<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Translations\PriceTranslation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;

/**
 * Price
 *
 * Eloquent model representing the Price entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Price newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Price newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Price query()
 *
 * @mixin \Eloquent
 */
final class Price extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'prices';

    protected $fillable = ['priceable_id', 'priceable_type', 'currency_id', 'amount', 'compare_amount', 'cost_amount', 'type', 'starts_at', 'ends_at', 'is_enabled', 'metadata'];

    /**
     * Handle casts functionality with proper error handling.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        // Guarantee that all monetary values preserve precision while dates remain Carbon instances.
        return [
            'amount'         => 'decimal:4',
            'compare_amount' => 'decimal:4',
            'cost_amount'    => 'decimal:4',
            'starts_at'      => 'datetime',
            'ends_at'        => 'datetime',
            'is_enabled'     => 'boolean',
            'metadata'       => 'array',
        ];
    }

    /**
     * Handle priceable functionality with proper error handling.
     *
     * @return MorphTo<Model, self>
     */
    public function priceable(): MorphTo
    {
        // Delegate to Laravel's morphTo helper to support products, variants, and other priceable entities.
        return $this->morphTo();
    }

    /**
     * Handle currency functionality with proper error handling.
     *
     * @return BelongsTo<Currency, self>
     */
    public function currency(): BelongsTo
    {
        // Connect each price to the currency record responsible for formatting and exchange rate handling.
        return $this->belongsTo(Currency::class);
    }

    /**
     * Expose the owning product when the price record represents a product-specific entry.
     *
     * @return BelongsTo<Product, self>
     */
    public function product(): BelongsTo
    {
        // Scope the relation to product-based prices so variant or service records
        // do not accidentally hydrate into the same association when traversing data.
        return $this
            ->belongsTo(Product::class, 'priceable_id')
            ->whereIn($this->qualifyColumn('priceable_type'), $this->productMorphTypes());
    }

    /**
     * Provide all morph type aliases recognised for product relations.
     *
     * @return list<string>
     */
    private function productMorphTypes(): array
    {
        // Capture potential aliases so legacy seeds that used base class names continue to resolve correctly.
        $aliases = [Product::class, (new Product)->getMorphClass(), class_basename(Product::class)];
        $aliases[] = strtolower(end($aliases));

        return array_values(array_unique(array_filter($aliases, static fn (string $value): bool => $value !== '')));
    }

    /**
     * Handle translations functionality with proper error handling.
     *
     * @return HasMany<PriceTranslation>
     */
    public function translations(): HasMany
    {
        // Expose localized labels and descriptions for back-office and storefront rendering.
        return $this->hasMany(PriceTranslation::class);
    }

    /**
     * Handle scopeEnabled functionality with proper error handling.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeEnabled(Builder $query): Builder
    {
        // Filter to prices explicitly flagged as enabled while leaving time-based
        // concerns to the dedicated `active()` scope that callers can append as
        // needed when narrowing to currently valid entries.
        return $query->where('is_enabled', true);
    }

    /**
     * Handle scopeActive functionality with proper error handling.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        // Keep the comparison moment consistent across the query evaluation.
        $now = now();

        return $query
            ->where('is_enabled', true)
            ->where(static function (Builder $builder) use ($now): void {
                // Allow records that have already started or do not have a start constraint.
                $builder->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(static function (Builder $builder) use ($now): void {
                // Allow records that have not yet ended or do not have an end constraint.
                $builder->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });
    }

    /**
     * Handle scopeForCurrency functionality with proper error handling.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeForCurrency(Builder $query, string $currencyCode): Builder
    {
        // Filter by ISO code without altering other scope combinations so the
        // caller retains control over enabled or active constraints.
        return $query->whereHas('currency', static function (Builder $builder) use ($currencyCode): void {
            $builder->where('code', $currencyCode);
        });
    }

    /**
     * Order price records by their translated name so admin grids remain predictable.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeOrderedByName(Builder $query, string $direction = 'asc', ?string $locale = null): Builder
    {
        // Normalise the direction for safety and fall back to the current locale when none is supplied.
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';
        $locale ??= app()->getLocale();

        $alias = 'price_ordering_translations';

        // Build a sub-query targeting the translated labels once to keep the join concise.
        $translationQuery = PriceTranslation::query()
            ->select(['price_id', 'name'])
            ->where('locale', $locale);

        $model = $query->getModel();

        // Join the translation data so ordering can happen on the localized label without duplicating rows.
        $query->leftJoinSub(
            $translationQuery,
            $alias,
            function (JoinClause $join) use ($alias, $model): void {
                $join->on("{$alias}.price_id", '=', $model->qualifyColumn('id'));
            }
        );

        return $query
            ->select($this->qualifyColumn('*'))
            ->orderBy("{$alias}.name", $direction)
            ->orderBy($this->qualifyColumn('id'));
    }

    /**
     * Handle isActive functionality with proper error handling.
     */
    public function isActive(): bool
    {
        if (! $this->is_enabled) {
            return false;
        }
        $now = now();
        if ($this->starts_at && $this->starts_at->gt($now)) {
            return false;
        }

        return ! ($this->ends_at && $this->ends_at->lt($now));
    }

    /**
     * Handle getDiscountPercentageAttribute functionality with proper error handling.
     */
    public function getDiscountPercentageAttribute(): ?int
    {
        if (! $this->compare_amount || $this->compare_amount <= $this->amount) {
            return null;
        }

        return (int) round(($this->compare_amount - $this->amount) / $this->compare_amount * 100);
    }

    // Translation methods
    /**
     * Handle getTranslatedName functionality with proper error handling.
     */
    public function getTranslatedName(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();
        // Resolve the translation while preferring already-loaded relationships to avoid additional queries.
        $translation = $this->resolveTranslation($locale);

        return $translation?->name;
    }

    /**
     * Handle getTranslatedDescription functionality with proper error handling.
     */
    public function getTranslatedDescription(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();
        // Resolve the translation while preferring already-loaded relationships to avoid additional queries.
        $translation = $this->resolveTranslation($locale);

        return $translation?->description;
    }

    /**
     * Resolve the translation for the requested locale with minimal query overhead.
     */
    private function resolveTranslation(?string $locale = null): ?PriceTranslation
    {
        // Default to the application locale when none is provided explicitly.
        $localeToUse = $locale ?: app()->getLocale();

        if ($this->relationLoaded('translations')) {
            /** @var Collection<int, PriceTranslation> $translations */
            $translations = $this->getRelation('translations');

            return $translations->firstWhere('locale', $localeToUse);
        }

        return $this->translations()->where('locale', $localeToUse)->first();
    }

    /**
     * Handle getTranslatedNotes functionality with proper error handling.
     */
    public function getTranslatedNotes(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();
        $translation = $this->translations()->where('locale', $locale)->first();

        return $translation?->notes;
    }

    // Scope for translated prices
    /**
     * Handle scopeWithTranslations functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeWithTranslations($query, ?string $locale = null)
    {
        $locale = $locale ?: app()->getLocale();

        return $query->with(['translations' => function ($q) use ($locale): void {
            $q->where('locale', $locale);
        }]);
    }

    // Get all available locales for this price
    /**
     * Handle getAvailableLocales functionality with proper error handling.
     */
    public function getAvailableLocales(): array
    {
        return $this->translations()->pluck('locale')->toArray();
    }

    // Check if price has translation for specific locale
    /**
     * Handle hasTranslationFor functionality with proper error handling.
     */
    public function hasTranslationFor(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->exists();
    }

    // Get or create translation for locale
    /**
     * Handle getOrCreateTranslation functionality with proper error handling.
     */
    public function getOrCreateTranslation(string $locale): \App\Models\Translations\PriceTranslation
    {
        return $this->translations()->firstOrCreate(['locale' => $locale], ['name' => null, 'description' => null, 'notes' => null]);
    }

    // Update translation for specific locale
    /**
     * Handle updateTranslation functionality with proper error handling.
     */
    public function updateTranslation(string $locale, array $data): bool
    {
        $translation = $this->getOrCreateTranslation($locale);

        return $translation->update($data);
    }

    // Delete translation for specific locale
    /**
     * Handle deleteTranslation functionality with proper error handling.
     */
    public function deleteTranslation(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->delete() > 0;
    }
}
