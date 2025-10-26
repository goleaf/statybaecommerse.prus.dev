<?php declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * ProductImage
 *
 * Eloquent model representing the ProductImage entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property int                             $id
 * @property int                             $product_id
 * @property string                          $path
 * @property string|null                     $alt_text
 * @property int                             $sort_order
 * @property bool                            $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Product $product
 * @property-read string $url
 * @property-read string $full_path
 * @property-read bool $exists_on_disk
 *
 * @method static \Illuminate\Database\Eloquent\Builder<ProductImage> newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<ProductImage> newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<ProductImage> query()
 * @method static \Illuminate\Database\Eloquent\Builder<ProductImage> active()
 * @method static \Illuminate\Database\Eloquent\Builder<ProductImage> forProduct(int $productId)
 * @method static \Illuminate\Database\Eloquent\Builder<ProductImage> ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<ProductImage> primary()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class ProductImage extends Model
{
    /** @use HasFactory<\Database\Factories\ProductImageFactory> */
    use HasFactory;
    use OrdersByName;

    protected $table = 'product_images';

    protected $fillable = ['product_id', 'path', 'alt_text', 'sort_order', 'is_active'];

    /**
     * Favour the alt_text column when sorting images so editors see human readable values first.
     */
    protected string $nameColumn = 'alt_text';

    protected $attributes = [
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the product that owns the image.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the public URL for the image.
     */
    public function getUrlAttribute(): string
    {
        return $this->resolvePublicUrl($this->path);
    }

    /**
     * Get the full storage path for the image.
     */
    public function getFullPathAttribute(): string
    {
        return storage_path('app/public/' . ltrim($this->path, '/'));
    }

    /**
     * Check if the image file exists on disk.
     */
    public function getExistsOnDiskAttribute(): bool
    {
        /** @var string $disk */
        $disk = config('filesystems.default', 'public');

        return Storage::disk($disk)->exists($this->path);
    }

    /**
     * Scope a query to only include active images.
     *
     * @param  Builder<ProductImage> $query
     * @return Builder<ProductImage>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include images for a specific product.
     *
     * @param  Builder<ProductImage> $query
     * @return Builder<ProductImage>
     */
    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope a query to order images by sort_order.
     *
     * @param  Builder<ProductImage> $query
     * @return Builder<ProductImage>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Scope a query to get the primary (first) image.
     *
     * @param  Builder<ProductImage> $query
     * @return Builder<ProductImage>
     */
    public function scopePrimary(Builder $query): Builder
    {
        /** @var Builder<ProductImage> $orderedQuery */
        $orderedQuery = $query->orderBy('sort_order')->orderBy('id');

        return $orderedQuery->limit(1);
    }

    /**
     * Check if this is the primary image for the product.
     */
    public function isPrimary(): bool
    {
        return $this->sort_order === 0 ||
            $this->id === self::forProduct($this->product_id)
                ->ordered()
                ->value('id');
    }

    /**
     * Get the alt text or generate a default one.
     */
    public function getAltTextOrDefault(): string
    {
        if ($this->alt_text !== null && trim($this->alt_text) !== '') {
            return $this->alt_text;
        }

        $product = $this->relationLoaded('product') ? $this->getRelation('product') : null;

        if ($product instanceof Product) {
            return $product->name . ' image';
        }

        return 'Product image';
    }

    /**
     * Resolve the public URL for the image path.
     */
    private function resolvePublicUrl(string $path): string
    {
        if ($path === '') {
            return $path;
        }

        // Check if already an absolute URL
        $absolutePrefixes = ['http://', 'https://'];
        foreach ($absolutePrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return $path;
            }
        }

        // Check if it's a relative path starting with /
        if (str_starts_with($path, '/')) {
            return asset(ltrim($path, '/'));
        }

        // Try to find the file in storage
        /** @var string $defaultDisk */
        $defaultDisk = config('filesystems.default', 'public');
        $disksToCheck = array_unique([$defaultDisk, 'public']);

        foreach ($disksToCheck as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->url($path);
            }
        }

        // Fall back to the public disk URL
        return Storage::disk('public')->url($path);
    }
}
