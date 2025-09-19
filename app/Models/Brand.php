<?php declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Brand
 *
 * Eloquent model representing the Brand entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $appends
 * @property mixed $table
 * @property string $translationModel
 * @property mixed $translatable
 * @method static \Illuminate\Database\Eloquent\Builder|Brand newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Brand newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Brand query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, EnabledScope::class])]
final class Brand extends Model implements HasMedia
{
    use HasFactory, SoftDeletes;
    use HasTranslations;
    use InteractsWithMedia;
    use LogsActivity;

    protected $fillable = ['name', 'slug', 'description', 'website', 'is_enabled', 'is_active', 'is_visible', 'is_featured', 'seo_title', 'seo_description'];

    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'is_active' => 'boolean',
            'is_visible' => 'boolean',
            'is_featured' => 'boolean'
        ];
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['products_count', 'logo', 'canonical_url', 'meta_tags', 'total_revenue', 'average_product_price', 'website_domain'];

    protected $table = 'brands';
    protected string $translationModel = \App\Models\Translations\BrandTranslation::class;
    protected $translatable = ['name', 'slug', 'description', 'seo_title', 'seo_description'];

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
     * Handle getActivitylogOptions functionality with proper error handling.
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'slug', 'description', 'website', 'is_enabled', 'is_visible'])->logOnlyDirty()->dontSubmitEmptyLogs()->setDescriptionForEvent(fn(string $eventName) => "Brand {$eventName}")->useLogName('brand');
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
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'brand_id');
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
     * Handle scopeVisible functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Handle scopeWithProducts functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeWithProducts($query)
    {
        return $query->whereHas('products');
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
     * Handle getLogoAttribute functionality with proper error handling.
     * @return string|null
     */
    public function getLogoAttribute(): ?string
    {
        return $this->getFirstMediaUrl('logo') ?: null;
    }

    /**
     * Handle getLogoUrl functionality with proper error handling.
     * @param string|null $size
     * @return string|null
     */
    public function getLogoUrl(?string $size = null): ?string
    {
        if (!$size) {
            return $this->getFirstMediaUrl('logo') ?: null;
        }
        return ($this->getFirstMediaUrl('logo', "logo-{$size}") ?: $this->getFirstMediaUrl('logo')) ?: null;
    }

    /**
     * Handle getBannerUrl functionality with proper error handling.
     * @param string|null $size
     * @return string|null
     */
    public function getBannerUrl(?string $size = null): ?string
    {
        if (!$size) {
            return $this->getFirstMediaUrl('banner') ?: null;
        }
        return ($this->getFirstMediaUrl('banner', "banner-{$size}") ?: $this->getFirstMediaUrl('banner')) ?: null;
    }

    /**
     * Handle getTranslatedName functionality with proper error handling.
     * @param string|null $locale
     * @return string
     */
    public function getTranslatedName(?string $locale = null): string
    {
        return $this->trans('name', $locale) ?: $this->name;
    }

    /**
     * Handle getTranslatedSlug functionality with proper error handling.
     * @param string|null $locale
     * @return string
     */
    public function getTranslatedSlug(?string $locale = null): string
    {
        return $this->trans('slug', $locale) ?: $this->slug;
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
     * Handle getTranslatedSeoTitle functionality with proper error handling.
     * @param string|null $locale
     * @return string|null
     */
    public function getTranslatedSeoTitle(?string $locale = null): ?string
    {
        return $this->trans('seo_title', $locale) ?: $this->seo_title;
    }

    /**
     * Handle getTranslatedSeoDescription functionality with proper error handling.
     * @param string|null $locale
     * @return string|null
     */
    public function getTranslatedSeoDescription(?string $locale = null): ?string
    {
        return $this->trans('seo_description', $locale) ?: $this->seo_description;
    }

    /**
     * Handle hasTranslation functionality with proper error handling.
     * @param string $locale
     * @return bool
     */
    public function hasTranslation(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->exists();
    }

    /**
     * Handle getAvailableLocales functionality with proper error handling.
     * @return array
     */
    public function getAvailableLocales(): array
    {
        return $this->translations()->pluck('locale')->toArray();
    }

    // Enhanced Translation Methods

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
     * @return App\Models\Translations\BrandTranslation
     */
    public function getOrCreateTranslation(string $locale): \App\Models\Translations\BrandTranslation
    {
        return $this->translations()->firstOrCreate(['locale' => $locale], ['name' => $this->name, 'slug' => $this->slug, 'description' => $this->description, 'seo_title' => $this->seo_title, 'seo_description' => $this->seo_description]);
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
     * Handle getBrandInfo functionality with proper error handling.
     * @return array
     */
    public function getBrandInfo(): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'slug' => $this->slug, 'description' => $this->description, 'website' => $this->website, 'is_enabled' => $this->is_enabled, 'seo_title' => $this->seo_title, 'seo_description' => $this->seo_description];
    }

    /**
     * Handle getMediaInfo functionality with proper error handling.
     * @return array
     */
    public function getMediaInfo(): array
    {
        return ['has_logo' => $this->hasMedia('logo'), 'has_banner' => $this->hasMedia('banner'), 'logo_url' => $this->getLogoUrl(), 'banner_url' => $this->getBannerUrl(), 'logo_urls' => ['xs' => $this->getLogoUrl('xs'), 'sm' => $this->getLogoUrl('sm'), 'md' => $this->getLogoUrl('md'), 'lg' => $this->getLogoUrl('lg')], 'banner_urls' => ['sm' => $this->getBannerUrl('sm'), 'md' => $this->getBannerUrl('md'), 'lg' => $this->getBannerUrl('lg')]];
    }

    /**
     * Handle getSeoInfo functionality with proper error handling.
     * @return array
     */
    public function getSeoInfo(): array
    {
        return ['seo_title' => $this->seo_title, 'seo_description' => $this->seo_description, 'canonical_url' => $this->getCanonicalUrl(), 'meta_tags' => $this->getMetaTags()];
    }

    /**
     * Handle getBusinessInfo functionality with proper error handling.
     * @return array
     */
    public function getBusinessInfo(): array
    {
        return ['products_count' => $this->products()->count(), 'published_products_count' => $this->products()->published()->count(), 'total_revenue' => $this->getTotalRevenue(), 'average_product_price' => $this->getAverageProductPrice(), 'is_active' => $this->is_enabled, 'has_products' => $this->products()->exists(), 'has_website' => !empty($this->website), 'has_media' => $this->hasAnyMedia()];
    }

    /**
     * Handle getCompleteInfo functionality with proper error handling.
     * @param string|null $locale
     * @return array
     */
    public function getCompleteInfo(?string $locale = null): array
    {
        return array_merge($this->getBrandInfo(), $this->getMediaInfo(), $this->getSeoInfo(), $this->getBusinessInfo(), ['translations' => $this->getAvailableLocales(), 'has_translations' => count($this->getAvailableLocales()) > 0, 'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString()]);
    }

    // Additional helper methods

    /**
     * Handle getCanonicalUrl functionality with proper error handling.
     * @return string
     */
    public function getCanonicalUrl(): string
    {
        return route('brands.show', $this);
    }

    /**
     * Handle getMetaTags functionality with proper error handling.
     * @return array
     */
    public function getMetaTags(): array
    {
        return ['title' => $this->seo_title ?: $this->name, 'description' => $this->seo_description ?: $this->description, 'og:title' => $this->seo_title ?: $this->name, 'og:description' => $this->seo_description ?: $this->description, 'og:image' => $this->getLogoUrl('lg'), 'og:url' => $this->getCanonicalUrl()];
    }

    /**
     * Handle getTotalRevenue functionality with proper error handling.
     * @return float
     */
    public function getTotalRevenue(): float
    {
        return $this->products()->join('order_items', 'products.id', '=', 'order_items.product_id')->join('orders', 'order_items.order_id', '=', 'orders.id')->where('orders.status', 'completed')->sum(\DB::raw('order_items.quantity * order_items.price'));
    }

    /**
     * Handle getAverageProductPrice functionality with proper error handling.
     * @return float|null
     */
    public function getAverageProductPrice(): ?float
    {
        return $this->products()->published()->avg('price');
    }

    /**
     * Handle getFullDisplayName functionality with proper error handling.
     * @param string|null $locale
     * @return string
     */
    public function getFullDisplayName(?string $locale = null): string
    {
        $name = $this->getTranslatedName($locale);
        $status = $this->is_enabled ? 'Enabled' : 'Disabled';
        return "{$name} ({$status})";
    }

    /**
     * Handle isActive functionality with proper error handling.
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->is_enabled;
    }

    /**
     * Handle hasProducts functionality with proper error handling.
     * @return bool
     */
    public function hasProducts(): bool
    {
        return $this->products()->exists();
    }

    /**
     * Handle hasPublishedProducts functionality with proper error handling.
     * @return bool
     */
    public function hasPublishedProducts(): bool
    {
        return $this->products()->published()->exists();
    }

    /**
     * Handle hasWebsite functionality with proper error handling.
     * @return bool
     */
    public function hasWebsite(): bool
    {
        return !empty($this->website);
    }

    /**
     * Handle hasAnyMedia functionality with proper error handling.
     * @return bool
     */
    public function hasAnyMedia(): bool
    {
        return $this->hasMedia('logo') || $this->hasMedia('banner');
    }

    /**
     * Handle getWebsiteDomain functionality with proper error handling.
     * @return string|null
     */
    public function getWebsiteDomain(): ?string
    {
        if (!$this->website) {
            return null;
        }
        return parse_url($this->website, PHP_URL_HOST);
    }

    /**
     * Handle scopeActive functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Handle scopeInactive functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeInactive($query)
    {
        return $query->where('is_enabled', false);
    }

    /**
     * Handle scopeWithWebsite functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeWithWebsite($query)
    {
        return $query->whereNotNull('website')->where('website', '!=', '');
    }

    /**
     * Handle scopeWithoutWebsite functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeWithoutWebsite($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('website')->orWhere('website', '');
        });
    }

    /**
     * Handle scopeWithMedia functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeWithMedia($query)
    {
        return $query->whereHas('media');
    }

    /**
     * Handle scopeWithoutMedia functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeWithoutMedia($query)
    {
        return $query->whereDoesntHave('media');
    }

    /**
     * Handle scopePopular functionality with proper error handling.
     * @param mixed $query
     * @param int $minProducts
     */
    public function scopePopular($query, int $minProducts = 5)
    {
        return $query->has('products', '>=', $minProducts);
    }

    /**
     * Handle scopeRecent functionality with proper error handling.
     * @param mixed $query
     * @param int $days
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Handle registerMediaCollections functionality with proper error handling.
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile()->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml']);
        $this->addMediaCollection('banner')->singleFile()->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    /**
     * Handle registerMediaConversions functionality with proper error handling.
     * @param Media|null $media
     * @return void
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        // Logo conversions in WebP format with multiple resolutions
        $this->addMediaConversion('logo-xs')->performOnCollections('logo')->width(64)->height(64)->format('webp')->quality(85)->sharpen(10)->optimize();
        $this->addMediaConversion('logo-sm')->performOnCollections('logo')->width(128)->height(128)->format('webp')->quality(85)->sharpen(10)->optimize();
        $this->addMediaConversion('logo-md')->performOnCollections('logo')->width(200)->height(200)->format('webp')->quality(85)->sharpen(10)->optimize();
        $this->addMediaConversion('logo-lg')->performOnCollections('logo')->width(400)->height(400)->format('webp')->quality(90)->sharpen(5)->optimize();
        // Banner conversions in WebP format with multiple resolutions
        $this->addMediaConversion('banner-sm')->performOnCollections('banner')->width(800)->height(400)->format('webp')->quality(85)->sharpen(10)->optimize();
        $this->addMediaConversion('banner-md')->performOnCollections('banner')->width(1200)->height(600)->format('webp')->quality(85)->sharpen(5)->optimize();
        $this->addMediaConversion('banner-lg')->performOnCollections('banner')->width(1920)->height(960)->format('webp')->quality(90)->sharpen(5)->optimize();
        // Legacy conversions for backward compatibility - now in WebP
        $this->addMediaConversion('thumb')->performOnCollections('logo')->width(200)->height(200)->format('webp')->quality(85)->sharpen(10)->optimize();
        $this->addMediaConversion('small')->performOnCollections('logo')->width(400)->height(400)->format('webp')->quality(85)->sharpen(10)->optimize();
    }
}
