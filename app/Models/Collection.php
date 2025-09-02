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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(200)
            ->sharpen(10);

        $this->addMediaConversion('small')
            ->width(400)
            ->height(400)
            ->sharpen(10);
    }
}
