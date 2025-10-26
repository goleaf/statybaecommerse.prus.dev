<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Support\Storage\SecureStorage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * VariantImage
 *
 * Eloquent model representing the VariantImage entity for variant-specific images.
 * The class provides helper accessors and scopes that are exercised heavily by the
 * accompanying unit tests as well as the Filament resources that manage product
 * variant images throughout the administration panel.
 */
final class VariantImage extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use OrdersByName;
    use SoftDeletes;

    /**
     * Order variant images by their alt text label via the shared trait.
     */
    protected string $nameColumn = 'alt_text';

    /**
     * The database table associated with the model.
     */
    protected $table = 'variant_images';

    /**
     * The attributes that can be mass-assigned.
     */
    protected $fillable = [
        'variant_id',
        'image_path',
        'alt_text',
        'description',
        'sort_order',
        'is_primary',
        'is_active',
        'file_size',
        'dimensions',
    ];

    /**
     * Attribute casting definition so boolean/integer values behave predictably.
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_primary' => 'boolean',
            'is_active'  => 'boolean',
            'file_size'  => 'integer',
        ];
    }

    /**
     * Accessors that should be included on array/json representations.
     */
    protected $appends = [
        'image_url',
        'thumbnail_url',
        'formatted_alt_text',
        'formatted_file_size',
        'formatted_dimensions',
        'image_exists',
    ];

    /**
     * Provide convenient access to the owning variant record.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Generate the full image URL used by the storefront.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        // Using the storage helper keeps the behaviour consistent during testing and production.
        return asset('storage/' . ltrim($this->image_path, '/'));
    }

    /**
     * Generate the thumbnail URL by deriving the thumbnail naming convention.
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        $pathInfo = pathinfo($this->image_path);
        $extension = $pathInfo['extension'] ?? 'jpg';
        $thumbnailPath = $pathInfo['dirname'] . '/thumbnails/' . $pathInfo['filename'] . '_thumb.' . $extension;

        return asset('storage/' . ltrim($thumbnailPath, '/'));
    }

    /**
     * Derive a human readable fallback for missing alt text.
     */
    public function getFormattedAltTextAttribute(): string
    {
        if ($this->alt_text) {
            return $this->alt_text;
        }

        return $this->variant ? $this->variant->display_name . ' - Variant Image' : 'Variant Image';
    }

    /**
     * Scope that restricts the query to primary images only.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope that restricts the query to active images.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope that ensures a stable ordering by sort order and id.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Named scope used in tests to filter by variant.
     */
    public function scopeForVariant($query, int $variantId)
    {
        return $query->where('variant_id', $variantId);
    }

    /**
     * Backwards-compatible alias for ordering when called statically.
     */
    public function scopeOrderBySortOrder($query)
    {
        // Reuse the ordered scope logic for backwards compatibility.
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Scope helper used by legacy code.
     */
    public function scopeByVariant($query, int $variantId)
    {
        // Provide compatibility with existing scope naming across the codebase.
        return $query->where('variant_id', $variantId);
    }

    /**
     * Register the media collections handled by Spatie Media Library.
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('variant_images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
            ->singleFile();
    }

    /**
     * Register the thumbnail conversions for uploaded media files.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10)
            ->optimize();

        $this
            ->addMediaConversion('small')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->optimize();

        $this
            ->addMediaConversion('medium')
            ->width(600)
            ->height(600)
            ->sharpen(10)
            ->optimize();

        $this
            ->addMediaConversion('large')
            ->width(1200)
            ->height(1200)
            ->sharpen(10)
            ->optimize();
    }

    /**
     * Mark the image as the primary image for the variant.
     */
    public function markAsPrimary(): bool
    {
        // Remove primary status from other images of the same variant to keep one primary record.
        self::where('variant_id', $this->variant_id)
            ->whereKeyNot($this->getKey())
            ->update(['is_primary' => false]);

        $this->is_primary = true;

        return $this->save();
    }

    /**
     * Helper to unset the primary flag.
     */
    public function unmarkAsPrimary(): bool
    {
        $this->is_primary = false;

        return $this->save();
    }

    /**
     * Convenience wrapper for existing naming expectations.
     */
    public function setAsPrimary(): bool
    {
        return $this->markAsPrimary();
    }

    /**
     * Activate the image so it becomes visible in listings.
     */
    public function activate(): bool
    {
        $this->is_active = true;

        return $this->save();
    }

    /**
     * Deactivate the image when it should be hidden without deletion.
     */
    public function deactivate(): bool
    {
        $this->is_active = false;

        return $this->save();
    }

    /**
     * Determine the next sort order for a given variant.
     */
    public static function getNextSortOrder(int $variantId): int
    {
        $maxSortOrder = self::where('variant_id', $variantId)->max('sort_order');

        return $maxSortOrder !== null ? $maxSortOrder + 1 : 1;
    }

    /**
     * Reorder images based on the provided id => order mapping.
     */
    public static function reorderImages(int $variantId, array $orders): void
    {
        // We iterate once to avoid repeated save events for untouched rows.
        self::where('variant_id', $variantId)
            ->whereIn('id', array_keys($orders))
            ->get()
            ->each(function (self $image) use ($orders): void {
                $newOrder = $orders[$image->id] ?? null;

                if ($newOrder === null || $image->sort_order === $newOrder) {
                    return;
                }

                $image->sort_order = (int) $newOrder;
                $image->save();
            });
    }

    /**
     * Fetch the primary image for the provided variant id.
     */
    public static function getPrimaryForVariant(int $variantId): ?self
    {
        return self::forVariant($variantId)->primary()->first();
    }

    /**
     * Retrieve all images for a variant ordered predictably.
     */
    public static function getAllForVariant(int $variantId)
    {
        return self::forVariant($variantId)->orderBySortOrder()->get();
    }

    /**
     * Count the images belonging to a variant.
     */
    public static function countForVariant(int $variantId): int
    {
        return (int) self::forVariant($variantId)->count();
    }

    /**
     * Determine the raw image dimensions either from stored data or the filesystem.
     */
    public function getImageDimensions(): ?array
    {
        $dimensions = $this->parseDimensions($this->dimensions);
        if ($dimensions !== null) {
            return $dimensions;
        }

        if (! $this->image_path) {
            return null;
        }

        $disk = SecureStorage::disk();
        if (! Storage::disk($disk)->exists($this->image_path)) {
            return null;
        }

        $absolutePath = Storage::disk($disk)->path($this->image_path);
        if (! file_exists($absolutePath)) {
            return null;
        }

        $imageInfo = @getimagesize($absolutePath);
        if ($imageInfo === false) {
            return null;
        }

        return [
            'width'     => $imageInfo[0],
            'height'    => $imageInfo[1],
            'mime_type' => $imageInfo['mime'] ?? null,
        ];
    }

    /**
     * Resolve the file size using the stored value or by querying the storage disk.
     */
    public function getImageFileSize(): ?int
    {
        if ($this->file_size !== null) {
            return (int) $this->file_size;
        }

        if (! $this->image_path) {
            return null;
        }

        $disk = SecureStorage::disk();
        if (! Storage::disk($disk)->exists($this->image_path)) {
            return null;
        }

        return Storage::disk($disk)->size($this->image_path);
    }

    /**
     * Format the raw file size into a human readable string.
     */
    public function getFormattedFileSizeAttribute(): ?string
    {
        $fileSize = $this->getImageFileSize();

        if ($fileSize === null) {
            return null;
        }

        return $this->formatBytes($fileSize);
    }

    /**
     * Provide the formatted dimensions accessor expected by the tests.
     */
    public function getFormattedDimensionsAttribute(): ?string
    {
        $dimensions = $this->parseDimensions($this->dimensions);

        if ($dimensions !== null) {
            return $dimensions['width'] . ' × ' . $dimensions['height'];
        }

        $rawDimensions = $this->getImageDimensions();
        if ($rawDimensions === null) {
            return null;
        }

        return $rawDimensions['width'] . ' × ' . $rawDimensions['height'];
    }

    /**
     * Quickly determine whether the image exists on the configured disk.
     */
    public function getImageExistsAttribute(): bool
    {
        if (! $this->image_path) {
            return false;
        }

        return Storage::disk(SecureStorage::disk())->exists($this->image_path);
    }

    /**
     * Package relevant metadata in a structured array.
     */
    public function getImageMetadata(): array
    {
        return [
            'file_size'  => $this->getImageFileSize(),
            'dimensions' => $this->dimensions,
        ];
    }

    /**
     * Internal helper to parse a WxH formatted string.
     */
    protected function parseDimensions(?string $dimensions): ?array
    {
        if ($dimensions === null || $dimensions === '') {
            return null;
        }

        $parts = array_map('trim', explode('x', strtolower((string) $dimensions)));
        if (count($parts) !== 2) {
            return null;
        }

        [$width, $height] = $parts;
        if (! is_numeric($width) || ! is_numeric($height)) {
            return null;
        }

        return [
            'width'  => (int) $width,
            'height' => (int) $height,
        ];
    }

    /**
     * Helper that converts bytes into a human readable string.
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = (float) $bytes;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return number_format($size, 2) . ' ' . $units[$unitIndex];
    }
}
