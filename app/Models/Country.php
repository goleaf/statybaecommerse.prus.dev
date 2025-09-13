<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Translations\CountryTranslation;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Country extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected string $translationModel = CountryTranslation::class;

    protected $table = 'countries';

    protected $fillable = [
        'name',
        'name_official',
        'cca2',
        'cca3',
        'ccn3',
        'code',
        'iso_code',
        'currency_code',
        'currency_symbol',
        'phone_code',
        'phone_calling_code',
        'flag',
        'svg_flag',
        'region',
        'subregion',
        'latitude',
        'longitude',
        'currencies',
        'languages',
        'timezones',
        'is_active',
        'is_eu_member',
        'requires_vat',
        'vat_rate',
        'timezone',
        'description',
        'metadata',
        'is_enabled',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'currencies' => 'array',
            'languages' => 'array',
            'timezones' => 'array',
            'is_active' => 'boolean',
            'is_eu_member' => 'boolean',
            'requires_vat' => 'boolean',
            'vat_rate' => 'decimal:2',
            'metadata' => 'array',
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function zones(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'country_zone', 'country_id', 'zone_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'country_code', 'cca2');
    }

    public function regions(): HasMany
    {
        return $this->hasMany(Region::class);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'country_code', 'cca2');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'country_code', 'cca2');
    }

    public function shippingZones(): BelongsToMany
    {
        return $this->belongsToMany(ShippingZone::class, 'country_shipping_zone', 'country_id', 'shipping_zone_id');
    }

    public function taxRates(): HasMany
    {
        return $this->hasMany(TaxRate::class, 'country_code', 'cca2');
    }

    public function currencies(): BelongsToMany
    {
        return $this->belongsToMany(Currency::class, 'country_currency', 'country_id', 'currency_id');
    }

    public function getDisplayNameAttribute(): string
    {
        $name = $this->trans('name') ?: $this->getOriginal('name');

        return $this->phone_calling_code ? "{$name} (+{$this->phone_calling_code})" : $name;
    }

    public function getTranslatedNameAttribute(): string
    {
        return $this->trans('name') ?: $this->getOriginal('name') ?: 'Unknown';
    }

    public function getTranslatedOfficialNameAttribute(): string
    {
        return $this->trans('name_official') ?: $this->getOriginal('name_official') ?: $this->getTranslatedNameAttribute();
    }

    public function getTranslatedDescriptionAttribute(): string
    {
        return $this->trans('description') ?: $this->getOriginal('description') ?: '';
    }

    public function getCodeAttribute(): string
    {
        return $this->cca2;
    }

    public function getIsoCodeAttribute(): string
    {
        return $this->cca3;
    }

    public function getPhoneCodeAttribute(): ?string
    {
        return $this->phone_calling_code;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeEuMembers($query)
    {
        return $query->where('is_eu_member', true);
    }

    public function scopeRequiresVat($query)
    {
        return $query->where('requires_vat', true);
    }

    public function scopeByRegion($query, string $region)
    {
        return $query->where('region', $region);
    }

    public function scopeByCurrency($query, string $currencyCode)
    {
        return $query->where('currency_code', $currencyCode);
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isEuMember(): bool
    {
        return $this->is_eu_member;
    }

    public function requiresVat(): bool
    {
        return $this->requires_vat;
    }

    public function getVatRate(): ?float
    {
        return $this->vat_rate ? (float) $this->vat_rate : null;
    }

    public function getFormattedVatRate(): string
    {
        return $this->vat_rate ? number_format((float) $this->vat_rate, 2).'%' : 'N/A';
    }

    public function getFullAddress(): string
    {
        $parts = array_filter([
            $this->translated_name,
            $this->region,
            $this->subregion,
        ]);

        return implode(', ', $parts);
    }

    public function getFlagUrl(): ?string
    {
        if ($this->flag) {
            return asset('flags/'.$this->flag);
        }

        return null;
    }

    public function getSvgFlagUrl(): ?string
    {
        if ($this->svg_flag) {
            return asset('flags/svg/'.$this->svg_flag);
        }

        return null;
    }
}
