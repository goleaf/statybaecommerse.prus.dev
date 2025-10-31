<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\TranslatableRecord;
use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use App\Observers\BrandObserver;
use App\Traits\HasTranslations;
use DB;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Brand
 *
 * Eloquent model representing the Brand entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed       $fillable
 * @property mixed       $appends
 * @property mixed       $table
 * @property string      $translationModel
 * @property mixed       $translatable
 * @property int         $id
 * @property string      $name
 * @property string|null $slug
 * @property string|null $description
 * @property bool        $is_enabled
 * @property bool        $is_visible
 * @property-read int|null $products_count
 * @property-read string|null $logo
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Brand newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Brand newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Brand query()
 *
 * @mixin \Eloquent
 */
// Attach the cache-aware observer so brand changes invalidate storefront data.
#[ObservedBy([BrandObserver::class])]
#[ScopedBy([ActiveScope::class, EnabledScope::class])]
final class Brand extends Model implements HasMedia, TranslatableRecord
{
    use HasFactory;
    use HasTranslations;
    use InteractsWithMedia;
    use LogsActivity;

    // Keep alphabetical listings predictable by piping queries through the shared OrdersByName scope.
    use OrdersByName;
    use Searchable;
    use SoftDeletes;

    /**
     * Allow the admin panel to mass assign all primary profile fields, including premium flags and social links.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'website',
        'contact_email',
        'contact_phone',
        'is_enabled',
        'is_active',
        'is_visible',
        'is_featured',
        'is_premium',
        'sort_order',
        'meta_title',
        'meta_description',
        'seo_title',
        'seo_description',
        'social_links',
    ];

    /**
     * Enumerate supported social platforms to sanitise JSON payloads from the Filament repeater.
     *
     * @var array<int, string>
     */
    public const SOCIAL_LINK_PLATFORMS = [
        'facebook',
        'instagram',
        'linkedin',
        'tiktok',
        'twitter',
        'youtube',
        'pinterest',
        'github',
        'website',
    ];

