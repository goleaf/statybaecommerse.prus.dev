<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use App\Models\Translations\RegionTranslation;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * Region
 * 
 * Eloquent model representing the Region entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property string $translationModel
 * @property mixed $table
 * @property mixed $fillable
 * @method static \Illuminate\Database\Eloquent\Builder|Region newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Region newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Region query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, EnabledScope::class])]
final class Region extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;
    protected string $translationModel = RegionTranslation::class;
    protected $table = 'regions';
    protected $fillable = ['name', 'slug', 'code', 'description', 'is_enabled', 'is_default', 'country_id', 'zone_id', 'parent_id', 'level', 'sort_order', 'metadata'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['is_enabled' => 'boolean', 'is_default' => 'boolean', 'level' => 'integer', 'sort_order' => 'integer', 'metadata' => 'array'];
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
     * Handle parent functionality with proper error handling.
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'parent_id');
    }
    /**
     * Handle children functionality with proper error handling.
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(Region::class, 'parent_id');
    }
    /**
     * Handle cities functionality with proper error handling.
     * @return HasMany
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }
    /**
     * Handle addresses functionality with proper error handling.
     * @return HasMany
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }
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
     * Handle warehouses functionality with proper error handling.
     * @return HasMany
     */
    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }
    /**
     * Handle stores functionality with proper error handling.
     * @return HasMany
     */
    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
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
     * Handle scopeDefault functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
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
     * Handle scopeRoot functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
    /**
     * Handle scopeWithChildren functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeWithChildren($query)
    {
        return $query->with('children');
    }
    /**
     * Handle scopeWithParent functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeWithParent($query)
    {
        return $query->with('parent');
    }
    /**
     * Handle scopeWithCountry functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeWithCountry($query)
    {
        return $query->with('country');
    }
    /**
     * Handle scopeWithZone functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeWithZone($query)
    {
        return $query->with('zone');
    }
    /**
     * Handle scopeOrdered functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
    /**
     * Handle scopeSearch functionality with proper error handling.
     * @param mixed $query
     * @param string $search
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%");
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
    /**
     * Handle getFullPathAttribute functionality with proper error handling.
     * @return string
     */
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
     * Handle getIsRootAttribute functionality with proper error handling.
     * @return bool
     */
    public function getIsRootAttribute(): bool
    {
        return $this->parent_id === null;
    }
    /**
     * Handle getIsLeafAttribute functionality with proper error handling.
     * @return bool
     */
    public function getIsLeafAttribute(): bool
    {
        return $this->children()->count() === 0;
    }
    /**
     * Handle getDepthAttribute functionality with proper error handling.
     * @return int
     */
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
    /**
     * Handle getBreadcrumbAttribute functionality with proper error handling.
     * @return array
     */
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
    /**
     * Handle getBreadcrumbStringAttribute functionality with proper error handling.
     * @return string
     */
    public function getBreadcrumbStringAttribute(): string
    {
        return collect($this->breadcrumb)->pluck('name')->implode(' > ');
    }
    /**
     * Handle getTotalCitiesCountAttribute functionality with proper error handling.
     * @return int
     */
    public function getTotalCitiesCountAttribute(): int
    {
        return $this->cities()->count() + $this->descendants->sum(fn($region) => $region->cities()->count());
    }
    /**
     * Handle getTotalAddressesCountAttribute functionality with proper error handling.
     * @return int
     */
    public function getTotalAddressesCountAttribute(): int
    {
        return $this->addresses()->count() + $this->descendants->sum(fn($region) => $region->addresses()->count());
    }
    /**
     * Handle getTotalUsersCountAttribute functionality with proper error handling.
     * @return int
     */
    public function getTotalUsersCountAttribute(): int
    {
        return $this->users()->count() + $this->descendants->sum(fn($region) => $region->users()->count());
    }
    /**
     * Handle getTotalOrdersCountAttribute functionality with proper error handling.
     * @return int
     */
    public function getTotalOrdersCountAttribute(): int
    {
        return $this->orders()->count() + $this->descendants->sum(fn($region) => $region->orders()->count());
    }
    /**
     * Handle getTotalCustomersCountAttribute functionality with proper error handling.
     * @return int
     */
    public function getTotalCustomersCountAttribute(): int
    {
        return $this->customers()->count() + $this->descendants->sum(fn($region) => $region->customers()->count());
    }
    /**
     * Handle getTotalWarehousesCountAttribute functionality with proper error handling.
     * @return int
     */
    public function getTotalWarehousesCountAttribute(): int
    {
        return $this->warehouses()->count() + $this->descendants->sum(fn($region) => $region->warehouses()->count());
    }
    /**
     * Handle getTotalStoresCountAttribute functionality with proper error handling.
     * @return int
     */
    public function getTotalStoresCountAttribute(): int
    {
        return $this->stores()->count() + $this->descendants->sum(fn($region) => $region->stores()->count());
    }
    /**
     * Handle getStatsAttribute functionality with proper error handling.
     * @return array
     */
    public function getStatsAttribute(): array
    {
        return ['cities' => $this->total_cities_count, 'addresses' => $this->total_addresses_count, 'users' => $this->total_users_count, 'orders' => $this->total_orders_count, 'customers' => $this->total_customers_count, 'warehouses' => $this->total_warehouses_count, 'stores' => $this->total_stores_count];
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
    // Scope for translated regions
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
    // Get all available locales for this region
    /**
     * Handle getAvailableLocales functionality with proper error handling.
     * @return array
     */
    public function getAvailableLocales(): array
    {
        return $this->translations()->pluck('locale')->toArray();
    }
    // Check if region has translation for specific locale
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
     * @return RegionTranslation
     */
    public function getOrCreateTranslation(string $locale): RegionTranslation
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
    // Additional helper methods
    /**
     * Handle getFullDisplayName functionality with proper error handling.
     * @param string|null $locale
     * @return string
     */
    public function getFullDisplayName(?string $locale = null): string
    {
        $name = $this->getTranslatedName($locale);
        if ($this->country) {
            $countryName = $this->country->getTranslatedName($locale);
            return "{$name}, {$countryName}";
        }
        return $name;
    }
    /**
     * Handle getHierarchyInfo functionality with proper error handling.
     * @return array
     */
    public function getHierarchyInfo(): array
    {
        return ['level' => $this->level, 'level_name' => $this->getLevelName(), 'depth' => $this->getDepthAttribute(), 'is_root' => $this->getIsRootAttribute(), 'is_leaf' => $this->getIsLeafAttribute(), 'has_parent' => $this->parent_id !== null, 'has_children' => $this->children()->count() > 0, 'children_count' => $this->children()->count()];
    }
    /**
     * Handle getLevelName functionality with proper error handling.
     * @return string
     */
    public function getLevelName(): string
    {
        return match ($this->level) {
            0 => 'Root',
            1 => 'State/Province',
            2 => 'County',
            3 => 'District',
            4 => 'Municipality',
            5 => 'Village',
            default => "Level {$this->level}",
        };
    }
    /**
     * Handle getGeographicInfo functionality with proper error handling.
     * @return array
     */
    public function getGeographicInfo(): array
    {
        return ['country' => $this->country ? ['id' => $this->country->id, 'name' => $this->country->translated_name, 'code' => $this->country->cca2] : null, 'zone' => $this->zone ? ['id' => $this->zone->id, 'name' => $this->zone->name] : null, 'parent' => $this->parent ? ['id' => $this->parent->id, 'name' => $this->parent->translated_name, 'level' => $this->parent->level] : null];
    }
    /**
     * Handle getBusinessInfo functionality with proper error handling.
     * @return array
     */
    public function getBusinessInfo(): array
    {
        return ['cities_count' => $this->cities()->count(), 'addresses_count' => $this->addresses()->count()];
    }
    /**
     * Handle getTotalBusinessInfo functionality with proper error handling.
     * @return array
     */
    public function getTotalBusinessInfo(): array
    {
        return ['total_cities' => $this->getTotalCitiesCountAttribute(), 'total_addresses' => $this->getTotalAddressesCountAttribute()];
    }
    /**
     * Handle getCompleteInfo functionality with proper error handling.
     * @param string|null $locale
     * @return array
     */
    public function getCompleteInfo(?string $locale = null): array
    {
        return ['basic' => ['id' => $this->id, 'name' => $this->getTranslatedName($locale), 'description' => $this->getTranslatedDescription($locale), 'code' => $this->code, 'slug' => $this->slug, 'full_display_name' => $this->getFullDisplayName($locale)], 'hierarchy' => $this->getHierarchyInfo(), 'geographic' => $this->getGeographicInfo(), 'business' => $this->getBusinessInfo(), 'total_business' => $this->getTotalBusinessInfo(), 'status' => ['is_enabled' => $this->is_enabled, 'is_default' => $this->is_default, 'sort_order' => $this->sort_order]];
    }
}