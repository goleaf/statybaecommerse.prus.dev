<?php declare(strict_types=1);

namespace App\Models;

use App\Models\Translations\CityTranslation;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class City extends Model
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

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
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

    public function getTranslatedNameAttribute(): string
    {
        return $this->trans('name') ?: $this->getOriginal('name') ?: 'Unknown';
    }

    public function getTranslatedDescriptionAttribute(): string
    {
        return $this->trans('description') ?: $this->getOriginal('description') ?: '';
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


