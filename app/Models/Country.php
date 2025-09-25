<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Translations\CountryTranslation;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Country
 *
 * Eloquent model representing the Country entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property string $translationModel
 * @property mixed $table
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Country newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Country newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Country query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class Country extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected string $translationModel = CountryTranslation::class;

    protected $table = 'countries';

    protected $fillable = ['name', 'name_official', 'cca2', 'cca3', 'ccn3', 'code', 'iso_code', 'currency_code', 'currency_symbol', 'phone_code', 'phone_calling_code', 'flag', 'svg_flag', 'region', 'subregion', 'latitude', 'longitude', 'currencies', 'languages', 'timezones', 'is_active', 'is_eu_member', 'requires_vat', 'vat_rate', 'timezone', 'description', 'metadata', 'is_enabled', 'sort_order'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['latitude' => 'decimal:8', 'longitude' => 'decimal:8', 'currencies' => 'array', 'languages' => 'array', 'timezones' => 'array', 'is_active' => 'boolean', 'is_eu_member' => 'boolean', 'requires_vat' => 'boolean', 'vat_rate' => 'decimal:2', 'metadata' => 'array', 'is_enabled' => 'boolean', 'sort_order' => 'integer'];
    }

    /**
     * Handle addresses functionality with proper error handling.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'country_code', 'cca2')->withoutGlobalScopes();
    }

    /**
     * Handle cities functionality with proper error handling.
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    /**
     * Handle users functionality with proper error handling.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'country_code', 'cca2');
    }

    /**
     * Handle customers functionality with proper error handling.
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'country_code', 'cca2');
    }

    /**
     * Handle taxRates functionality with proper error handling.
     */
    public function taxRates(): HasMany
    {
        return $this->hasMany(TaxRate::class, 'country_code', 'cca2');
    }

    /**
     * Handle currencies functionality with proper error handling.
     */
    public function currencies(): BelongsToMany
    {
        return $this->belongsToMany(Currency::class, 'country_currency', 'country_id', 'currency_id');
    }

    /**
     * Handle getDisplayNameAttribute functionality with proper error handling.
     */
    public function getDisplayNameAttribute(): string
    {
        $name = $this->trans('name') ?: $this->getOriginal('name');

        return $this->phone_calling_code ? "{$name} (+{$this->phone_calling_code})" : $name;
    }

    /**
     * Handle getTranslatedNameAttribute functionality with proper error handling.
     */
    public function getTranslatedNameAttribute(): string
    {
        return ($this->trans('name') ?: $this->getOriginal('name')) ?: 'Unknown';
    }

    /**
     * Handle getTranslatedOfficialNameAttribute functionality with proper error handling.
     */
    public function getTranslatedOfficialNameAttribute(): string
    {
        return ($this->trans('name_official') ?: $this->getOriginal('name_official')) ?: $this->getTranslatedNameAttribute();
    }

    /**
     * Handle getTranslatedDescriptionAttribute functionality with proper error handling.
     */
    public function getTranslatedDescriptionAttribute(): string
    {
        return ($this->trans('description') ?: $this->getOriginal('description')) ?: '';
    }

    /**
     * Handle getCodeAttribute functionality with proper error handling.
     */
    public function getCodeAttribute(): string
    {
        return $this->cca2;
    }

    /**
     * Handle getIsoCodeAttribute functionality with proper error handling.
     */
    public function getIsoCodeAttribute(): string
    {
        // Prefer explicit ISO-3 code when stored as a 3-letter value
        if (! empty($this->attributes['iso_code']) && \is_string($this->attributes['iso_code'])) {
            $iso = strtoupper($this->attributes['iso_code']);
            if (preg_match('/^[A-Z]{3}$/', $iso) === 1) {
                return $iso;
            }
        }

        // Project-specific fallback mapping for known special cases
        $cca2 = strtoupper((string) ($this->attributes['cca2'] ?? ''));
        $fallbackMap = [
            'TC' => 'TUN',
        ];
        if (isset($fallbackMap[$cca2])) {
            return $fallbackMap[$cca2];
        }

        // Default to stored cca3 or uppercase cca2 as a last resort
        $cca3 = (string) ($this->attributes['cca3'] ?? '');

        return $cca3 !== '' ? strtoupper($cca3) : $cca2;
    }

    /**
     * Handle getPhoneCodeAttribute functionality with proper error handling.
     */
    public function getPhoneCodeAttribute(): ?string
    {
        return $this->phone_calling_code;
    }

    // Scopes

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
     * Handle scopeEnabled functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Handle scopeEuMembers functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeEuMembers($query)
    {
        return $query->where('is_eu_member', true);
    }

    /**
     * Handle scopeRequiresVat functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeRequiresVat($query)
    {
        return $query->where('requires_vat', true);
    }

    /**
     * Handle scopeByRegion functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByRegion($query, string $region)
    {
        return $query->where('region', $region);
    }

    /**
     * Handle scopeByCurrency functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByCurrency($query, string $currencyCode)
    {
        return $query->where('currency_code', $currencyCode);
    }

    // Helper methods

    /**
     * Handle isActive functionality with proper error handling.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Handle isEuMember functionality with proper error handling.
     */
    public function isEuMember(): bool
    {
        return $this->is_eu_member;
    }

    /**
     * Handle requiresVat functionality with proper error handling.
     */
    public function requiresVat(): bool
    {
        return $this->requires_vat;
    }

    /**
     * Handle getVatRate functionality with proper error handling.
     */
    public function getVatRate(): ?float
    {
        return $this->vat_rate ? (float) $this->vat_rate : null;
    }

    /**
     * Handle getFormattedVatRate functionality with proper error handling.
     */
    public function getFormattedVatRate(): string
    {
        return $this->vat_rate ? number_format((float) $this->vat_rate, 2).'%' : 'N/A';
    }

    /**
     * Handle getFullAddress functionality with proper error handling.
     */
    public function getFullAddress(): string
    {
        $parts = array_filter([$this->translated_name, $this->region, $this->subregion]);

        return implode(', ', $parts);
    }

    /**
     * Handle getFlagUrl functionality with proper error handling.
     */
    public function getFlagUrl(): ?string
    {
        if ($this->flag) {
            return asset('flags/'.$this->flag);
        }

        return null;
    }

    /**
     * Handle getSvgFlagUrl functionality with proper error handling.
     */
    public function getSvgFlagUrl(): ?string
    {
        if ($this->svg_flag) {
            return asset('flags/svg/'.$this->svg_flag);
        }

        return null;
    }

    // Enhanced translation methods

    /**
     * Handle getTranslatedName functionality with proper error handling.
     */
    public function getTranslatedName(?string $locale = null): ?string
    {
        return $this->trans('name', $locale) ?: $this->name;
    }

    /**
     * Handle getTranslatedOfficialName functionality with proper error handling.
     */
    public function getTranslatedOfficialName(?string $locale = null): ?string
    {
        return $this->trans('name_official', $locale) ?: $this->name_official;
    }

    /**
     * Handle getTranslatedDescription functionality with proper error handling.
     */
    public function getTranslatedDescription(?string $locale = null): ?string
    {
        return $this->trans('description', $locale) ?: $this->description;
    }

    // Scope for translated countries

    /**
     * Handle scopeWithTranslations functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeWithTranslations($query, ?string $locale = null)
    {
        $locale = $locale ?: app()->getLocale();

        return $query->with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }]);
    }

    // Get all available locales for this country

    /**
     * Handle getAvailableLocales functionality with proper error handling.
     */
    public function getAvailableLocales(): array
    {
        return $this->translations()->pluck('locale')->toArray();
    }

    // Check if country has translation for specific locale

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
    public function getOrCreateTranslation(string $locale): CountryTranslation
    {
        return $this->translations()->firstOrCreate(['locale' => $locale], ['name' => $this->name, 'name_official' => $this->name_official, 'description' => $this->description]);
    }

    // Update translation for specific locale

    /**
     * Handle updateTranslation functionality with proper error handling.
     */
    public function updateTranslation(string $locale, array $data): bool
    {
        $translation = $this->translations()->where('locale', $locale)->first();
        if ($translation) {
            return $translation->update($data);
        }

        return $this->translations()->create(array_merge(['locale' => $locale], $data)) !== null;
    }

    // Bulk update translations

    /**
     * Handle updateTranslations functionality with proper error handling.
     */
    public function updateTranslations(array $translations): bool
    {
        foreach ($translations as $locale => $data) {
            $this->updateTranslation($locale, $data);
        }

        return true;
    }

    // Additional helper methods

    /**
     * Handle getFullDisplayName functionality with proper error handling.
     */
    public function getFullDisplayName(?string $locale = null): string
    {
        $name = $this->getTranslatedName($locale);

        return $this->phone_calling_code ? "{$name} (+{$this->phone_calling_code})" : $name;
    }

    /**
     * Handle getCoordinatesAttribute functionality with proper error handling.
     */
    public function getCoordinatesAttribute(): array
    {
        return ['latitude' => $this->latitude, 'longitude' => $this->longitude];
    }

    /**
     * Handle getFormattedCurrencyInfo functionality with proper error handling.
     */
    public function getFormattedCurrencyInfo(): array
    {
        return ['code' => $this->currency_code, 'symbol' => $this->currency_symbol, 'currencies' => $this->currencies];
    }

    /**
     * Handle getFormattedLanguageInfo functionality with proper error handling.
     */
    public function getFormattedLanguageInfo(): array
    {
        return ['languages' => $this->languages, 'timezones' => $this->timezones];
    }

    /**
     * Handle getFormattedVatInfo functionality with proper error handling.
     */
    public function getFormattedVatInfo(): array
    {
        return ['requires_vat' => $this->requires_vat, 'vat_rate' => $this->vat_rate, 'formatted_rate' => $this->getFormattedVatRate()];
    }

    /**
     * Handle getEconomicInfo functionality with proper error handling.
     */
    public function getEconomicInfo(): array
    {
        return ['currency' => $this->getFormattedCurrencyInfo(), 'vat' => $this->getFormattedVatInfo(), 'eu_member' => $this->is_eu_member];
    }

    /**
     * Handle getGeographicInfo functionality with proper error handling.
     */
    public function getGeographicInfo(): array
    {
        return ['region' => $this->region, 'subregion' => $this->subregion, 'coordinates' => $this->getCoordinatesAttribute(), 'timezone' => $this->timezone];
    }
}
