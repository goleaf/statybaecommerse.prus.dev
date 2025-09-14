<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Translations\RegionTranslation;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final /**
 * Region
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class Region extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected string $translationModel = RegionTranslation::class;

    protected $table = 'regions';

    protected $fillable = [
        'name',
        'slug',
        'code',
        'description',
        'is_enabled',
        'is_default',
        'country_id',
        'zone_id',
        'parent_id',
        'level',
        'sort_order',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'is_default' => 'boolean',
            'level' => 'integer',
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Region::class, 'parent_id');
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

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

    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
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

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeWithChildren($query)
    {
        return $query->with('children');
    }

    public function scopeWithParent($query)
    {
        return $query->with('parent');
    }

    public function scopeWithCountry($query)
    {
        return $query->with('country');
    }

    public function scopeWithZone($query)
    {
        return $query->with('zone');
    }


    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
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

    public function getFullPathAttribute(): string
    {
        $path = collect([$this->translated_name]);
        $parent = $this->parent;

        while ($parent) {
            $path->prepend($parent->translated_name);
            $parent = $parent->parent;
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

    public function getIsRootAttribute(): bool
    {
        return $this->parent_id === null;
    }

    public function getIsLeafAttribute(): bool
    {
        return $this->children()->count() === 0;
    }

    public function getDepthAttribute(): int
    {
        $depth = 0;
        $parent = $this->parent;

        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }

    public function getBreadcrumbAttribute(): array
    {
        $breadcrumb = [$this];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($breadcrumb, $parent);
            $parent = $parent->parent;
        }

        return $breadcrumb;
    }

    public function getBreadcrumbStringAttribute(): string
    {
        return collect($this->breadcrumb)->pluck('name')->implode(' > ');
    }

    public function getTotalCitiesCountAttribute(): int
    {
        return $this->cities()->count() + $this->descendants->sum(fn ($region) => $region->cities()->count());
    }

    public function getTotalAddressesCountAttribute(): int
    {
        return $this->addresses()->count() + $this->descendants->sum(fn ($region) => $region->addresses()->count());
    }

    public function getTotalUsersCountAttribute(): int
    {
        return $this->users()->count() + $this->descendants->sum(fn ($region) => $region->users()->count());
    }

    public function getTotalOrdersCountAttribute(): int
    {
        return $this->orders()->count() + $this->descendants->sum(fn ($region) => $region->orders()->count());
    }

    public function getTotalCustomersCountAttribute(): int
    {
        return $this->customers()->count() + $this->descendants->sum(fn ($region) => $region->customers()->count());
    }

    public function getTotalWarehousesCountAttribute(): int
    {
        return $this->warehouses()->count() + $this->descendants->sum(fn ($region) => $region->warehouses()->count());
    }

    public function getTotalStoresCountAttribute(): int
    {
        return $this->stores()->count() + $this->descendants->sum(fn ($region) => $region->stores()->count());
    }

    public function getStatsAttribute(): array
    {
        return [
            'cities' => $this->total_cities_count,
            'addresses' => $this->total_addresses_count,
            'users' => $this->total_users_count,
            'orders' => $this->total_orders_count,
            'customers' => $this->total_customers_count,
            'warehouses' => $this->total_warehouses_count,
            'stores' => $this->total_stores_count,
        ];
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

    // Scope for translated regions
    public function scopeWithTranslations($query, ?string $locale = null)
    {
        $locale = $locale ?: app()->getLocale();

        return $query->with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }]);
    }

    // Get all available locales for this region
    public function getAvailableLocales(): array
    {
        return $this->translations()->pluck('locale')->toArray();
    }

    // Check if region has translation for specific locale
    public function hasTranslationFor(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->exists();
    }

    // Get or create translation for locale
    public function getOrCreateTranslation(string $locale): RegionTranslation
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

    // Additional helper methods
    public function getFullDisplayName(?string $locale = null): string
    {
        $name = $this->getTranslatedName($locale);
        
        if ($this->country) {
            $countryName = $this->country->getTranslatedName($locale);
            return "{$name}, {$countryName}";
        }
        
        return $name;
    }

    public function getHierarchyInfo(): array
    {
        return [
            'level' => $this->level,
            'level_name' => $this->getLevelName(),
            'depth' => $this->getDepthAttribute(),
            'is_root' => $this->getIsRootAttribute(),
            'is_leaf' => $this->getIsLeafAttribute(),
            'has_parent' => $this->parent_id !== null,
            'has_children' => $this->children()->count() > 0,
            'children_count' => $this->children()->count(),
        ];
    }

    public function getLevelName(): string
    {
        return match ($this->level) {
            0 => 'Root',
            1 => 'State/Province',
            2 => 'County',
            3 => 'District',
            4 => 'Municipality',
            5 => 'Village',
            default => "Level {$this->level}"
        };
    }

    public function getGeographicInfo(): array
    {
        return [
            'country' => $this->country ? [
                'id' => $this->country->id,
                'name' => $this->country->translated_name,
                'code' => $this->country->cca2,
            ] : null,
            'zone' => $this->zone ? [
                'id' => $this->zone->id,
                'name' => $this->zone->name,
            ] : null,
            'parent' => $this->parent ? [
                'id' => $this->parent->id,
                'name' => $this->parent->translated_name,
                'level' => $this->parent->level,
            ] : null,
        ];
    }

    public function getBusinessInfo(): array
    {
        return [
            'cities_count' => $this->cities()->count(),
            'addresses_count' => $this->addresses()->count(),
            // 'users_count' => $this->users()->count(), // users table doesn't have region_id
            // 'orders_count' => $this->orders()->count(), // orders table doesn't have region_id
            // 'customers_count' => $this->customers()->count(), // customers table doesn't exist
            // 'warehouses_count' => $this->warehouses()->count(), // warehouses table doesn't exist
            // 'stores_count' => $this->stores()->count(), // stores table doesn't exist
        ];
    }

    public function getTotalBusinessInfo(): array
    {
        return [
            'total_cities' => $this->getTotalCitiesCountAttribute(),
            'total_addresses' => $this->getTotalAddressesCountAttribute(),
            // 'total_users' => $this->getTotalUsersCountAttribute(), // users table doesn't have region_id
            // 'total_orders' => $this->getTotalOrdersCountAttribute(), // orders table doesn't have region_id
            // 'total_customers' => $this->getTotalCustomersCountAttribute(), // customers table doesn't exist
            // 'total_warehouses' => $this->getTotalWarehousesCountAttribute(), // warehouses table doesn't exist
            // 'total_stores' => $this->getTotalStoresCountAttribute(), // stores table doesn't exist
        ];
    }

    public function getCompleteInfo(?string $locale = null): array
    {
        return [
            'basic' => [
                'id' => $this->id,
                'name' => $this->getTranslatedName($locale),
                'description' => $this->getTranslatedDescription($locale),
                'code' => $this->code,
                'slug' => $this->slug,
                'full_display_name' => $this->getFullDisplayName($locale),
            ],
            'hierarchy' => $this->getHierarchyInfo(),
            'geographic' => $this->getGeographicInfo(),
            'business' => $this->getBusinessInfo(),
            'total_business' => $this->getTotalBusinessInfo(),
            'status' => [
                'is_enabled' => $this->is_enabled,
                'is_default' => $this->is_default,
                'sort_order' => $this->sort_order,
            ],
        ];
    }
}
