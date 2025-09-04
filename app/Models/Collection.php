<?php declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

final class Collection extends Model implements HasMedia
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
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
            'is_automatic' => 'boolean',
            'rules' => 'array',
        ];
    }

    protected string $translationModel = \App\Models\Translations\CollectionTranslation::class;

    protected static function booted(): void
    {
        static::saved(function (): void {
            self::flushCaches();
        });
        static::deleted(function (): void {
            self::flushCaches();
        });
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

    public function isManual(): bool
    {
        return !$this->is_automatic;
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
        if (!$size) {
            return $this->getFirstMediaUrl('images') ?: null;
        }

        return $this->getFirstMediaUrl('images', "image-{$size}") ?: $this->getFirstMediaUrl('images');
    }

    public function getBannerUrl(?string $size = null): ?string
    {
        if (!$size) {
            return $this->getFirstMediaUrl('banner') ?: null;
        }

        return $this->getFirstMediaUrl('banner', "banner-{$size}") ?: $this->getFirstMediaUrl('banner');
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

    public function registerMediaConversions(Media $media = null): void
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
