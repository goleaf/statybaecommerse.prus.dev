<?php declare(strict_types=1);

namespace App\Models;

use App\Models\Translations\CountryTranslation;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Country extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected string $translationModel = CountryTranslation::class;

    protected $table = 'countries';

    protected $fillable = [
        'region',
        'subregion',
        'cca2',
        'cca3',
        'flag',
        'latitude',
        'longitude',
        'phone_calling_code',
        'currencies',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'currencies' => 'array',
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

    public function getCodeAttribute(): string
    {
        return $this->cca2;
    }

    public function getIsoCodeAttribute(): string
    {
        return $this->cca3;
    }

    public function getPhoneCodeAttribute(): string
    {
        return $this->phone_calling_code;
    }
}
