<?php declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

final class Brand extends Model implements HasMedia
{
    use HasFactory, SoftDeletes;
    use HasTranslations;
    use InteractsWithMedia;
    use LogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'website',
        'is_enabled',
        'seo_title',
        'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }

    protected $table = 'brands';
    protected string $translationModel = \App\Models\Translations\BrandTranslation::class;
    
    protected $translatable = [
        'name',
        'slug', 
        'description',
        'seo_title',
        'seo_description',
    ];

    protected static function booted(): void
    {
        static::saved(function (): void {
            self::flushCaches();
        });
        static::deleted(function (): void {
            self::flushCaches();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'description', 'website', 'is_enabled'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Brand {$eventName}")
            ->useLogName('brand');
    }

    public static function flushCaches(): void
    {
        $locales = collect(config('app.supported_locales', 'en'))
            ->when(fn($v) => is_string($v), fn($c) => collect(explode(',', (string) $c)))
            ->map(fn($v) => trim((string) $v))
            ->filter()
            ->values();
        foreach ($locales as $loc) {
            Cache::forget("sitemap:urls:{$loc}");
        }
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'brand_id');
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeWithProducts($query)
    {
        return $query->whereHas('products');
    }

    public function getProductsCountAttribute(): int
    {
        return $this->products()->published()->count();
    }

    public function getLogoAttribute(): ?string
    {
        return $this->getFirstMediaUrl('logo') ?: null;
    }

    public function getLogoUrl(?string $size = null): ?string
    {
        if (!$size) {
            return $this->getFirstMediaUrl('logo') ?: null;
        }

        return $this->getFirstMediaUrl('logo', "logo-{$size}") ?: $this->getFirstMediaUrl('logo') ?: null;
    }

    public function getBannerUrl(?string $size = null): ?string
    {
        if (!$size) {
            return $this->getFirstMediaUrl('banner') ?: null;
        }

        return $this->getFirstMediaUrl('banner', "banner-{$size}") ?: $this->getFirstMediaUrl('banner') ?: null;
    }

    public function getTranslatedName(?string $locale = null): string
    {
        return $this->trans('name', $locale) ?: $this->name;
    }

    public function getTranslatedSlug(?string $locale = null): string
    {
        return $this->trans('slug', $locale) ?: $this->slug;
    }

    public function getTranslatedDescription(?string $locale = null): ?string
    {
        return $this->trans('description', $locale) ?: $this->description;
    }

    public function getTranslatedSeoTitle(?string $locale = null): ?string
    {
        return $this->trans('seo_title', $locale) ?: $this->seo_title;
    }

    public function getTranslatedSeoDescription(?string $locale = null): ?string
    {
        return $this->trans('seo_description', $locale) ?: $this->seo_description;
    }

    public function hasTranslation(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->exists();
    }

    public function getAvailableLocales(): array
    {
        return $this->translations()->pluck('locale')->toArray();
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('logo')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml']);

        $this
            ->addMediaCollection('banner')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        // Logo conversions in WebP format with multiple resolutions
        $this
            ->addMediaConversion('logo-xs')
            ->performOnCollections('logo')
            ->width(64)
            ->height(64)
            ->format('webp')
            ->quality(85)
            ->sharpen(10)
            ->optimize();

        $this
            ->addMediaConversion('logo-sm')
            ->performOnCollections('logo')
            ->width(128)
            ->height(128)
            ->format('webp')
            ->quality(85)
            ->sharpen(10)
            ->optimize();

        $this
            ->addMediaConversion('logo-md')
            ->performOnCollections('logo')
            ->width(200)
            ->height(200)
            ->format('webp')
            ->quality(85)
            ->sharpen(10)
            ->optimize();

        $this
            ->addMediaConversion('logo-lg')
            ->performOnCollections('logo')
            ->width(400)
            ->height(400)
            ->format('webp')
            ->quality(90)
            ->sharpen(5)
            ->optimize();

        // Banner conversions in WebP format with multiple resolutions
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
            ->performOnCollections('logo')
            ->width(200)
            ->height(200)
            ->format('webp')
            ->quality(85)
            ->sharpen(10)
            ->optimize();

        $this
            ->addMediaConversion('small')
            ->performOnCollections('logo')
            ->width(400)
            ->height(400)
            ->format('webp')
            ->quality(85)
            ->sharpen(10)
            ->optimize();
    }
}
