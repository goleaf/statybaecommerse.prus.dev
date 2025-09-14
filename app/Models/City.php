<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use App\Models\Translations\CityTranslation;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * City
 * 
 * Eloquent model representing the City entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property string $translationModel
 * @property mixed $table
 * @property mixed $fillable
 * @method static \Illuminate\Database\Eloquent\Builder|City newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|City newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|City query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, EnabledScope::class])]
final class City extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;
    protected string $translationModel = CityTranslation::class;
    protected $table = 'cities';
    protected $fillable = ['name', 'slug', 'code', 'description', 'is_enabled', 'is_default', 'is_capital', 'country_id', 'zone_id', 'region_id', 'parent_id', 'level', 'latitude', 'longitude', 'population', 'postal_codes', 'sort_order', 'metadata', 'type', 'area', 'density', 'elevation', 'timezone', 'currency_code', 'currency_symbol', 'language_code', 'language_name', 'phone_code', 'postal_code', 'is_active'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['is_enabled' => 'boolean', 'is_default' => 'boolean', 'is_capital' => 'boolean', 'level' => 'integer', 'latitude' => 'decimal:8', 'longitude' => 'decimal:8', 'population' => 'integer', 'postal_codes' => 'array', 'sort_order' => 'integer', 'metadata' => 'array', 'area' => 'decimal:2', 'density' => 'decimal:2', 'elevation' => 'decimal:2', 'is_active' => 'boolean'];
    }
    /**
     * Handle country functionality with proper error handling.
     * @return BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
    /**
     * Handle zone functionality with proper error handling.
     * @return BelongsTo
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
    /**
     * Handle region functionality with proper error handling.
     * @return BelongsTo
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
    /**
     * Handle parent functionality with proper error handling.
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(City::class, 'parent_id');
    }
    /**
     * Handle children functionality with proper error handling.
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(City::class, 'parent_id');
    }
    /**
     * Handle addresses functionality with proper error handling.
     * @return HasMany
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }
    // Additional relations for comprehensive city management
    /**
     * Handle users functionality with proper error handling.
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
    /**
     * Handle orders functionality with proper error handling.
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
    /**
     * Handle customers functionality with proper error handling.
     * @return HasMany
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
    /**
     * Handle locations functionality with proper error handling.
     * @return HasMany
     */
    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }
    /**
     * Handle scopeEnabled functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }
    /**
     * Handle scopeActive functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    /**
     * Handle scopeOrdered functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
    /**
     * Handle scopeDefault functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
    /**
     * Handle scopeCapital functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeCapital($query)
    {
        return $query->where('is_capital', true);
    }
    /**
     * Handle scopeByLevel functionality with proper error handling.
     * @param mixed $query
     * @param int $level
     */
    public function scopeByLevel($query, int $level)
    {
        return $query->where('level', $level);
    }
    /**
     * Handle scopeByCountry functionality with proper error handling.
     * @param mixed $query
     * @param string $countryId
     */
    public function scopeByCountry($query, string $countryId)
    {
        return $query->where('country_id', $countryId);
    }
    /**
     * Handle scopeByZone functionality with proper error handling.
     * @param mixed $query
     * @param string $zoneId
     */
    public function scopeByZone($query, string $zoneId)
    {
        return $query->where('zone_id', $zoneId);
    }
    /**
     * Handle scopeByRegion functionality with proper error handling.
     * @param mixed $query
     * @param string $regionId
     */
    public function scopeByRegion($query, string $regionId)
    {
        return $query->where('region_id', $regionId);
    }
    /**
     * Handle scopeRoot functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
    /**
     * Handle scopeByCode functionality with proper error handling.
     * @param mixed $query
     * @param string $code
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }
    /**
     * Handle scopeByType functionality with proper error handling.
     * @param mixed $query
     * @param string $type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
    /**
     * Handle scopeByPopulation functionality with proper error handling.
     * @param mixed $query
     * @param int $minPopulation
     */
    public function scopeByPopulation($query, int $minPopulation)
    {
        return $query->where('population', '>=', $minPopulation);
    }
    /**
     * Handle scopeByArea functionality with proper error handling.
     * @param mixed $query
     * @param float $minArea
     */
    public function scopeByArea($query, float $minArea)
    {
        return $query->where('area', '>=', $minArea);
    }
    /**
     * Handle scopeByDensity functionality with proper error handling.
     * @param mixed $query
     * @param float $minDensity
     */
    public function scopeByDensity($query, float $minDensity)
    {
        return $query->where('density', '>=', $minDensity);
    }
    /**
     * Handle scopeByElevation functionality with proper error handling.
     * @param mixed $query
     * @param float $minElevation
     */
    public function scopeByElevation($query, float $minElevation)
    {
        return $query->where('elevation', '>=', $minElevation);
    }
    /**
     * Handle scopeByTimezone functionality with proper error handling.
     * @param mixed $query
     * @param string $timezone
     */
    public function scopeByTimezone($query, string $timezone)
    {
        return $query->where('timezone', $timezone);
    }
    /**
     * Handle scopeByCurrency functionality with proper error handling.
     * @param mixed $query
     * @param string $currencyCode
     */
    public function scopeByCurrency($query, string $currencyCode)
    {
        return $query->where('currency_code', $currencyCode);
    }
    /**
     * Handle scopeByLanguage functionality with proper error handling.
     * @param mixed $query
     * @param string $languageCode
     */
    public function scopeByLanguage($query, string $languageCode)
    {
        return $query->where('language_code', $languageCode);
    }
    /**
     * Handle scopeByPhoneCode functionality with proper error handling.
     * @param mixed $query
     * @param string $phoneCode
     */
    public function scopeByPhoneCode($query, string $phoneCode)
    {
        return $query->where('phone_code', $phoneCode);
    }
    /**
     * Handle scopeByPostalCode functionality with proper error handling.
     * @param mixed $query
     * @param string $postalCode
     */
    public function scopeByPostalCode($query, string $postalCode)
    {
        return $query->where('postal_code', $postalCode);
    }
    /**
     * Handle scopeByLatitude functionality with proper error handling.
     * @param mixed $query
     * @param float $latitude
     */
    public function scopeByLatitude($query, float $latitude)
    {
        return $query->where('latitude', $latitude);
    }
    /**
     * Handle scopeByLongitude functionality with proper error handling.
     * @param mixed $query
     * @param float $longitude
     */
    public function scopeByLongitude($query, float $longitude)
    {
        return $query->where('longitude', $longitude);
    }
    /**
     * Handle scopeByCoordinates functionality with proper error handling.
     * @param mixed $query
     * @param float $latitude
     * @param float $longitude
     */
    public function scopeByCoordinates($query, float $latitude, float $longitude)
    {
        return $query->where('latitude', $latitude)->where('longitude', $longitude);
    }
    /**
     * Handle scopeByCountryCode functionality with proper error handling.
     * @param mixed $query
     * @param string $code
     */
    public function scopeByCountryCode($query, string $code)
    {
        return $query->whereHas('country', function ($q) use ($code) {
            $q->where('code', $code);
        });
    }
    /**
     * Handle scopeByCountryIsoCode functionality with proper error handling.
     * @param mixed $query
     * @param string $isoCode
     */
    public function scopeByCountryIsoCode($query, string $isoCode)
    {
        return $query->whereHas('country', function ($q) use ($isoCode) {
            $q->where('iso_code', $isoCode);
        });
    }
    /**
     * Handle scopeByCountryContinent functionality with proper error handling.
     * @param mixed $query
     * @param string $continent
     */
    public function scopeByCountryContinent($query, string $continent)
    {
        return $query->whereHas('country', function ($q) use ($continent) {
            $q->where('continent', $continent);
        });
    }
    /**
     * Handle scopeByCountryCapital functionality with proper error handling.
     * @param mixed $query
     * @param string $capital
     */
    public function scopeByCountryCapital($query, string $capital)
    {
        return $query->whereHas('country', function ($q) use ($capital) {
            $q->where('capital', $capital);
        });
    }
    /**
     * Handle scopeByRegionCode functionality with proper error handling.
     * @param mixed $query
     * @param string $code
     */
    public function scopeByRegionCode($query, string $code)
    {
        return $query->whereHas('region', function ($q) use ($code) {
            $q->where('code', $code);
        });
    }
    /**
     * Handle scopeByRegionCapital functionality with proper error handling.
     * @param mixed $query
     * @param string $capital
     */
    public function scopeByRegionCapital($query, string $capital)
    {
        return $query->whereHas('region', function ($q) use ($capital) {
            $q->where('capital', $capital);
        });
    }
    /**
     * Handle scopeByRegionPopulation functionality with proper error handling.
     * @param mixed $query
     * @param int $minPopulation
     */
    public function scopeByRegionPopulation($query, int $minPopulation)
    {
        return $query->whereHas('region', function ($q) use ($minPopulation) {
            $q->where('population', '>=', $minPopulation);
        });
    }
    /**
     * Handle scopeByCountryCurrency functionality with proper error handling.
     * @param mixed $query
     * @param string $currencyCode
     */
    public function scopeByCountryCurrency($query, string $currencyCode)
    {
        return $query->whereHas('country', function ($q) use ($currencyCode) {
            $q->where('currency_code', $currencyCode);
        });
    }
    /**
     * Handle scopeByCountryLanguage functionality with proper error handling.
     * @param mixed $query
     * @param string $languageCode
     */
    public function scopeByCountryLanguage($query, string $languageCode)
    {
        return $query->whereHas('country', function ($q) use ($languageCode) {
            $q->where('language_code', $languageCode);
        });
    }
    /**
     * Handle scopeByCountryTimezone functionality with proper error handling.
     * @param mixed $query
     * @param string $timezone
     */
    public function scopeByCountryTimezone($query, string $timezone)
    {
        return $query->whereHas('country', function ($q) use ($timezone) {
            $q->where('timezone', $timezone);
        });
    }
    /**
     * Handle scopeByCountryPhoneCode functionality with proper error handling.
     * @param mixed $query
     * @param string $phoneCode
     */
    public function scopeByCountryPhoneCode($query, string $phoneCode)
    {
        return $query->whereHas('country', function ($q) use ($phoneCode) {
            $q->where('phone_code', $phoneCode);
        });
    }
    /**
     * Handle scopeByRegionType functionality with proper error handling.
     * @param mixed $query
     * @param string $type
     */
    public function scopeByRegionType($query, string $type)
    {
        return $query->whereHas('region', function ($q) use ($type) {
            $q->where('type', $type);
        });
    }
    /**
     * Handle scopeByRegionArea functionality with proper error handling.
     * @param mixed $query
     * @param float $minArea
     */
    public function scopeByRegionArea($query, float $minArea)
    {
        return $query->whereHas('region', function ($q) use ($minArea) {
            $q->where('area', '>=', $minArea);
        });
    }
    /**
     * Handle scopeByRegionDensity functionality with proper error handling.
     * @param mixed $query
     * @param float $minDensity
     */
    public function scopeByRegionDensity($query, float $minDensity)
    {
        return $query->whereHas('region', function ($q) use ($minDensity) {
            $q->where('density', '>=', $minDensity);
        });
    }
    /**
     * Handle scopeByRegionTimezone functionality with proper error handling.
     * @param mixed $query
     * @param string $timezone
     */
    public function scopeByRegionTimezone($query, string $timezone)
    {
        return $query->whereHas('region', function ($q) use ($timezone) {
            $q->where('timezone', $timezone);
        });
    }
    /**
     * Handle scopeByRegionCurrency functionality with proper error handling.
     * @param mixed $query
     * @param string $currencyCode
     */
    public function scopeByRegionCurrency($query, string $currencyCode)
    {
        return $query->whereHas('region', function ($q) use ($currencyCode) {
            $q->where('currency_code', $currencyCode);
        });
    }
    /**
     * Handle scopeByRegionLanguage functionality with proper error handling.
     * @param mixed $query
     * @param string $languageCode
     */
    public function scopeByRegionLanguage($query, string $languageCode)
    {
        return $query->whereHas('region', function ($q) use ($languageCode) {
            $q->where('language_code', $languageCode);
        });
    }
    /**
     * Handle scopeByRegionPhoneCode functionality with proper error handling.
     * @param mixed $query
     * @param string $phoneCode
     */
    public function scopeByRegionPhoneCode($query, string $phoneCode)
    {
        return $query->whereHas('region', function ($q) use ($phoneCode) {
            $q->where('phone_code', $phoneCode);
        });
    }
    /**
     * Handle scopeByZoneCode functionality with proper error handling.
     * @param mixed $query
     * @param string $code
     */
    public function scopeByZoneCode($query, string $code)
    {
        return $query->whereHas('zone', function ($q) use ($code) {
            $q->where('code', $code);
        });
    }
    /**
     * Handle scopeByZoneType functionality with proper error handling.
     * @param mixed $query
     * @param string $type
     */
    public function scopeByZoneType($query, string $type)
    {
        return $query->whereHas('zone', function ($q) use ($type) {
            $q->where('type', $type);
        });
    }
    /**
     * Handle scopeByZoneCurrency functionality with proper error handling.
     * @param mixed $query
     * @param string $currencyCode
     */
    public function scopeByZoneCurrency($query, string $currencyCode)
    {
        return $query->whereHas('zone', function ($q) use ($currencyCode) {
            $q->where('currency_code', $currencyCode);
        });
    }
    /**
     * Handle scopeByZoneLanguage functionality with proper error handling.
     * @param mixed $query
     * @param string $languageCode
     */
    public function scopeByZoneLanguage($query, string $languageCode)
    {
        return $query->whereHas('zone', function ($q) use ($languageCode) {
            $q->where('default_language', $languageCode);
        });
    }
    /**
     * Handle scopeByZoneTimezone functionality with proper error handling.
     * @param mixed $query
     * @param string $timezone
     */
    public function scopeByZoneTimezone($query, string $timezone)
    {
        return $query->whereHas('zone', function ($q) use ($timezone) {
            $q->where('timezone', $timezone);
        });
    }
    /**
     * Handle scopeByZonePhoneCode functionality with proper error handling.
     * @param mixed $query
     * @param string $phoneCode
     */
    public function scopeByZonePhoneCode($query, string $phoneCode)
    {
        return $query->whereHas('zone', function ($q) use ($phoneCode) {
            $q->where('phone_code', $phoneCode);
        });
    }
    /**
     * Handle scopeByZoneCurrencyAndLanguage functionality with proper error handling.
     * @param mixed $query
     * @param string $currencyCode
     * @param string $languageCode
     */
    public function scopeByZoneCurrencyAndLanguage($query, string $currencyCode, string $languageCode)
    {
        return $query->whereHas('zone', function ($q) use ($currencyCode, $languageCode) {
            $q->where('currency_code', $currencyCode)->where('default_language', $languageCode);
        });
    }
    /**
     * Handle scopeByZoneCurrencyAndTimezone functionality with proper error handling.
     * @param mixed $query
     * @param string $currencyCode
     * @param string $timezone
     */
    public function scopeByZoneCurrencyAndTimezone($query, string $currencyCode, string $timezone)
    {
        return $query->whereHas('zone', function ($q) use ($currencyCode, $timezone) {
            $q->where('currency_code', $currencyCode)->where('timezone', $timezone);
        });
    }
    /**
     * Handle scopeByZoneLanguageAndTimezone functionality with proper error handling.
     * @param mixed $query
     * @param string $languageCode
     * @param string $timezone
     */
    public function scopeByZoneLanguageAndTimezone($query, string $languageCode, string $timezone)
    {
        return $query->whereHas('zone', function ($q) use ($languageCode, $timezone) {
            $q->where('default_language', $languageCode)->where('timezone', $timezone);
        });
    }
    /**
     * Handle scopeByZoneCurrencyLanguageAndTimezone functionality with proper error handling.
     * @param mixed $query
     * @param string $currencyCode
     * @param string $languageCode
     * @param string $timezone
     */
    public function scopeByZoneCurrencyLanguageAndTimezone($query, string $currencyCode, string $languageCode, string $timezone)
    {
        return $query->whereHas('zone', function ($q) use ($currencyCode, $languageCode, $timezone) {
            $q->where('currency_code', $currencyCode)->where('default_language', $languageCode)->where('timezone', $timezone);
        });
    }
    /**
     * Handle getTranslatedNameAttribute functionality with proper error handling.
     * @return string
     */
    public function getTranslatedNameAttribute(): string
    {
        return ($this->trans('name') ?: $this->getOriginal('name')) ?: 'Unknown';
    }
    /**
     * Handle getTranslatedDescriptionAttribute functionality with proper error handling.
     * @return string
     */
    public function getTranslatedDescriptionAttribute(): string
    {
        return ($this->trans('description') ?: $this->getOriginal('description')) ?: '';
    }
    // Enhanced translation methods
    /**
     * Handle getTranslatedName functionality with proper error handling.
     * @param string|null $locale
     * @return string|null
     */
    public function getTranslatedName(?string $locale = null): ?string
    {
        return $this->trans('name', $locale) ?: $this->name;
    }
    /**
     * Handle getTranslatedDescription functionality with proper error handling.
     * @param string|null $locale
     * @return string|null
     */
    public function getTranslatedDescription(?string $locale = null): ?string
    {
        return $this->trans('description', $locale) ?: $this->description;
    }
    // Scope for translated cities
    /**
     * Handle scopeWithTranslations functionality with proper error handling.
     * @param mixed $query
     * @param string|null $locale
     */
    public function scopeWithTranslations($query, ?string $locale = null)
    {
        $locale = $locale ?: app()->getLocale();
        return $query->with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }]);
    }
    // Get all available locales for this city
    /**
     * Handle getAvailableLocales functionality with proper error handling.
     * @return array
     */
    public function getAvailableLocales(): array
    {
        return $this->translations()->pluck('locale')->toArray();
    }
    // Check if city has translation for specific locale
    /**
     * Handle hasTranslationFor functionality with proper error handling.
     * @param string $locale
     * @return bool
     */
    public function hasTranslationFor(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->exists();
    }
    // Get or create translation for locale
    /**
     * Handle getOrCreateTranslation functionality with proper error handling.
     * @param string $locale
     * @return CityTranslation
     */
    public function getOrCreateTranslation(string $locale): CityTranslation
    {
        return $this->translations()->firstOrCreate(['locale' => $locale], ['name' => $this->name, 'description' => $this->description]);
    }
    // Update translation for specific locale
    /**
     * Handle updateTranslation functionality with proper error handling.
     * @param string $locale
     * @param array $data
     * @return bool
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
     * @param array $translations
     * @return bool
     */
    public function updateTranslations(array $translations): bool
    {
        foreach ($translations as $locale => $data) {
            $this->updateTranslation($locale, $data);
        }
        return true;
    }
    /**
     * Handle getFullPathAttribute functionality with proper error handling.
     * @return string
     */
    public function getFullPathAttribute(): string
    {
        $path = collect([$this->translated_name]);
        if ($this->region) {
            $path->prepend($this->region->translated_name);
        }
        if ($this->country) {
            $path->prepend($this->country->translated_name);
        }
        return $path->implode(' > ');
    }
    /**
     * Handle getAncestorsAttribute functionality with proper error handling.
     */
    public function getAncestorsAttribute()
    {
        $ancestors = collect();
        $parent = $this->parent;
        while ($parent) {
            $ancestors->prepend($parent);
            $parent = $parent->parent;
        }
        return $ancestors;
    }
    /**
     * Handle getDescendantsAttribute functionality with proper error handling.
     */
    public function getDescendantsAttribute()
    {
        $descendants = collect();
        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->descendants);
        }
        return $descendants;
    }
    /**
     * Handle getCoordinatesAttribute functionality with proper error handling.
     * @return array
     */
    public function getCoordinatesAttribute(): array
    {
        return ['latitude' => $this->latitude, 'longitude' => $this->longitude];
    }
}