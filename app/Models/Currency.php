<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Translations\CurrencyTranslation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use InvalidArgumentException;

/**
 * Currency
 *
 * Eloquent model representing the Currency entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 * @property array $translatable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Currency newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency query()
 *
 * @mixin \Eloquent
 */
final class Currency extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'currencies';

    protected $fillable = [
        'name',
        'code',
        'symbol',
        'iso_code',
        'description',
        'exchange_rate',
        'base_currency',
        'decimal_places',
        'symbol_position',
        'thousands_separator',
        'decimal_separator',
        'is_active',
        'is_default',
        'is_enabled',
        'sort_order',
        'auto_update_rate',
    ];

    public array $translatable = ['name'];

    /**
     * Queue of translations that should be persisted once the model is saved.
     *
     * @var array<string, array<string, string>>
     */
    protected array $pendingTranslations = [];

    protected static function booted(): void
    {
        static::saved(function (self $currency): void {
            $currency->persistPendingTranslations();
        });
    }

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return [
            'exchange_rate' => 'float',
            'decimal_places' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'is_enabled' => 'boolean',
            'auto_update_rate' => 'boolean',
        ];
    }

    // Relationships

    /**
     * Translated attributes stored in the currency_translations table.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(CurrencyTranslation::class);
    }

    /**
     * Handle prices functionality with proper error handling.
     */
    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    /**
     * Handle orders functionality with proper error handling.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Handle countries functionality with proper error handling.
     */
    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'country_currencies');
    }

    /**
     * Handle priceLists functionality with proper error handling.
     */
    public function priceLists(): HasMany
    {
        return $this->hasMany(PriceList::class);
    }

    /**
     * Handle campaigns functionality with proper error handling.
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    /**
     * Handle discounts functionality with proper error handling.
     */
    public function discounts(): HasMany
    {
        return $this->hasMany(Discount::class);
    }

    // Scopes

    /**
     * Scope currencies that are enabled in the back office.
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope the default currency for storefront fallbacks.
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope currencies that are actively selectable.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // Accessors

    /**
     * Handle getFormattedSymbolAttribute functionality with proper error handling.
     */
    public function getFormattedSymbolAttribute(): string
    {
        return $this->symbol ?? $this->code;
    }

    /**
     * Handle getFormattedExchangeRateAttribute functionality with proper error handling.
     */
    public function getFormattedExchangeRateAttribute(): string
    {
        $decimalPlaces = $this->decimal_places ?? 2;
        $decimalSeparator = $this->decimal_separator ?? '.';
        $thousandsSeparator = $this->thousands_separator ?? ',';

        return number_format(
            $this->exchange_rate,
            $decimalPlaces,
            $decimalSeparator,
            $thousandsSeparator
        );
    }

    // Methods

    /**
     * Override attribute setter to support array assignment for translatable attributes.
     */
    public function setAttribute($key, $value)
    {
        if ($this->isTranslatableAttribute((string) $key) && is_array($value)) {
            return $this->setTranslations((string) $key, $value);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Persist a translation for the given locale.
     */
    public function setTranslation(string $key, string $locale, $value): self
    {
        $this->guardTranslatableKey($key);

        if ($value === null || $value === '') {
            unset($this->pendingTranslations[$key][$locale]);
            if ($this->exists) {
                $this->translations()->where('locale', $locale)->delete();
            }

            return $this;
        }

        $this->pendingTranslations[$key][$locale] = (string) $value;
        $this->applyBaseAttributeFromTranslations($key, $this->pendingTranslations[$key]);

        if ($this->exists) {
            $this->syncTranslation($key, $locale, (string) $value);
            unset($this->pendingTranslations[$key][$locale]);
        }

        return $this;
    }

    /**
     * Persist multiple translations for an attribute.
     *
     * @param array<string, string> $translations
     */
    public function setTranslations(string $key, array $translations): self
    {
        $this->guardTranslatableKey($key);

        $filtered = array_filter(
            $translations,
            static fn ($value): bool => $value !== null && $value !== ''
        );

        $this->pendingTranslations[$key] = array_map('strval', $filtered);

        $this->applyBaseAttributeFromTranslations($key, $this->pendingTranslations[$key]);

        return $this;
    }

    /**
     * Retrieve all translations for the provided attribute.
     *
     * @return array<string, string>
     */
    public function getTranslations(?string $key = null): array
    {
        if ($key === null) {
            $result = [];

            foreach ($this->translatable as $attribute) {
                $result[$attribute] = $this->getTranslations($attribute);
            }

            return $result;
        }

        $this->guardTranslatableKey($key);

        $this->loadMissing('translations');

        $translations = $this->translations
            ->mapWithKeys(fn (CurrencyTranslation $translation): array => [
                $translation->locale => (string) $translation->{$key},
            ])
            ->toArray();

        if (($this->pendingTranslations[$key] ?? []) !== []) {
            $translations = array_merge($translations, $this->pendingTranslations[$key]);
        }

        $fallbackLocale = config('app.fallback_locale') ?? config('app.locale');
        $baseValue = parent::getAttribute($key);

        if ($baseValue !== null && $baseValue !== '' && ! array_key_exists($fallbackLocale, $translations)) {
            $translations[$fallbackLocale] = (string) $baseValue;
        }

        return $translations;
    }

    /**
     * Retrieve the translation for a specific locale.
     */
    public function getTranslation(string $key, string $locale, bool $useFallbackLocale = true): ?string
    {
        $translations = $this->getTranslations($key);

        if (array_key_exists($locale, $translations)) {
            return $translations[$locale];
        }

        if ($useFallbackLocale) {
            $fallbackLocale = config('app.fallback_locale') ?? config('app.locale');

            if (array_key_exists($fallbackLocale, $translations)) {
                return $translations[$fallbackLocale];
            }

            $baseValue = parent::getAttribute($key);

            if ($baseValue !== null && $baseValue !== '') {
                return (string) $baseValue;
            }
        }

        return null;
    }

    /**
     * Determine if the provided attribute is translatable.
     */
    private function isTranslatableAttribute(string $key): bool
    {
        return in_array($key, $this->translatable, true);
    }

    /**
     * Guard against accessing non-translatable attributes.
     */
    private function guardTranslatableKey(string $key): void
    {
        if (! $this->isTranslatableAttribute($key)) {
            throw new InvalidArgumentException("The attribute [{$key}] is not translatable.");
        }
    }

    /**
     * Derive the base attribute value from the translation collection.
     *
     * @param array<string, string> $translations
     */
    private function applyBaseAttributeFromTranslations(string $key, array $translations): void
    {
        if ($translations === []) {
            return;
        }

        $preferredLocales = array_filter([
            config('app.locale'),
            config('app.fallback_locale'),
        ]);

        foreach ($preferredLocales as $preferredLocale) {
            if (isset($translations[$preferredLocale])) {
                parent::setAttribute($key, $translations[$preferredLocale]);

                return;
            }
        }

        $firstTranslation = reset($translations);

        if ($firstTranslation !== false) {
            parent::setAttribute($key, $firstTranslation);
        }
    }

    /**
     * Persist queued translations to the database.
     */
    private function persistPendingTranslations(): void
    {
        if ($this->pendingTranslations === [] || ! $this->exists) {
            return;
        }

        foreach ($this->pendingTranslations as $attribute => $translations) {
            foreach ($translations as $locale => $value) {
                $this->syncTranslation($attribute, $locale, $value);
            }
        }

        $this->pendingTranslations = [];
        $this->unsetRelation('translations');
        $this->load('translations');
    }

    /**
     * Sync a single translation record.
     */
    private function syncTranslation(string $key, string $locale, ?string $value): void
    {
        $column = $key;

        if ($value === null || $value === '') {
            $this->translations()->where('locale', $locale)->delete();

            return;
        }

        $this->translations()->updateOrCreate(
            ['locale' => $locale],
            [$column => $value]
        );
    }

    /**
     * Handle isDefault functionality with proper error handling.
     */
    public function isDefault(): bool
    {
        return $this->is_default;
    }

    /**
     * Handle isEnabled functionality with proper error handling.
     */
    public function isEnabled(): bool
    {
        return $this->is_enabled;
    }

    /**
     * Handle isActive functionality with proper error handling.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Handle formatAmount functionality with proper error handling.
     */
    public function formatAmount(float $amount): string
    {
        $decimalPlaces = $this->decimal_places ?? 2;
        $decimalSeparator = $this->decimal_separator ?? '.';
        $thousandsSeparator = $this->thousands_separator ?? ',';
        $formattedAmount = number_format($amount, $decimalPlaces, $decimalSeparator, $thousandsSeparator);

        if ($this->symbol) {
            $symbolPosition = $this->symbol_position ?? 'before';

            return $symbolPosition === 'before'
                ? sprintf('%s %s', $this->symbol, $formattedAmount)
                : sprintf('%s %s', $formattedAmount, $this->symbol);
        }

        return sprintf('%s %s', $formattedAmount, $this->code);
    }
}
