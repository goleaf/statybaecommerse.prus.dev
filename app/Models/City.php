<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Translations\CityTranslation;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final /**
 * City
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class City extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected string $translationModel = CityTranslation::class;

    protected $table = 'cities';

    protected $fillable = [
        'name',
        'slug',
        'code',
        'description',
        'is_enabled',
        'is_default',
        'is_capital',
        'country_id',
        'zone_id',
        'region_id',
        'parent_id',
        'level',
        'latitude',
        'longitude',
        'population',
        'postal_codes',
        'sort_order',
        'metadata',
        'type',
        'area',
        'density',
        'elevation',
        'timezone',
        'currency_code',
        'currency_symbol',
        'language_code',
        'language_name',
        'phone_code',
        'postal_code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'is_default' => 'boolean',
            'is_capital' => 'boolean',
            'level' => 'integer',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'population' => 'integer',
            'postal_codes' => 'array',
            'sort_order' => 'integer',
            'metadata' => 'array',
            'area' => 'decimal:2',
            'density' => 'decimal:2',
            'elevation' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(City::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(City::class, 'parent_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    // Additional relations for comprehensive city management
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeCapital($query)
    {
        return $query->where('is_capital', true);
    }

    public function scopeByLevel($query, int $level)
    {
        return $query->where('level', $level);
    }

    public function scopeByCountry($query, string $countryId)
    {
        return $query->where('country_id', $countryId);
    }

    public function scopeByZone($query, string $zoneId)
    {
        return $query->where('zone_id', $zoneId);
    }

    public function scopeByRegion($query, string $regionId)
    {
        return $query->where('region_id', $regionId);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByPopulation($query, int $minPopulation)
    {
        return $query->where('population', '>=', $minPopulation);
    }

    public function scopeByArea($query, float $minArea)
    {
        return $query->where('area', '>=', $minArea);
    }

    public function scopeByDensity($query, float $minDensity)
    {
        return $query->where('density', '>=', $minDensity);
    }

    public function scopeByElevation($query, float $minElevation)
    {
        return $query->where('elevation', '>=', $minElevation);
    }

    public function scopeByTimezone($query, string $timezone)
    {
        return $query->where('timezone', $timezone);
    }

    public function scopeByCurrency($query, string $currencyCode)
    {
        return $query->where('currency_code', $currencyCode);
    }

    public function scopeByLanguage($query, string $languageCode)
    {
        return $query->where('language_code', $languageCode);
    }

    public function scopeByPhoneCode($query, string $phoneCode)
    {
        return $query->where('phone_code', $phoneCode);
    }

    public function scopeByPostalCode($query, string $postalCode)
    {
        return $query->where('postal_code', $postalCode);
    }

    public function scopeByLatitude($query, float $latitude)
    {
        return $query->where('latitude', $latitude);
    }

    public function scopeByLongitude($query, float $longitude)
    {
        return $query->where('longitude', $longitude);
    }

    public function scopeByCoordinates($query, float $latitude, float $longitude)
    {
        return $query->where('latitude', $latitude)->where('longitude', $longitude);
    }

    public function scopeByCountryCode($query, string $code)
    {
        return $query->whereHas('country', function ($q) use ($code) {
            $q->where('code', $code);
        });
    }

    public function scopeByCountryIsoCode($query, string $isoCode)
    {
        return $query->whereHas('country', function ($q) use ($isoCode) {
            $q->where('iso_code', $isoCode);
        });
    }

    public function scopeByCountryContinent($query, string $continent)
    {
        return $query->whereHas('country', function ($q) use ($continent) {
            $q->where('continent', $continent);
        });
    }

    public function scopeByCountryCapital($query, string $capital)
    {
        return $query->whereHas('country', function ($q) use ($capital) {
            $q->where('capital', $capital);
        });
    }

    public function scopeByRegionCode($query, string $code)
    {
        return $query->whereHas('region', function ($q) use ($code) {
            $q->where('code', $code);
        });
    }

    public function scopeByRegionCapital($query, string $capital)
    {
        return $query->whereHas('region', function ($q) use ($capital) {
            $q->where('capital', $capital);
        });
    }

    public function scopeByRegionPopulation($query, int $minPopulation)
    {
        return $query->whereHas('region', function ($q) use ($minPopulation) {
            $q->where('population', '>=', $minPopulation);
        });
    }

    public function scopeByCountryCurrency($query, string $currencyCode)
    {
        return $query->whereHas('country', function ($q) use ($currencyCode) {
            $q->where('currency_code', $currencyCode);
        });
    }

    public function scopeByCountryLanguage($query, string $languageCode)
    {
        return $query->whereHas('country', function ($q) use ($languageCode) {
            $q->where('language_code', $languageCode);
        });
    }

    public function scopeByCountryTimezone($query, string $timezone)
    {
        return $query->whereHas('country', function ($q) use ($timezone) {
            $q->where('timezone', $timezone);
        });
    }

    public function scopeByCountryPhoneCode($query, string $phoneCode)
    {
        return $query->whereHas('country', function ($q) use ($phoneCode) {
            $q->where('phone_code', $phoneCode);
        });
    }

    public function scopeByRegionType($query, string $type)
    {
        return $query->whereHas('region', function ($q) use ($type) {
            $q->where('type', $type);
        });
    }

    public function scopeByRegionArea($query, float $minArea)
    {
        return $query->whereHas('region', function ($q) use ($minArea) {
            $q->where('area', '>=', $minArea);
        });
    }

    public function scopeByRegionDensity($query, float $minDensity)
    {
        return $query->whereHas('region', function ($q) use ($minDensity) {
            $q->where('density', '>=', $minDensity);
        });
    }

    public function scopeByRegionTimezone($query, string $timezone)
    {
        return $query->whereHas('region', function ($q) use ($timezone) {
            $q->where('timezone', $timezone);
        });
    }

    public function scopeByRegionCurrency($query, string $currencyCode)
    {
        return $query->whereHas('region', function ($q) use ($currencyCode) {
            $q->where('currency_code', $currencyCode);
        });
    }

    public function scopeByRegionLanguage($query, string $languageCode)
    {
        return $query->whereHas('region', function ($q) use ($languageCode) {
            $q->where('language_code', $languageCode);
        });
    }

    public function scopeByRegionPhoneCode($query, string $phoneCode)
    {
        return $query->whereHas('region', function ($q) use ($phoneCode) {
            $q->where('phone_code', $phoneCode);
        });
    }

    public function scopeByZoneCode($query, string $code)
    {
        return $query->whereHas('zone', function ($q) use ($code) {
            $q->where('code', $code);
        });
    }

    public function scopeByZoneType($query, string $type)
    {
        return $query->whereHas('zone', function ($q) use ($type) {
            $q->where('type', $type);
        });
    }

    public function scopeByZoneCurrency($query, string $currencyCode)
    {
        return $query->whereHas('zone', function ($q) use ($currencyCode) {
            $q->where('currency_code', $currencyCode);
        });
    }

    public function scopeByZoneLanguage($query, string $languageCode)
    {
        return $query->whereHas('zone', function ($q) use ($languageCode) {
            $q->where('default_language', $languageCode);
        });
    }

    public function scopeByZoneTimezone($query, string $timezone)
    {
        return $query->whereHas('zone', function ($q) use ($timezone) {
            $q->where('timezone', $timezone);
        });
    }

    public function scopeByZonePhoneCode($query, string $phoneCode)
    {
        return $query->whereHas('zone', function ($q) use ($phoneCode) {
            $q->where('phone_code', $phoneCode);
        });
    }

    public function scopeByZoneCurrencyAndLanguage($query, string $currencyCode, string $languageCode)
    {
        return $query->whereHas('zone', function ($q) use ($currencyCode, $languageCode) {
            $q->where('currency_code', $currencyCode)
              ->where('default_language', $languageCode);
        });
    }

    public function scopeByZoneCurrencyAndTimezone($query, string $currencyCode, string $timezone)
    {
        return $query->whereHas('zone', function ($q) use ($currencyCode, $timezone) {
            $q->where('currency_code', $currencyCode)
              ->where('timezone', $timezone);
        });
    }

    public function scopeByZoneLanguageAndTimezone($query, string $languageCode, string $timezone)
    {
        return $query->whereHas('zone', function ($q) use ($languageCode, $timezone) {
            $q->where('default_language', $languageCode)
              ->where('timezone', $timezone);
        });
    }

    public function scopeByZoneCurrencyLanguageAndTimezone($query, string $currencyCode, string $languageCode, string $timezone)
    {
        return $query->whereHas('zone', function ($q) use ($currencyCode, $languageCode, $timezone) {
            $q->where('currency_code', $currencyCode)
              ->where('default_language', $languageCode)
              ->where('timezone', $timezone);
        });
    }

    public function getTranslatedNameAttribute(): string
    {
        return $this->trans('name') ?: $this->getOriginal('name') ?: 'Unknown';
    }

    public function getTranslatedDescriptionAttribute(): string
    {
        return $this->trans('description') ?: $this->getOriginal('description') ?: '';
    }

    // Enhanced translation methods
    public function getTranslatedName(?string $locale = null): ?string
    {
        return $this->trans('name', $locale) ?: $this->name;
    }

    public function getTranslatedDescription(?string $locale = null): ?string
    {
        return $this->trans('description', $locale) ?: $this->description;
    }

    // Scope for translated cities
    public function scopeWithTranslations($query, ?string $locale = null)
    {
        $locale = $locale ?: app()->getLocale();

        return $query->with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }]);
    }

    // Get all available locales for this city
    public function getAvailableLocales(): array
    {
        return $this->translations()->pluck('locale')->toArray();
    }

    // Check if city has translation for specific locale
    public function hasTranslationFor(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->exists();
    }

    // Get or create translation for locale
    public function getOrCreateTranslation(string $locale): CityTranslation
    {
        return $this->translations()->firstOrCreate(
            ['locale' => $locale],
            [
                'name' => $this->name,
                'description' => $this->description,
            ]
        );
    }

    // Update translation for specific locale
    public function updateTranslation(string $locale, array $data): bool
    {
        $translation = $this->translations()->where('locale', $locale)->first();
        
        if ($translation) {
            return $translation->update($data);
        }
        
        return $this->translations()->create(array_merge(['locale' => $locale], $data)) !== null;
    }

    // Bulk update translations
    public function updateTranslations(array $translations): bool
    {
        foreach ($translations as $locale => $data) {
            $this->updateTranslation($locale, $data);
        }
        
        return true;
    }

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

    public function getDescendantsAttribute()
    {
        $descendants = collect();

        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->descendants);
        }

        return $descendants;
    }

    public function getCoordinatesAttribute(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}
