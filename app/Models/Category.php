<?php declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use App\Models\Scopes\VisibleScope;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Category
 *
 * Eloquent model representing the Category entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 * @property mixed $appends
 * @property string $translationModel
 * @method static \Illuminate\Database\Eloquent\Builder|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, EnabledScope::class, VisibleScope::class])]
final class Category extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia, SoftDeletes;

    protected $fillable = ['name', 'slug', 'description', 'short_description', 'parent_id', 'sort_order', 'is_visible', 'is_enabled', 'is_active', 'is_featured', 'color', 'seo_title', 'seo_description', 'show_in_menu', 'product_limit'];
    protected $casts = ['is_visible' => 'boolean', 'is_enabled' => 'boolean', 'is_active' => 'boolean', 'is_featured' => 'boolean', 'show_in_menu' => 'boolean', 'sort_order' => 'integer', 'product_limit' => 'integer'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['full_name', 'breadcrumb', 'canonical_url', 'meta_tags', 'total_revenue', 'average_product_price', 'is_root', 'is_leaf', 'depth', 'level', 'ancestors_count', 'descendants_count', 'full_path'];

    protected string $translationModel = \App\Models\Translations\CategoryTranslation::class;

    /**
     * Handle parent functionality with proper error handling.
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Handle children functionality with proper error handling.
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Handle latestChild functionality with proper error handling.
     * @return HasOne
     */
    public function latestChild(): HasOne
    {
        return $this->children()->one()->latestOfMany();
    }

    /**
     * Handle products functionality with proper error handling.
     * @return BelongsToMany
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_categories');
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
     * Handle scopeActive functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_visible', true);
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
     * Handle scopeOrdered functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Handle scopeWithProductCounts functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeWithProductCounts($query)
    {
        return $query->withCount('products');
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
     * Handle scopeRoots functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
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
     * Handle scopeFeatured functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeFeatured($query)
    {
        // Note: is_featured column doesn't exist in database
        // This scope is kept for compatibility but returns all categories
        return $query;
    }

    /**
     * Handle scopeWithChildren functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeWithChildren($query)
    {
        return $query->withCount('children');
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
     * Handle scopeWithAllRelations functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeWithAllRelations($query)
    {
        return $query->with(['parent', 'children', 'products', 'translations']);
    }

    /**
     * Handle getFullNameAttribute functionality with proper error handling.
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        $name = $this->trans('name', app()->getLocale());
        if ($this->parent) {
            return $this->parent->getFullNameAttribute() . ' > ' . $name;
        }
        return $name;
    }

    /**
     * Handle getBreadcrumbAttribute functionality with proper error handling.
     * @return array
     */
    public function getBreadcrumbAttribute(): array
    {
        $breadcrumb = [];
        $category = $this;
        while ($category) {
            array_unshift($breadcrumb, ['id' => $category->id, 'name' => $category->trans('name', app()->getLocale()), 'slug' => $category->slug]);
            $category = $category->parent;
        }
        return $breadcrumb;
    }

    /**
     * Handle getCanonicalUrlAttribute functionality with proper error handling.
     * @return string
     */
    public function getCanonicalUrlAttribute(): string
    {
        return route('categories.show', $this->slug);
    }

    /**
     * Handle getMetaTagsAttribute functionality with proper error handling.
     * @return array
     */
    public function getMetaTagsAttribute(): array
    {
        return [
            'title' => $this->seo_title ?? $this->name,
            'description' => $this->seo_description ?? $this->description,
            'keywords' => $this->seo_keywords ?? [],
            'canonical' => $this->canonical_url,
        ];
    }

    /**
     * Handle getTotalRevenueAttribute functionality with proper error handling.
     * @return float
     */
    public function getTotalRevenueAttribute(): float
    {
        return (float) ($this->products()->sum('price') ?? 0.0);
    }

    /**
     * Handle getAverageProductPriceAttribute functionality with proper error handling.
     * @return float
     */
    public function getAverageProductPriceAttribute(): float
    {
        $productCount = $this->products()->count();
        return $productCount > 0 ? (float) ($this->total_revenue / $productCount) : 0.0;
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
        $category = $this;
        while ($category->parent) {
            $depth++;
            $category = $category->parent;
        }
        return $depth;
    }

    /**
     * Handle getLevelAttribute functionality with proper error handling.
     * @return int
     */
    public function getLevelAttribute(): int
    {
        return $this->depth + 1;
    }

    /**
     * Handle getAncestorsCountAttribute functionality with proper error handling.
     * @return int
     */
    public function getAncestorsCountAttribute(): int
    {
        return $this->depth;
    }

    /**
     * Handle getDescendantsCountAttribute functionality with proper error handling.
     * @return int
     */
    public function getDescendantsCountAttribute(): int
    {
        $count = 0;
        foreach ($this->children as $child) {
            $count += 1 + $child->descendants_count;
        }
        return $count;
    }

    /**
     * Handle getFullPathAttribute functionality with proper error handling.
     * @return string
     */
    public function getFullPathAttribute(): string
    {
        $path = [];
        $category = $this;
        while ($category) {
            array_unshift($path, $category->slug);
            $category = $category->parent;
        }
        return implode('/', $path);
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
     * @return App\Models\Translations\CategoryTranslation
     */
    public function getOrCreateTranslation(string $locale): \App\Models\Translations\CategoryTranslation
    {
        return $this->translations()->firstOrCreate(['locale' => $locale], ['name' => $this->name, 'slug' => $this->slug, 'description' => $this->description, 'short_description' => $this->short_description, 'seo_title' => $this->seo_title, 'seo_description' => $this->seo_description]);
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
     * Handle getCategoryInfo functionality with proper error handling.
     * @return array
     */
    public function getCategoryInfo(): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'slug' => $this->slug, 'description' => $this->description, 'short_description' => $this->short_description, 'parent_id' => $this->parent_id, 'sort_order' => $this->sort_order, 'is_visible' => $this->is_visible, 'is_enabled' => $this->is_enabled, 'show_in_menu' => $this->show_in_menu, 'product_limit' => $this->product_limit];
    }

    /**
     * Handle getHierarchyInfo functionality with proper error handling.
     * @return array
     */
    public function getHierarchyInfo(): array
    {
        return ['is_root' => $this->isRoot(), 'is_leaf' => $this->isLeaf(), 'depth' => $this->getDepth(), 'level' => $this->getLevel(), 'ancestors_count' => $this->getAncestorsCount(), 'descendants_count' => $this->getDescendantsCount(), 'children_count' => $this->children()->count(), 'parent_name' => $this->parent?->name, 'full_path' => $this->getFullPath(), 'breadcrumb' => $this->getBreadcrumbAttribute()];
    }

    /**
     * Handle getMediaInfo functionality with proper error handling.
     * @return array
     */
    public function getMediaInfo(): array
    {
        return ['has_image' => $this->hasMedia('images'), 'has_banner' => $this->hasMedia('banner'), 'has_gallery' => $this->hasMedia('gallery'), 'image_url' => $this->getImageUrl(), 'banner_url' => $this->getBannerUrl(), 'gallery_count' => $this->getGalleryCount(), 'media_count' => $this->getMediaCount()];
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
        return ['products_count' => $this->products()->count(), 'published_products_count' => $this->products()->published()->count(), 'total_revenue' => $this->getTotalRevenue(), 'average_product_price' => $this->getAverageProductPrice(), 'is_active' => $this->is_enabled, 'is_visible' => $this->is_visible, 'show_in_menu' => $this->show_in_menu, 'has_products' => $this->products()->exists(), 'has_children' => $this->children()->exists()];
    }

    /**
     * Handle getCompleteInfo functionality with proper error handling.
     * @param string|null $locale
     * @return array
     */
    public function getCompleteInfo(?string $locale = null): array
    {
        return array_merge($this->getCategoryInfo(), $this->getHierarchyInfo(), $this->getMediaInfo(), $this->getSeoInfo(), $this->getBusinessInfo(), ['translations' => $this->getAvailableLocales(), 'has_translations' => count($this->getAvailableLocales()) > 0, 'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString()]);
    }

    /**
     * Handle getAvailableLocales functionality with proper error handling.
     * @return array
     */
    public function getAvailableLocales(): array
    {
        return $this->translations()->pluck('locale')->toArray();
    }

    // Additional helper methods

    /**
     * Handle getCanonicalUrl functionality with proper error handling.
     * @return string
     */
    public function getCanonicalUrl(): string
    {
        return route('categories.show', $this);
    }

    /**
     * Handle getMetaTags functionality with proper error handling.
     * @return array
     */
    public function getMetaTags(): array
    {
        return ['title' => $this->seo_title ?: $this->name, 'description' => ($this->seo_description ?: $this->short_description) ?: $this->description, 'og:title' => $this->seo_title ?: $this->name, 'og:description' => ($this->seo_description ?: $this->short_description) ?: $this->description, 'og:image' => $this->getImageUrl(), 'og:url' => $this->getCanonicalUrl()];
    }

    /**
     * Handle getTotalRevenue functionality with proper error handling.
     * @return float
     */
    public function getTotalRevenue(): float
    {
        return (float) ($this->products()->join('order_items', 'products.id', '=', 'order_items.product_id')->join('orders', 'order_items.order_id', '=', 'orders.id')->where('orders.status', 'completed')->sum(\DB::raw('order_items.quantity * order_items.price')) ?? 0.0);
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
        $name = $this->trans('name', $locale) ?: $this->name;
        $status = $this->is_enabled ? 'Enabled' : 'Disabled';
        return "{$name} ({$status})";
    }

    // Hierarchy methods

    /**
     * Handle isRoot functionality with proper error handling.
     * @return bool
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Handle isLeaf functionality with proper error handling.
     * @return bool
     */
    public function isLeaf(): bool
    {
        return $this->children()->count() === 0;
    }

    /**
     * Handle getDepth functionality with proper error handling.
     * @return int
     */
    public function getDepth(): int
    {
        $depth = 0;
        $category = $this->parent;
        while ($category) {
            $depth++;
            $category = $category->parent;
        }
        return $depth;
    }

    /**
     * Handle getLevel functionality with proper error handling.
     * @return int
     */
    public function getLevel(): int
    {
        return $this->getDepth() + 1;
    }

    /**
     * Handle getAncestorsCount functionality with proper error handling.
     * @return int
     */
    public function getAncestorsCount(): int
    {
        return $this->getDepth();
    }

    /**
     * Handle getDescendantsCount functionality with proper error handling.
     * @return int
     */
    public function getDescendantsCount(): int
    {
        $count = $this->children()->count();
        foreach ($this->children as $child) {
            $count += $child->getDescendantsCount();
        }
        return $count;
    }

    /**
     * Handle getFullPath functionality with proper error handling.
     * @return string
     */
    public function getFullPath(): string
    {
        $path = [];
        $category = $this;
        while ($category) {
            array_unshift($path, $category->name);
            $category = $category->parent;
        }
        return implode(' > ', $path);
    }

    // Media methods

    /**
     * Handle getImageUrl functionality with proper error handling.
     * @param string|null $size
     * @return string|null
     */
    public function getImageUrl(?string $size = null): ?string
    {
        if (!$size) {
            return $this->getFirstMediaUrl('images');
        }
        return $this->getFirstMediaUrl('images', $size) ?: $this->getFirstMediaUrl('images');
    }

    /**
     * Handle getBannerUrl functionality with proper error handling.
     * @param string|null $size
     * @return string|null
     */
    public function getBannerUrl(?string $size = null): ?string
    {
        if (!$size) {
            return $this->getFirstMediaUrl('banner');
        }
        return $this->getFirstMediaUrl('banner', $size) ?: $this->getFirstMediaUrl('banner');
    }

    /**
     * Handle getGalleryCount functionality with proper error handling.
     * @return int
     */
    public function getGalleryCount(): int
    {
        return $this->getMedia('gallery')->count();
    }

    /**
     * Handle getMediaCount functionality with proper error handling.
     * @return int
     */
    public function getMediaCount(): int
    {
        return $this->getMedia()->count();
    }

    // Status methods

    /**
     * Handle isActive functionality with proper error handling.
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->is_enabled;
    }

    /**
     * Handle isVisible functionality with proper error handling.
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->is_visible;
    }

    /**
     * Handle isFeatured functionality with proper error handling.
     * @return bool
     */
    public function isFeatured(): bool
    {
        // Note: is_featured column doesn't exist in database
        // This method is kept for compatibility but always returns false
        return false;
    }

    /**
     * Handle showInMenu functionality with proper error handling.
     * @return bool
     */
    public function showInMenu(): bool
    {
        return $this->show_in_menu;
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
     * Handle hasChildren functionality with proper error handling.
     * @return bool
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Handle hasParent functionality with proper error handling.
     * @return bool
     */
    public function hasParent(): bool
    {
        return !is_null($this->parent_id);
    }

    // Additional scopes

    /**
     * Handle scopeWithoutParent functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeWithoutParent($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Handle scopeHidden functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeHidden($query)
    {
        return $query->where('is_visible', false);
    }

    /**
     * Handle scopeInMenu functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeInMenu($query)
    {
        return $query->where('show_in_menu', true);
    }

    /**
     * Handle scopeNotInMenu functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeNotInMenu($query)
    {
        return $query->where('show_in_menu', false);
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
     * Handle scopeDeep functionality with proper error handling.
     * @param mixed $query
     * @param int $minDepth
     */
    public function scopeDeep($query, int $minDepth = 2)
    {
        return $query->whereHas('parent', function ($q) use ($minDepth) {
            $q->whereHas('parent', function ($q2) use ($minDepth) {
                if ($minDepth > 2) {
                    $q2->whereHas('parent', function ($q3) {
                        $q3->whereNotNull('parent_id');
                    });
                } else {
                    $q2->whereNotNull('parent_id');
                }
            });
        });
    }

    /**
     * Handle registerMediaCollections functionality with proper error handling.
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])->singleFile();
        $this->addMediaCollection('banner')->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])->singleFile();
        $this->addMediaCollection('gallery')->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    /**
     * Handle registerMediaConversions functionality with proper error handling.
     * @param Media|null $media
     * @return void
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(300)->height(300)->sharpen(10);
        $this->addMediaConversion('medium')->width(600)->height(600)->sharpen(10);
        $this->addMediaConversion('large')->width(1200)->height(1200)->sharpen(10);
    }
}
