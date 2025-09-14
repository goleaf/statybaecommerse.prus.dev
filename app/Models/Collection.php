<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\VisibleScope;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
/**
 * Collection
 * 
 * Eloquent model representing the Collection entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $table
 * @property mixed $fillable
 * @property mixed $translatable
 * @property string $translationModel
 * @method static \Illuminate\Database\Eloquent\Builder|Collection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Collection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Collection query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, VisibleScope::class])]
final class Collection extends Model implements HasMedia
{
    use HasFactory, SoftDeletes;
    use HasTranslations;
    use InteractsWithMedia;
    protected $table = 'collections';
    protected $fillable = ['name', 'slug', 'description', 'is_visible', 'sort_order', 'seo_title', 'seo_description', 'is_automatic', 'rules', 'max_products', 'is_active', 'meta_title', 'meta_description', 'meta_keywords', 'display_type', 'products_per_page', 'show_filters'];
    public static $translatable = ['name', 'description', 'meta_title', 'meta_description', 'meta_keywords'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['is_visible' => 'boolean', 'sort_order' => 'integer', 'is_automatic' => 'boolean', 'rules' => 'array', 'is_active' => 'boolean', 'products_per_page' => 'integer', 'show_filters' => 'boolean', 'meta_keywords' => 'array'];
    }
    protected string $translationModel = \App\Models\Translations\CollectionTranslation::class;
    /**
     * Handle booted functionality with proper error handling.
     * @return void
     */
    protected static function booted(): void
    {
        self::saved(function (): void {
            self::flushCaches();
        });
        self::deleted(function (): void {
            self::flushCaches();
        });
    }
    /**
     * Handle getRouteKeyName functionality with proper error handling.
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
    /**
     * Handle flushCaches functionality with proper error handling.
     * @return void
     */
    public static function flushCaches(): void
    {
        $locales = collect(config('app.supported_locales', 'en'))->when(fn($v) => is_string($v), fn($c) => collect(explode(',', (string) $c)))->map(fn($v) => trim((string) $v))->filter()->values();
        foreach ($locales as $loc) {
            Cache::forget("sitemap:urls:{$loc}");
        }
    }
    /**
     * Handle products functionality with proper error handling.
     * @return BelongsToMany
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_collections');
    }
    /**
     * Handle rules functionality with proper error handling.
     * @return HasMany
     */
    public function rules(): HasMany
    {
        return $this->hasMany(CollectionRule::class);
    }
    /**
     * Handle scopeVisible functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }
    /**
     * Handle scopeManual functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeManual($query)
    {
        return $query->where('is_automatic', false);
    }
    /**
     * Handle scopeAutomatic functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeAutomatic($query)
    {
        return $query->where('is_automatic', true);
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
     * Handle scopeActive functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    /**
     * Handle isManual functionality with proper error handling.
     * @return bool
     */
    public function isManual(): bool
    {
        return !$this->is_automatic;
    }
    /**
     * Handle isAutomatic functionality with proper error handling.
     * @return bool
     */
    public function isAutomatic(): bool
    {
        return (bool) $this->is_automatic;
    }
    /**
     * Handle getProductsCountAttribute functionality with proper error handling.
     * @return int
     */
    public function getProductsCountAttribute(): int
    {
        return $this->products()->published()->count();
    }
    /**
     * Handle getImageAttribute functionality with proper error handling.
     * @return string|null
     */
    public function getImageAttribute(): ?string
    {
        return $this->getFirstMediaUrl('images') ?: null;
    }
    /**
     * Handle getImageUrl functionality with proper error handling.
     * @param string|null $size
     * @return string|null
     */
    public function getImageUrl(?string $size = null): ?string
    {
        if (!$size) {
            $url = $this->getFirstMediaUrl('images');
            return $url ?: null;
        }
        $url = $this->getFirstMediaUrl('images', "image-{$size}") ?: $this->getFirstMediaUrl('images');
        return $url ?: '';
    }
    /**
     * Handle getBannerUrl functionality with proper error handling.
     * @param string|null $size
     * @return string|null
     */
    public function getBannerUrl(?string $size = null): ?string
    {
        if (!$size) {
            $url = $this->getFirstMediaUrl('banner');
            return $url ?: null;
        }
        $url = $this->getFirstMediaUrl('banner', "banner-{$size}") ?: $this->getFirstMediaUrl('banner');
        return $url ?: '';
    }
    /**
     * Handle registerMediaCollections functionality with proper error handling.
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')->singleFile()->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml']);
        $this->addMediaCollection('banner')->singleFile()->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }
    // Advanced Translation Methods
    /**
     * Handle getTranslatedName functionality with proper error handling.
     * @param string|null $locale
     * @return string|null
     */
    public function getTranslatedName(?string $locale = null): ?string
    {
        $translated = $this->trans('name', $locale);
        return $translated ?: $this->name;
    }
    /**
     * Handle getTranslatedDescription functionality with proper error handling.
     * @param string|null $locale
     * @return string|null
     */
    public function getTranslatedDescription(?string $locale = null): ?string
    {
        $translated = $this->trans('description', $locale);
        return $translated ?: $this->description;
    }
    /**
     * Handle getTranslatedSlug functionality with proper error handling.
     * @param string|null $locale
     * @return string|null
     */
    public function getTranslatedSlug(?string $locale = null): ?string
    {
        $translated = $this->trans('slug', $locale);
        return $translated ?: $this->slug;
    }
    /**
     * Handle getTranslatedMetaTitle functionality with proper error handling.
     * @param string|null $locale
     * @return string|null
     */
    public function getTranslatedMetaTitle(?string $locale = null): ?string
    {
        $translated = $this->trans('meta_title', $locale);
        return $translated ?: $this->meta_title;
    }
    /**
     * Handle getTranslatedMetaDescription functionality with proper error handling.
     * @param string|null $locale
     * @return string|null
     */
    public function getTranslatedMetaDescription(?string $locale = null): ?string
    {
        $translated = $this->trans('meta_description', $locale);
        return $translated ?: $this->meta_description;
    }
    /**
     * Handle getTranslatedMetaKeywords functionality with proper error handling.
     * @param string|null $locale
     * @return array|null
     */
    public function getTranslatedMetaKeywords(?string $locale = null): ?array
    {
        $translated = $this->trans('meta_keywords', $locale);
        return $translated ?: $this->meta_keywords;
    }
    // Scope for translated collections
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
    // Translation Management Methods
    /**
     * Handle getAvailableLocales functionality with proper error handling.
     * @return array
     */
    public function getAvailableLocales(): array
    {
        return $this->translations()->pluck('locale')->unique()->values()->toArray();
    }
    /**
     * Handle hasTranslationFor functionality with proper error handling.
     * @param string $locale
     * @return bool
     */
    public function hasTranslationFor(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->exists();
    }
    /**
     * Handle getOrCreateTranslation functionality with proper error handling.
     * @param string $locale
     * @return App\Models\Translations\CollectionTranslation
     */
    public function getOrCreateTranslation(string $locale): \App\Models\Translations\CollectionTranslation
    {
        return $this->translations()->firstOrCreate(['locale' => $locale], ['name' => $this->name, 'slug' => $this->slug, 'description' => $this->description, 'meta_title' => $this->meta_title, 'meta_description' => $this->meta_description, 'meta_keywords' => $this->meta_keywords]);
    }
    /**
     * Handle updateTranslation functionality with proper error handling.
     * @param string $locale
     * @param array $data
     * @return bool
     */
    public function updateTranslation(string $locale, array $data): bool
    {
        $translation = $this->getOrCreateTranslation($locale);
        return $translation->update($data);
    }
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
    // Helper Methods
    /**
     * Handle getFullDisplayName functionality with proper error handling.
     * @param string|null $locale
     * @return string
     */
    public function getFullDisplayName(?string $locale = null): string
    {
        $name = $this->getTranslatedName($locale);
        $type = $this->is_automatic ? 'Auto' : 'Manual';
        return "{$name} ({$type})";
    }
    /**
     * Handle getCollectionInfo functionality with proper error handling.
     * @return array
     */
    public function getCollectionInfo(): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'slug' => $this->slug, 'description' => $this->description, 'type' => $this->is_automatic ? 'automatic' : 'manual', 'is_visible' => $this->is_visible, 'products_count' => $this->getProductsCountAttribute(), 'display_type' => $this->display_type, 'products_per_page' => $this->products_per_page, 'show_filters' => $this->show_filters, 'sort_order' => $this->sort_order];
    }
    /**
     * Handle getSeoInfo functionality with proper error handling.
     * @return array
     */
    public function getSeoInfo(): array
    {
        return ['seo_title' => $this->seo_title, 'seo_description' => $this->seo_description, 'meta_title' => $this->meta_title, 'meta_description' => $this->meta_description, 'meta_keywords' => $this->meta_keywords];
    }
    /**
     * Handle getBusinessInfo functionality with proper error handling.
     * @return array
     */
    public function getBusinessInfo(): array
    {
        return ['products_count' => $this->getProductsCountAttribute(), 'max_products' => $this->max_products, 'display_type' => $this->display_type, 'products_per_page' => $this->products_per_page, 'show_filters' => $this->show_filters, 'sort_order' => $this->sort_order, 'is_active' => $this->is_active, 'is_visible' => $this->is_visible, 'is_automatic' => $this->is_automatic];
    }
    /**
     * Handle getCompleteInfo functionality with proper error handling.
     * @param string|null $locale
     * @return array
     */
    public function getCompleteInfo(?string $locale = null): array
    {
        return array_merge($this->getCollectionInfo(), $this->getSeoInfo(), $this->getBusinessInfo(), ['translations' => $this->getAvailableLocales(), 'has_translations' => count($this->getAvailableLocales()) > 0, 'image_url' => $this->getImageUrl(), 'banner_url' => $this->getBannerUrl(), 'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString()]);
    }
    /**
     * Handle registerMediaConversions functionality with proper error handling.
     * @param Media|null $media
     * @return void
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        // Collection image conversions with multiple resolutions
        $this->addMediaConversion('image-xs')->performOnCollections('images')->width(64)->height(64)->format('webp')->quality(85)->sharpen(10)->optimize();
        $this->addMediaConversion('image-sm')->performOnCollections('images')->width(200)->height(200)->format('webp')->quality(85)->sharpen(10)->optimize();
        $this->addMediaConversion('image-md')->performOnCollections('images')->width(400)->height(400)->format('webp')->quality(85)->sharpen(10)->optimize();
        $this->addMediaConversion('image-lg')->performOnCollections('images')->width(600)->height(600)->format('webp')->quality(90)->sharpen(5)->optimize();
        // Banner conversions with multiple resolutions
        $this->addMediaConversion('banner-sm')->performOnCollections('banner')->width(800)->height(400)->format('webp')->quality(85)->sharpen(10)->optimize();
        $this->addMediaConversion('banner-md')->performOnCollections('banner')->width(1200)->height(600)->format('webp')->quality(85)->sharpen(5)->optimize();
        $this->addMediaConversion('banner-lg')->performOnCollections('banner')->width(1920)->height(960)->format('webp')->quality(90)->sharpen(5)->optimize();
        // Legacy conversions for backward compatibility - now in WebP
        $this->addMediaConversion('thumb')->performOnCollections('images')->width(200)->height(200)->format('webp')->quality(85)->sharpen(10)->optimize();
        $this->addMediaConversion('small')->performOnCollections('images')->width(400)->height(400)->format('webp')->quality(85)->sharpen(10)->optimize();
    }
}