    /**
     * Point the shared OrdersByName trait at the human readable column exposed in storefront UIs.
     */
    protected string $nameColumn = 'name';

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return [
            // Force boolean casting so API consumers never receive integer toggles.
            'is_enabled'  => 'boolean',
            'is_active'   => 'boolean',
            'is_visible'  => 'boolean',
            'is_featured' => 'boolean',
            'is_premium'  => 'boolean',
            // Normalise manual ordering values so table filters and JSON responses stay numeric.
            'sort_order'  => 'integer',
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

    public function shouldBeSearchable(): bool
    {
        if (config('search.driver') !== 'scout' || ! config('search.scout.enabled')) {
            return false;
        }

        if (! $this->is_enabled || empty($this->slug)) {
            return false;
        }

        return $this->searchableProductsExist();
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $this->loadCount([
            'products as products_count' => static fn (Builder $query): Builder => $query
                ->where('is_visible', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now()),
        ]);

        $locale = app()->getLocale();

        return [
            'id'                     => $this->getKey(),
            'type'                   => 'brand',
            'name'                   => $this->name,
            'slug'                   => $this->slug,
            'description'            => $this->description,
            'translated_name'        => $this->trans('name', $locale),
            'translated_description' => $this->trans('description', $locale),
            'products_count'         => (int) ($this->products_count ?? 0),
            'is_enabled'             => (bool) $this->is_enabled,
        ];
    }

    private function searchableProductsExist(): bool
    {
        return $this->products()
            ->where('is_visible', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->exists();
    }

    /**
     * Handle booted functionality with proper error handling.
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
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Handle getActivitylogOptions functionality with proper error handling.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'slug', 'description', 'website', 'is_enabled', 'is_visible'])->logOnlyDirty()->dontSubmitEmptyLogs()->setDescriptionForEvent(fn (string $eventName): string => "Brand {$eventName}")->useLogName('brand');
    }

    /**
     * Handle flushCaches functionality with proper error handling.
     */
    public static function flushCaches(): void
    {
        $locales = collect(config('app.supported_locales', 'en'))->when(is_string(...), fn ($c): \Illuminate\Support\Collection => collect(explode(',', (string) $c)))->map(fn ($v): string => trim((string) $v))->filter()->values();
        foreach ($locales as $loc) {
            Cache::forget("sitemap:urls:{$loc}");
        }
    }

    /**
     * Handle products functionality with proper error handling.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'brand_id');
    }

    /**
     * Handle scopeEnabled functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Handle scopeVisible functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Handle scopeWithProducts functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeWithProducts($query)
    {
        return $query->whereHas('products');
    }

    /**
     * Handle getProductsCountAttribute functionality with proper error handling.
     */
    public function getProductsCountAttribute(): int
    {
        return $this->products()->published()->count();
    }

    /**
     * Handle getLogoAttribute functionality with proper error handling.
     */
    public function getLogoAttribute(): ?string
    {
        return $this->getFirstMediaUrl('logo') ?: null;
    }

    /**
     * Handle getLogoUrl functionality with proper error handling.
     */
    public function getLogoUrl(?string $size = null): ?string
    {
        if (! $size) {
            return $this->getFirstMediaUrl('logo') ?: null;
        }

        return ($this->getFirstMediaUrl('logo', "logo-{$size}") ?: $this->getFirstMediaUrl('logo')) ?: null;
    }

    /**
     * Handle getBannerUrl functionality with proper error handling.
     */
    public function getBannerUrl(?string $size = null): ?string
    {
        if (! $size) {
            return $this->getFirstMediaUrl('banner') ?: null;
        }

        return ($this->getFirstMediaUrl('banner', "banner-{$size}") ?: $this->getFirstMediaUrl('banner')) ?: null;
    }

    /**
     * Handle getTranslatedName functionality with proper error handling.
     */
    public function getTranslatedName(?string $locale = null): string
    {
        return $this->trans('name', $locale) ?: $this->name;
    }

    /**
     * Handle getTranslatedSlug functionality with proper error handling.
     */
    public function getTranslatedSlug(?string $locale = null): string
    {
        return $this->trans('slug', $locale) ?: $this->slug;
    }

    /**
     * Handle getTranslatedDescription functionality with proper error handling.
     */
    public function getTranslatedDescription(?string $locale = null): ?string
    {
        return $this->trans('description', $locale) ?: $this->description;
    }

    /**
     * Handle getTranslatedSeoTitle functionality with proper error handling.
     */
    public function getTranslatedSeoTitle(?string $locale = null): ?string
    {
        return $this->trans('seo_title', $locale) ?: $this->seo_title;
    }

    /**
     * Handle getTranslatedSeoDescription functionality with proper error handling.
     */
    public function getTranslatedSeoDescription(?string $locale = null): ?string
    {
        return $this->trans('seo_description', $locale) ?: $this->seo_description;
    }

    /**
     * Handle hasTranslation functionality with proper error handling.
     */
    public function hasTranslation(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->exists();
    }

    /**
     * Handle getAvailableLocales functionality with proper error handling.
     */
    public function getAvailableLocales(): array
    {
        return $this->translations()->pluck('locale')->toArray();
    }

    // Enhanced Translation Methods

    /**
     * Handle scopeWithTranslations functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeWithTranslations($query, ?string $locale = null)
    {
        $locale = $locale ?: app()->getLocale();

        return $query->with(['translations' => function ($q) use ($locale): void {
            $q->where('locale', $locale);
        }]);
    }

    /**
     * Handle hasTranslationFor functionality with proper error handling.
     */
    public function hasTranslationFor(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->exists();
    }

    /**
     * Handle getOrCreateTranslation functionality with proper error handling.
     */
    public function getOrCreateTranslation(string $locale): \App\Models\Translations\BrandTranslation
    {
        return $this->translations()->firstOrCreate(['locale' => $locale], ['name' => $this->name, 'slug' => $this->slug, 'description' => $this->description, 'seo_title' => $this->seo_title, 'seo_description' => $this->seo_description]);
    }

    /**
     * Handle updateTranslation functionality with proper error handling.
     */
    public function updateTranslation(string $locale, array $data): bool
    {
        $translation = $this->getOrCreateTranslation($locale);

        return $translation->update($data);
    }

    /**
     * Handle updateTranslations functionality with proper error handling.
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
     */
    public function getBrandInfo(): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'slug' => $this->slug, 'description' => $this->description, 'website' => $this->website, 'is_enabled' => $this->is_enabled, 'seo_title' => $this->seo_title, 'seo_description' => $this->seo_description];
    }

