<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use App\Models\Scopes\VisibleScope;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[ScopedBy([ActiveScope::class, EnabledScope::class, VisibleScope::class])]
final /**
 * Category
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class Category extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'parent_id',
        'sort_order',
        'is_visible',
        'is_enabled',
        'seo_title',
        'seo_description',
        'show_in_menu',
        'product_limit',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'is_enabled' => 'boolean',
        'show_in_menu' => 'boolean',
        'sort_order' => 'integer',
        'product_limit' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'full_name',
        'breadcrumb',
        'canonical_url',
        'meta_tags',
        'total_revenue',
        'average_product_price',
        'is_root',
        'is_leaf',
        'depth',
        'level',
        'ancestors_count',
        'descendants_count',
        'full_path',
    ];

    protected string $translationModel = \App\Models\Translations\CategoryTranslation::class;

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Get the category's latest child.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestChild(): HasOne
    {
        return $this->children()->one()->latestOfMany();
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_categories');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeWithProductCounts($query)
    {
        return $query->withCount('products');
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeFeatured($query)
    {
        // Note: is_featured column doesn't exist in database
        // This scope is kept for compatibility but returns all categories
        return $query;
    }

    public function scopeWithChildren($query)
    {
        return $query->withCount('children');
    }

    public function scopeWithParent($query)
    {
        return $query->with('parent');
    }

    public function scopeWithAllRelations($query)
    {
        return $query->with(['parent', 'children', 'products', 'translations']);
    }

    public function getFullNameAttribute(): string
    {
        $name = $this->trans('name', app()->getLocale());

        if ($this->parent) {
            return $this->parent->getFullNameAttribute().' > '.$name;
        }

        return $name;
    }

    public function getBreadcrumbAttribute(): array
    {
        $breadcrumb = [];
        $category = $this;

        while ($category) {
            array_unshift($breadcrumb, [
                'id' => $category->id,
                'name' => $category->trans('name', app()->getLocale()),
                'slug' => $category->slug,
            ]);
            $category = $category->parent;
        }

        return $breadcrumb;
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

    public function getOrCreateTranslation(string $locale): \App\Models\Translations\CategoryTranslation
    {
        return $this->translations()->firstOrCreate(
            ['locale' => $locale],
            [
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
                'short_description' => $this->short_description,
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
    public function getCategoryInfo(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'parent_id' => $this->parent_id,
            'sort_order' => $this->sort_order,
            'is_visible' => $this->is_visible,
            'is_enabled' => $this->is_enabled,
            'show_in_menu' => $this->show_in_menu,
            'product_limit' => $this->product_limit,
        ];
    }

    public function getHierarchyInfo(): array
    {
        return [
            'is_root' => $this->isRoot(),
            'is_leaf' => $this->isLeaf(),
            'depth' => $this->getDepth(),
            'level' => $this->getLevel(),
            'ancestors_count' => $this->getAncestorsCount(),
            'descendants_count' => $this->getDescendantsCount(),
            'children_count' => $this->children()->count(),
            'parent_name' => $this->parent?->name,
            'full_path' => $this->getFullPath(),
            'breadcrumb' => $this->getBreadcrumbAttribute(),
        ];
    }

    public function getMediaInfo(): array
    {
        return [
            'has_image' => $this->hasMedia('images'),
            'has_banner' => $this->hasMedia('banner'),
            'has_gallery' => $this->hasMedia('gallery'),
            'image_url' => $this->getImageUrl(),
            'banner_url' => $this->getBannerUrl(),
            'gallery_count' => $this->getGalleryCount(),
            'media_count' => $this->getMediaCount(),
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
            'is_visible' => $this->is_visible,
            'show_in_menu' => $this->show_in_menu,
            'has_products' => $this->products()->exists(),
            'has_children' => $this->children()->exists(),
        ];
    }

    public function getCompleteInfo(?string $locale = null): array
    {
        return array_merge(
            $this->getCategoryInfo(),
            $this->getHierarchyInfo(),
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

    public function getAvailableLocales(): array
    {
        return $this->translations()->pluck('locale')->toArray();
    }

    // Additional helper methods
    public function getCanonicalUrl(): string
    {
        return route('categories.show', $this);
    }

    public function getMetaTags(): array
    {
        return [
            'title' => $this->seo_title ?: $this->name,
            'description' => $this->seo_description ?: $this->short_description ?: $this->description,
            'og:title' => $this->seo_title ?: $this->name,
            'og:description' => $this->seo_description ?: $this->short_description ?: $this->description,
            'og:image' => $this->getImageUrl(),
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
        $name = $this->trans('name', $locale) ?: $this->name;
        $status = $this->is_enabled ? 'Enabled' : 'Disabled';
        return "{$name} ({$status})";
    }

    // Hierarchy methods
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    public function isLeaf(): bool
    {
        return $this->children()->count() === 0;
    }

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

    public function getLevel(): int
    {
        return $this->getDepth() + 1;
    }

    public function getAncestorsCount(): int
    {
        return $this->getDepth();
    }

    public function getDescendantsCount(): int
    {
        $count = $this->children()->count();
        foreach ($this->children as $child) {
            $count += $child->getDescendantsCount();
        }
        return $count;
    }

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
    public function getImageUrl(?string $size = null): ?string
    {
        if (!$size) {
            return $this->getFirstMediaUrl('images');
        }
        
        return $this->getFirstMediaUrl('images', $size) ?: $this->getFirstMediaUrl('images');
    }

    public function getBannerUrl(?string $size = null): ?string
    {
        if (!$size) {
            return $this->getFirstMediaUrl('banner');
        }
        
        return $this->getFirstMediaUrl('banner', $size) ?: $this->getFirstMediaUrl('banner');
    }

    public function getGalleryCount(): int
    {
        return $this->getMedia('gallery')->count();
    }

    public function getMediaCount(): int
    {
        return $this->getMedia()->count();
    }

    // Status methods
    public function isActive(): bool
    {
        return $this->is_enabled;
    }

    public function isVisible(): bool
    {
        return $this->is_visible;
    }

    public function isFeatured(): bool
    {
        // Note: is_featured column doesn't exist in database
        // This method is kept for compatibility but always returns false
        return false;
    }

    public function showInMenu(): bool
    {
        return $this->show_in_menu;
    }

    public function hasProducts(): bool
    {
        return $this->products()->exists();
    }

    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    public function hasParent(): bool
    {
        return !is_null($this->parent_id);
    }

    // Additional scopes
    public function scopeWithoutParent($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeHidden($query)
    {
        return $query->where('is_visible', false);
    }

    public function scopeInMenu($query)
    {
        return $query->where('show_in_menu', true);
    }

    public function scopeNotInMenu($query)
    {
        return $query->where('show_in_menu', false);
    }

    public function scopePopular($query, int $minProducts = 5)
    {
        return $query->has('products', '>=', $minProducts);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

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

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
            ->singleFile();

        $this
            ->addMediaCollection('banner')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
            ->singleFile();

        $this
            ->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10);

        $this
            ->addMediaConversion('medium')
            ->width(600)
            ->height(600)
            ->sharpen(10);

        $this
            ->addMediaConversion('large')
            ->width(1200)
            ->height(1200)
            ->sharpen(10);
    }
}
