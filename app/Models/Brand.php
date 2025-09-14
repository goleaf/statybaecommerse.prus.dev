<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[ScopedBy([ActiveScope::class, EnabledScope::class])]
final /**
 * Brand
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class Brand extends Model implements HasMedia
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

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'products_count',
        'logo',
        'canonical_url',
        'meta_tags',
        'total_revenue',
        'average_product_price',
        'website_domain',
    ];

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'description', 'website', 'is_enabled'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Brand {$eventName}")
            ->useLogName('brand');
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
        if (! $size) {
            return $this->getFirstMediaUrl('logo') ?: null;
        }

        return $this->getFirstMediaUrl('logo', "logo-{$size}") ?: $this->getFirstMediaUrl('logo') ?: null;
    }

    public function getBannerUrl(?string $size = null): ?string
    {
        if (! $size) {
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

    // Enhanced Translation Methods
    public function scopeWithTranslations($query, ?string $locale = null)
    {
        $locale = $locale ?: app()->getLocale();
        return $query->with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }]);
    }

    public function hasTranslationFor(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->exists();
    }

    public function getOrCreateTranslation(string $locale): \App\Models\Translations\BrandTranslation
    {
        return $this->translations()->firstOrCreate(
            ['locale' => $locale],
            [
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
                'seo_title' => $this->seo_title,
                'seo_description' => $this->seo_description,
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
    public function getBrandInfo(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'website' => $this->website,
            'is_enabled' => $this->is_enabled,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
        ];
    }

    public function getMediaInfo(): array
    {
        return [
            'has_logo' => $this->hasMedia('logo'),
            'has_banner' => $this->hasMedia('banner'),
            'logo_url' => $this->getLogoUrl(),
            'banner_url' => $this->getBannerUrl(),
            'logo_urls' => [
                'xs' => $this->getLogoUrl('xs'),
                'sm' => $this->getLogoUrl('sm'),
                'md' => $this->getLogoUrl('md'),
                'lg' => $this->getLogoUrl('lg'),
            ],
            'banner_urls' => [
                'sm' => $this->getBannerUrl('sm'),
                'md' => $this->getBannerUrl('md'),
                'lg' => $this->getBannerUrl('lg'),
            ],
        ];
    }

    public function getSeoInfo(): array
    {
        return [
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'canonical_url' => $this->getCanonicalUrl(),
            'meta_tags' => $this->getMetaTags(),
        ];
    }

    public function getBusinessInfo(): array
    {
        return [
            'products_count' => $this->products()->count(),
            'published_products_count' => $this->products()->published()->count(),
            'total_revenue' => $this->getTotalRevenue(),
            'average_product_price' => $this->getAverageProductPrice(),
            'is_active' => $this->is_enabled,
            'has_products' => $this->products()->exists(),
            'has_website' => !empty($this->website),
            'has_media' => $this->hasAnyMedia(),
        ];
    }

    public function getCompleteInfo(?string $locale = null): array
    {
        return array_merge(
            $this->getBrandInfo(),
            $this->getMediaInfo(),
            $this->getSeoInfo(),
            $this->getBusinessInfo(),
            [
                'translations' => $this->getAvailableLocales(),
                'has_translations' => count($this->getAvailableLocales()) > 0,
                'created_at' => $this->created_at?->toISOString(),
                'updated_at' => $this->updated_at?->toISOString(),
            ]
        );
    }

    // Additional helper methods
    public function getCanonicalUrl(): string
    {
        return route('brands.show', $this);
    }

    public function getMetaTags(): array
    {
        return [
            'title' => $this->seo_title ?: $this->name,
            'description' => $this->seo_description ?: $this->description,
            'og:title' => $this->seo_title ?: $this->name,
            'og:description' => $this->seo_description ?: $this->description,
            'og:image' => $this->getLogoUrl('lg'),
            'og:url' => $this->getCanonicalUrl(),
        ];
    }

    public function getTotalRevenue(): float
    {
        return $this->products()
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->sum(\DB::raw('order_items.quantity * order_items.price'));
    }

    public function getAverageProductPrice(): ?float
    {
        return $this->products()->published()->avg('price');
    }

    public function getFullDisplayName(?string $locale = null): string
    {
        $name = $this->getTranslatedName($locale);
        $status = $this->is_enabled ? 'Enabled' : 'Disabled';
        return "{$name} ({$status})";
    }

    public function isActive(): bool
    {
        return $this->is_enabled;
    }

    public function hasProducts(): bool
    {
        return $this->products()->exists();
    }

    public function hasPublishedProducts(): bool
    {
        return $this->products()->published()->exists();
    }

    public function hasWebsite(): bool
    {
        return !empty($this->website);
    }

    public function hasAnyMedia(): bool
    {
        return $this->hasMedia('logo') || $this->hasMedia('banner');
    }

    public function getWebsiteDomain(): ?string
    {
        if (!$this->website) {
            return null;
        }
        
        return parse_url($this->website, PHP_URL_HOST);
    }

    public function scopeActive($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_enabled', false);
    }

    public function scopeWithWebsite($query)
    {
        return $query->whereNotNull('website')->where('website', '!=', '');
    }

    public function scopeWithoutWebsite($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('website')->orWhere('website', '');
        });
    }

    public function scopeWithMedia($query)
    {
        return $query->whereHas('media');
    }

    public function scopeWithoutMedia($query)
    {
        return $query->whereDoesntHave('media');
    }

    public function scopePopular($query, int $minProducts = 5)
    {
        return $query->has('products', '>=', $minProducts);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
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

    public function registerMediaConversions(?Media $media = null): void
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
