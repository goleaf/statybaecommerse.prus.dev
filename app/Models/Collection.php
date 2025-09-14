<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final /**
 * Collection
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class Collection extends Model implements HasMedia
{
    use HasFactory, SoftDeletes;
    use HasTranslations;
    use InteractsWithMedia;

    protected $table = 'collections';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_visible',
        'sort_order',
        'seo_title',
        'seo_description',
        'is_automatic',
        'rules',
        'max_products',
        'is_active',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'display_type',
        'products_per_page',
        'show_filters',
    ];

    public static $translatable = [
        'name',
        'description',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
            'is_automatic' => 'boolean',
            'rules' => 'array',
            'is_active' => 'boolean',
            'products_per_page' => 'integer',
            'show_filters' => 'boolean',
            'meta_keywords' => 'array',
        ];
    }

    protected string $translationModel = \App\Models\Translations\CollectionTranslation::class;

    protected static function booted(): void
    {
        self::saved(function (): void {
            self::flushCaches();
        });
        self::deleted(function (): void {
            self::flushCaches();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public static function flushCaches(): void
    {
        $locales = collect(config('app.supported_locales', 'en'))
            ->when(fn ($v) => is_string($v), fn ($c) => collect(explode(',', (string) $c)))
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->values();
        foreach ($locales as $loc) {
            Cache::forget("sitemap:urls:{$loc}");
        }
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_collections');
    }

    public function rules(): HasMany
    {
        return $this->hasMany(CollectionRule::class);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeManual($query)
    {
        return $query->where('is_automatic', false);
    }

    public function scopeAutomatic($query)
    {
        return $query->where('is_automatic', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isManual(): bool
    {
        return ! $this->is_automatic;
    }

    public function isAutomatic(): bool
    {
        return (bool) $this->is_automatic;
    }

    public function getProductsCountAttribute(): int
    {
        return $this->products()->published()->count();
    }

    public function getImageAttribute(): ?string
    {
        return $this->getFirstMediaUrl('images') ?: null;
    }

    public function getImageUrl(?string $size = null): ?string
    {
        if (! $size) {
            $url = $this->getFirstMediaUrl('images');

            return $url ?: null;
        }

        $url = $this->getFirstMediaUrl('images', "image-{$size}") ?: $this->getFirstMediaUrl('images');

        return $url ?: '';
    }

    public function getBannerUrl(?string $size = null): ?string
    {
        if (! $size) {
            $url = $this->getFirstMediaUrl('banner');

            return $url ?: null;
        }

        $url = $this->getFirstMediaUrl('banner', "banner-{$size}") ?: $this->getFirstMediaUrl('banner');

        return $url ?: '';
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('images')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml']);

        $this
            ->addMediaCollection('banner')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    // Advanced Translation Methods
    public function getTranslatedName(?string $locale = null): ?string
    {
        $translated = $this->trans('name', $locale);
        return $translated ?: $this->name;
    }

    public function getTranslatedDescription(?string $locale = null): ?string
    {
        $translated = $this->trans('description', $locale);
        return $translated ?: $this->description;
    }

    public function getTranslatedSlug(?string $locale = null): ?string
    {
        $translated = $this->trans('slug', $locale);
        return $translated ?: $this->slug;
    }

    public function getTranslatedMetaTitle(?string $locale = null): ?string
    {
        $translated = $this->trans('meta_title', $locale);
        return $translated ?: $this->meta_title;
    }

    public function getTranslatedMetaDescription(?string $locale = null): ?string
    {
        $translated = $this->trans('meta_description', $locale);
        return $translated ?: $this->meta_description;
    }

    public function getTranslatedMetaKeywords(?string $locale = null): ?array
    {
        $translated = $this->trans('meta_keywords', $locale);
        return $translated ?: $this->meta_keywords;
    }

    // Scope for translated collections
    public function scopeWithTranslations($query, ?string $locale = null)
    {
        $locale = $locale ?: app()->getLocale();
        return $query->with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }]);
    }

    // Translation Management Methods
    public function getAvailableLocales(): array
    {
        return $this->translations()->pluck('locale')->unique()->values()->toArray();
    }

    public function hasTranslationFor(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->exists();
    }

    public function getOrCreateTranslation(string $locale): \App\Models\Translations\CollectionTranslation
    {
        return $this->translations()->firstOrCreate(
            ['locale' => $locale],
            [
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
                'meta_title' => $this->meta_title,
                'meta_description' => $this->meta_description,
                'meta_keywords' => $this->meta_keywords,
            ]
        );
    }

    public function updateTranslation(string $locale, array $data): bool
    {
        $translation = $this->getOrCreateTranslation($locale);
        return $translation->update($data);
    }

    public function updateTranslations(array $translations): bool
    {
        foreach ($translations as $locale => $data) {
            $this->updateTranslation($locale, $data);
        }
        return true;
    }

    // Helper Methods
    public function getFullDisplayName(?string $locale = null): string
    {
        $name = $this->getTranslatedName($locale);
        $type = $this->is_automatic ? 'Auto' : 'Manual';
        return "{$name} ({$type})";
    }

    public function getCollectionInfo(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'type' => $this->is_automatic ? 'automatic' : 'manual',
            'is_visible' => $this->is_visible,
            'products_count' => $this->getProductsCountAttribute(),
            'display_type' => $this->display_type,
            'products_per_page' => $this->products_per_page,
            'show_filters' => $this->show_filters,
            'sort_order' => $this->sort_order,
        ];
    }

    public function getSeoInfo(): array
    {
        return [
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
        ];
    }

    public function getBusinessInfo(): array
    {
        return [
            'products_count' => $this->getProductsCountAttribute(),
            'max_products' => $this->max_products,
            'display_type' => $this->display_type,
            'products_per_page' => $this->products_per_page,
            'show_filters' => $this->show_filters,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'is_visible' => $this->is_visible,
            'is_automatic' => $this->is_automatic,
        ];
    }

    public function getCompleteInfo(?string $locale = null): array
    {
        return array_merge(
            $this->getCollectionInfo(),
            $this->getSeoInfo(),
            $this->getBusinessInfo(),
            [
                'translations' => $this->getAvailableLocales(),
                'has_translations' => count($this->getAvailableLocales()) > 0,
                'image_url' => $this->getImageUrl(),
                'banner_url' => $this->getBannerUrl(),
                'created_at' => $this->created_at?->toISOString(),
                'updated_at' => $this->updated_at?->toISOString(),
            ]
        );
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // Collection image conversions with multiple resolutions
        $this
            ->addMediaConversion('image-xs')
            ->performOnCollections('images')
            ->width(64)
            ->height(64)
            ->format('webp')
            ->quality(85)
            ->sharpen(10)
            ->optimize();

        $this
            ->addMediaConversion('image-sm')
            ->performOnCollections('images')
            ->width(200)
            ->height(200)
            ->format('webp')
            ->quality(85)
            ->sharpen(10)
            ->optimize();

        $this
            ->addMediaConversion('image-md')
            ->performOnCollections('images')
            ->width(400)
            ->height(400)
            ->format('webp')
            ->quality(85)
            ->sharpen(10)
            ->optimize();

        $this
            ->addMediaConversion('image-lg')
            ->performOnCollections('images')
            ->width(600)
            ->height(600)
            ->format('webp')
            ->quality(90)
            ->sharpen(5)
            ->optimize();

        // Banner conversions with multiple resolutions
        $this
            ->addMediaConversion('banner-sm')
            ->performOnCollections('banner')
            ->width(800)
            ->height(400)
            ->format('webp')
            ->quality(85)
            ->sharpen(10)
            ->optimize();

        $this
            ->addMediaConversion('banner-md')
            ->performOnCollections('banner')
            ->width(1200)
            ->height(600)
            ->format('webp')
            ->quality(85)
            ->sharpen(5)
            ->optimize();

        $this
            ->addMediaConversion('banner-lg')
            ->performOnCollections('banner')
            ->width(1920)
            ->height(960)
            ->format('webp')
            ->quality(90)
            ->sharpen(5)
            ->optimize();

        // Legacy conversions for backward compatibility - now in WebP
        $this
            ->addMediaConversion('thumb')
            ->performOnCollections('images')
            ->width(200)
            ->height(200)
            ->format('webp')
            ->quality(85)
            ->sharpen(10)
            ->optimize();

        $this
            ->addMediaConversion('small')
            ->performOnCollections('images')
            ->width(400)
            ->height(400)
            ->format('webp')
            ->quality(85)
            ->sharpen(10)
            ->optimize();
    }
}
