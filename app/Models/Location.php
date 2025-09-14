<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use App\Models\Translations\LocationTranslation;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
/**
 * Location
 * 
 * Eloquent model representing the Location entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property string $translationModel
 * @property mixed $table
 * @property mixed $fillable
 * @method static \Illuminate\Database\Eloquent\Builder|Location newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Location newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Location query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, EnabledScope::class])]
final class Location extends Model
{
    use HasFactory, HasTranslations, SoftDeletes, LogsActivity;
    protected string $translationModel = LocationTranslation::class;
    protected $table = 'locations';
    protected $fillable = ['name', 'slug', 'description', 'code', 'address_line_1', 'address_line_2', 'city', 'state', 'postal_code', 'country_code', 'phone', 'email', 'is_enabled', 'is_default', 'type', 'latitude', 'longitude', 'opening_hours', 'contact_info', 'sort_order'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['is_enabled' => 'boolean', 'is_default' => 'boolean', 'latitude' => 'float', 'longitude' => 'float', 'opening_hours' => 'array', 'contact_info' => 'array', 'sort_order' => 'integer'];
    }
    /**
     * Handle getActivitylogOptions functionality with proper error handling.
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'code', 'type', 'is_enabled', 'is_default', 'address_line_1', 'city', 'country_code'])->logOnlyDirty()->dontSubmitEmptyLogs()->setDescriptionForEvent(fn(string $eventName) => "Location {$eventName}")->useLogName('location');
    }
    /**
     * Handle country functionality with proper error handling.
     * @return BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code', 'cca2');
    }
    /**
     * Handle inventories functionality with proper error handling.
     * @return HasMany
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }
    /**
     * Handle variantInventories functionality with proper error handling.
     * @return HasMany
     */
    public function variantInventories(): HasMany
    {
        return $this->hasMany(VariantInventory::class);
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
     * Handle scopeByType functionality with proper error handling.
     * @param mixed $query
     * @param string $type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
    /**
     * Handle getFullAddressAttribute functionality with proper error handling.
     * @return string
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([$this->address_line_1, $this->address_line_2, $this->city, $this->state, $this->postal_code]);
        return implode(', ', $parts);
    }
    /**
     * Handle getDisplayNameAttribute functionality with proper error handling.
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        return ($this->trans('name') ?: $this->getOriginal('name')) ?: 'Unknown Location';
    }
    /**
     * Handle getTranslatedNameAttribute functionality with proper error handling.
     * @return string
     */
    public function getTranslatedNameAttribute(): string
    {
        return ($this->trans('name') ?: $this->getOriginal('name')) ?: 'Unknown Location';
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
     * Handle getTranslatedSlugAttribute functionality with proper error handling.
     * @return string
     */
    public function getTranslatedSlugAttribute(): string
    {
        return ($this->trans('slug') ?: $this->getOriginal('slug')) ?: '';
    }
    /**
     * Handle getTypeLabelAttribute functionality with proper error handling.
     * @return string
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'warehouse' => __('locations.type_warehouse'),
            'store' => __('locations.type_store'),
            'office' => __('locations.type_office'),
            'pickup_point' => __('locations.type_pickup_point'),
            'other' => __('locations.type_other'),
            default => $this->type,
        };
    }
    /**
     * Handle getCoordinatesAttribute functionality with proper error handling.
     * @return string|null
     */
    public function getCoordinatesAttribute(): ?string
    {
        if ($this->latitude && $this->longitude) {
            return "{$this->latitude}, {$this->longitude}";
        }
        return null;
    }
    /**
     * Handle getGoogleMapsUrlAttribute functionality with proper error handling.
     * @return string|null
     */
    public function getGoogleMapsUrlAttribute(): ?string
    {
        if ($this->latitude && $this->longitude) {
            return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
        }
        return null;
    }
    /**
     * Handle isWarehouse functionality with proper error handling.
     * @return bool
     */
    public function isWarehouse(): bool
    {
        return $this->type === 'warehouse';
    }
    /**
     * Handle isStore functionality with proper error handling.
     * @return bool
     */
    public function isStore(): bool
    {
        return $this->type === 'store';
    }
    /**
     * Handle isOffice functionality with proper error handling.
     * @return bool
     */
    public function isOffice(): bool
    {
        return $this->type === 'office';
    }
    /**
     * Handle isOther functionality with proper error handling.
     * @return bool
     */
    public function isOther(): bool
    {
        return $this->type === 'other';
    }
    /**
     * Handle hasCoordinates functionality with proper error handling.
     * @return bool
     */
    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }
    /**
     * Handle hasOpeningHours functionality with proper error handling.
     * @return bool
     */
    public function hasOpeningHours(): bool
    {
        return !empty($this->opening_hours);
    }
    /**
     * Handle isOpenNow functionality with proper error handling.
     * @return bool
     */
    public function isOpenNow(): bool
    {
        if (!$this->hasOpeningHours()) {
            return false;
        }
        $currentDay = strtolower(now()->format('l'));
        $currentTime = now()->format('H:i');
        foreach ($this->opening_hours as $hours) {
            if ($hours['day'] === $currentDay && !($hours['is_closed'] ?? false)) {
                $openTime = $hours['open_time'] ?? null;
                $closeTime = $hours['close_time'] ?? null;
                if ($openTime && $closeTime) {
                    return $currentTime >= $openTime && $currentTime <= $closeTime;
                }
            }
        }
        return false;
    }
    /**
     * Handle getOpeningHoursForDay functionality with proper error handling.
     * @param string $day
     * @return array|null
     */
    public function getOpeningHoursForDay(string $day): ?array
    {
        if (!$this->hasOpeningHours()) {
            return null;
        }
        foreach ($this->opening_hours as $hours) {
            if ($hours['day'] === strtolower($day)) {
                return $hours;
            }
        }
        return null;
    }
    /**
     * Handle getFormattedOpeningHours functionality with proper error handling.
     * @return array
     */
    public function getFormattedOpeningHours(): array
    {
        if (!$this->hasOpeningHours()) {
            return [];
        }
        $formatted = [];
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        foreach ($days as $day) {
            $hours = $this->getOpeningHoursForDay($day);
            if ($hours) {
                $formatted[$day] = ['day' => __("locations.{$day}"), 'open_time' => $hours['open_time'] ?? null, 'close_time' => $hours['close_time'] ?? null, 'is_closed' => $hours['is_closed'] ?? false];
            }
        }
        return $formatted;
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
    /**
     * Handle getTranslatedSlug functionality with proper error handling.
     * @param string|null $locale
     * @return string|null
     */
    public function getTranslatedSlug(?string $locale = null): ?string
    {
        return $this->trans('slug', $locale) ?: $this->slug;
    }
    // Scope for translated locations
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
    // Get all available locales for this location
    /**
     * Handle getAvailableLocales functionality with proper error handling.
     * @return array
     */
    public function getAvailableLocales(): array
    {
        return $this->translations()->pluck('locale')->toArray();
    }
    // Check if location has translation for specific locale
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
     * @return LocationTranslation
     */
    public function getOrCreateTranslation(string $locale): LocationTranslation
    {
        return $this->translations()->firstOrCreate(['locale' => $locale], ['name' => $this->name, 'description' => $this->description, 'slug' => $this->slug]);
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
     * Handle getLocationInfo functionality with proper error handling.
     * @return array
     */
    public function getLocationInfo(): array
    {
        return ['basic' => ['id' => $this->id, 'name' => $this->getTranslatedName(), 'description' => $this->getTranslatedDescription(), 'code' => $this->code, 'slug' => $this->getTranslatedSlug(), 'type' => $this->type, 'type_label' => $this->getTypeLabelAttribute()], 'address' => ['full_address' => $this->getFullAddressAttribute(), 'address_line_1' => $this->address_line_1, 'address_line_2' => $this->address_line_2, 'city' => $this->city, 'state' => $this->state, 'postal_code' => $this->postal_code, 'country_code' => $this->country_code], 'contact' => ['phone' => $this->phone, 'email' => $this->email, 'contact_info' => $this->contact_info], 'coordinates' => ['latitude' => $this->latitude, 'longitude' => $this->longitude, 'coordinates_string' => $this->getCoordinatesAttribute(), 'google_maps_url' => $this->getGoogleMapsUrlAttribute(), 'has_coordinates' => $this->hasCoordinates()], 'business' => ['opening_hours' => $this->opening_hours, 'formatted_opening_hours' => $this->getFormattedOpeningHours(), 'has_opening_hours' => $this->hasOpeningHours(), 'is_open_now' => $this->isOpenNow()], 'status' => ['is_enabled' => $this->is_enabled, 'is_default' => $this->is_default, 'sort_order' => $this->sort_order]];
    }
    /**
     * Handle getBusinessInfo functionality with proper error handling.
     * @return array
     */
    public function getBusinessInfo(): array
    {
        return ['type' => $this->type, 'type_label' => $this->getTypeLabelAttribute(), 'is_warehouse' => $this->isWarehouse(), 'is_store' => $this->isStore(), 'is_office' => $this->isOffice(), 'is_pickup_point' => $this->type === 'pickup_point', 'has_coordinates' => $this->hasCoordinates(), 'has_opening_hours' => $this->hasOpeningHours(), 'is_open_now' => $this->isOpenNow()];
    }
    /**
     * Handle getCompleteInfo functionality with proper error handling.
     * @param string|null $locale
     * @return array
     */
    public function getCompleteInfo(?string $locale = null): array
    {
        return ['basic' => ['id' => $this->id, 'name' => $this->getTranslatedName($locale), 'description' => $this->getTranslatedDescription($locale), 'code' => $this->code, 'slug' => $this->getTranslatedSlug($locale), 'full_display_name' => $this->getFullDisplayName($locale)], 'location' => $this->getLocationInfo(), 'business' => $this->getBusinessInfo(), 'status' => ['is_enabled' => $this->is_enabled, 'is_default' => $this->is_default, 'sort_order' => $this->sort_order]];
    }
}