    /**
     * Handle getMediaInfo functionality with proper error handling.
     */
    public function getMediaInfo(): array
    {
        return ['has_logo' => $this->hasMedia('logo'), 'has_banner' => $this->hasMedia('banner'), 'logo_url' => $this->getLogoUrl(), 'banner_url' => $this->getBannerUrl(), 'logo_urls' => ['xs' => $this->getLogoUrl('xs'), 'sm' => $this->getLogoUrl('sm'), 'md' => $this->getLogoUrl('md'), 'lg' => $this->getLogoUrl('lg')], 'banner_urls' => ['sm' => $this->getBannerUrl('sm'), 'md' => $this->getBannerUrl('md'), 'lg' => $this->getBannerUrl('lg')]];
    }

    /**
     * Handle getSeoInfo functionality with proper error handling.
     */
    public function getSeoInfo(): array
    {
        return ['seo_title' => $this->seo_title, 'seo_description' => $this->seo_description, 'canonical_url' => $this->getCanonicalUrl(), 'meta_tags' => $this->getMetaTags()];
    }

    /**
     * Handle getBusinessInfo functionality with proper error handling.
     */
    public function getBusinessInfo(): array
    {
        return [
            'products_count'           => $this->products()->count(),
            'published_products_count' => $this->products()->published()->count(),
            'total_revenue'            => $this->getTotalRevenue(),
            'average_product_price'    => $this->getAverageProductPrice(),
            'is_active'                => $this->is_enabled,
            'is_premium'               => (bool) $this->is_premium,
            'has_products'             => $this->products()->exists(),
            'has_website'              => ! empty($this->website),
            'has_media'                => $this->hasAnyMedia(),
            'social_links_count'       => count($this->social_links),
        ];
    }

    /**
     * Handle getCompleteInfo functionality with proper error handling.
     */
    public function getCompleteInfo(?string $locale = null): array
    {
        return array_merge(
            $this->getBrandInfo(),
            $this->getMediaInfo(),
            $this->getSeoInfo(),
            $this->getBusinessInfo(),
            [
                'social_links'       => $this->social_links,
                'social_links_count' => count($this->social_links),
                'translations'       => $this->getAvailableLocales(),
                'has_translations'   => count($this->getAvailableLocales()) > 0,
                'is_premium'         => (bool) $this->is_premium,
                'created_at'         => $this->created_at?->toISOString(),
                'updated_at'         => $this->updated_at?->toISOString(),
            ],
        );
    }

    // Additional helper methods

    /**
     * Handle getCanonicalUrl functionality with proper error handling.
     */
    public function getCanonicalUrl(): string
    {
        if (Route::has('brands.show')) {
            return route('brands.show', $this);
        }

        // Fallback keeps previews and tests functional even if the route is not registered.
        $slug = trim((string) ($this->slug ?? ''));

        return $slug !== '' ? url('/brands/' . $slug) : url('/');
    }

    /**
     * Handle getCanonicalUrlAttribute functionality with proper error handling.
     */
    public function getCanonicalUrlAttribute(): string
    {
        return $this->getCanonicalUrl();
    }

    /**
     * Handle getMetaTags functionality with proper error handling.
     */
    public function getMetaTags(): array
    {
        return ['title' => $this->seo_title ?: $this->name, 'description' => $this->seo_description ?: $this->description, 'og:title' => $this->seo_title ?: $this->name, 'og:description' => $this->seo_description ?: $this->description, 'og:image' => $this->getLogoUrl('lg'), 'og:url' => $this->getCanonicalUrl()];
    }

    /**
     * Handle getTotalRevenue functionality with proper error handling.
     */
    public function getTotalRevenue(): float
    {
        if (! Schema::hasTable('order_items') || ! Schema::hasTable('orders')) {
            return 0.0;
        }

        return $this->products()
            ->withoutGlobalScopes()
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->sum(DB::raw('order_items.quantity * order_items.price'));
    }

    /**
     * Handle getAverageProductPrice functionality with proper error handling.
     */
    public function getAverageProductPrice(): ?float
    {
        return $this->products()->published()->avg('price');
    }

    /**
     * Handle getFullDisplayName functionality with proper error handling.
     */
    public function getFullDisplayName(?string $locale = null): string
    {
        $name = $this->getTranslatedName($locale);
        $status = $this->is_enabled ? 'Enabled' : 'Disabled';

        return "{$name} ({$status})";
    }

    // Eloquent attribute accessors for appended attributes
    public function getMetaTagsAttribute(): array
    {
        return $this->getMetaTags();
    }

    public function getTotalRevenueAttribute(): float
    {
        return $this->getTotalRevenue();
    }

    public function getAverageProductPriceAttribute(): ?float
    {
        return $this->getAverageProductPrice();
    }

