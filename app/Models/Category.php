<?php declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

final class Category extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, HasTranslations, InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'parent_id',
        'sort_order',
        'is_visible',
        'is_enabled',
        'is_featured',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'show_in_menu',
        'product_limit',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'is_enabled' => 'boolean',
        'is_featured' => 'boolean',
        'show_in_menu' => 'boolean',
        'sort_order' => 'integer',
        'product_limit' => 'integer',
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
        return $query->where('is_featured', true);
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
            return $this->parent->getFullNameAttribute() . ' > ' . $name;
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

    public function registerMediaConversions(Media $media = null): void
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