    public function getWebsiteDomainAttribute(): ?string
    {
        return $this->getWebsiteDomain();
    }

    /**
     * Quickly determine whether the brand has been elevated to premium status.
     */
    public function isPremium(): bool
    {
        return (bool) $this->is_premium;
    }

    /**
     * Handle isActive functionality with proper error handling.
     */
    public function isActive(): bool
    {
        return $this->is_enabled;
    }

    /**
     * Handle hasProducts functionality with proper error handling.
     */
    public function hasProducts(): bool
    {
        return $this->products()->exists();
    }

    /**
     * Handle hasPublishedProducts functionality with proper error handling.
     */
    public function hasPublishedProducts(): bool
    {
        return $this->products()->published()->exists();
    }

    /**
     * Handle hasWebsite functionality with proper error handling.
     */
    public function hasWebsite(): bool
    {
        return ! empty($this->website);
    }

    /**
     * Handle hasAnyMedia functionality with proper error handling.
     */
    public function hasAnyMedia(): bool
    {
        return $this->hasMedia('logo') || $this->hasMedia('banner');
    }

    /**
     * Handle getWebsiteDomain functionality with proper error handling.
     */
    public function getWebsiteDomain(): ?string
    {
        if (! $this->website) {
            return null;
        }

        return parse_url($this->website, PHP_URL_HOST);
    }

    /**
     * Handle scopeActive functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Handle scopeInactive functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeInactive($query)
    {
        return $query->where('is_enabled', false);
    }

    /**
     * Handle scopeWithWebsite functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeWithWebsite($query)
    {
        return $query->whereNotNull('website')->where('website', '!=', '');
    }

    /**
     * Handle scopeWithoutWebsite functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeWithoutWebsite($query)
    {
        return $query->where(function ($q): void {
            $q->whereNull('website')->orWhere('website', '');
        });
    }

    /**
     * Handle scopeWithMedia functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeWithMedia($query)
    {
        return $query->whereHas('media');
    }

    /**
     * Provide a reusable way to order brands by their name field.
     */
    public function scopeOrderedByName(Builder $query, string $direction = 'asc'): Builder
    {
        // Sanitize the provided direction so only ASC or DESC are allowed.
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        // Return the modified query builder ordered by the human friendly brand name.
        return $query->orderBy('name', $direction);
    }

    /**
     * Handle scopeWithoutMedia functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeWithoutMedia($query)
    {
        return $query->whereDoesntHave('media');
    }

    /**
     * Handle scopePopular functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopePopular($query, int $minProducts = 5)
    {
        return $query->has('products', '>=', $minProducts);
    }

    /**
     * Handle scopeRecent functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Handle scopePremium functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    /**
     * Handle registerMediaCollections functionality with proper error handling.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile()->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml']);
        $this->addMediaCollection('banner')->singleFile()->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    /**
     * Handle registerMediaConversions functionality with proper error handling.
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

    /**
     * Normalise JSON social links so the admin repeater and API payloads stay consistent.
     */
    protected function socialLinks(): Attribute
    {
        return Attribute::make(
            get: function ($value): array {
                // Decode the stored payload if the array cast has not already run.
                $decoded = is_array($value) ? $value : (is_string($value) ? json_decode($value, true) : []);

                return collect($decoded)
                    ->filter(fn ($link): bool => is_array($link) && isset($link['platform'], $link['url']))
                    ->map(function (array $link): array {
                        // Cast individual entries to strings and keep the structure minimal.
                        return [
                            'platform' => (string) $link['platform'],
                            'url'      => (string) $link['url'],
                        ];
                    })
                    ->values()
                    ->all();
            },
            set: function ($value): array {
                // Accept repeater-style arrays or associative payloads from seeders.
                $normalized = collect(is_array($value) ? $value : [])
                    ->map(function ($link) {
                        if (! is_array($link)) {
                            return null;
                        }

                        $platform = strtolower((string) ($link['platform'] ?? ''));
                        $url = trim((string) ($link['url'] ?? ''));

                        if ($platform === '' || $url === '') {
                            return null;
                        }

                        if (! in_array($platform, self::SOCIAL_LINK_PLATFORMS, true)) {
                            return null;
                        }

                        return [
                            'platform' => $platform,
                            'url'      => $url,
                        ];
                    })
                    ->filter()
                    ->values()
                    ->all();
                // Return explicitly keyed JSON so Eloquent persists the sanitised payload consistently.
                return ['social_links' => json_encode($normalized)];
            },
        );
    }
}
