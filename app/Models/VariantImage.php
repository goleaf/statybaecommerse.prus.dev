<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * VariantImage
 *
 * Eloquent model representing the VariantImage entity for variant-specific images.
 *
 * @property mixed $table
 * @property mixed $fillable
 * @property mixed $casts
 * @property mixed $appends
 *
 * @method static \Illuminate\Database\Eloquent\Builder|VariantImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VariantImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VariantImage query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, EnabledScope::class])]
final class VariantImage extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $table = 'variant_images';

    protected $fillable = [
        'variant_id',
        'image_path',
        'alt_text',
        'sort_order',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_primary' => 'boolean',
        ];
    }

    protected $appends = [
        'image_url',
        'thumbnail_url',
        'formatted_alt_text',
    ];

    /**
     * Handle variant functionality with proper error handling.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Handle getImageUrlAttribute functionality with proper error handling.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return asset('storage/'.$this->image_path);
    }

    /**
     * Handle getThumbnailUrlAttribute functionality with proper error handling.
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        $pathInfo = pathinfo($this->image_path);
        $extension = $pathInfo['extension'] ?? 'jpg';
        $thumbnailPath = $pathInfo['dirname'].'/thumbnails/'.$pathInfo['filename'].'_thumb.'.$extension;

        return asset('storage/'.$thumbnailPath);
    }

    /**
     * Handle getFormattedAltTextAttribute functionality with proper error handling.
     */
    public function getFormattedAltTextAttribute(): string
    {
        if ($this->alt_text) {
            return $this->alt_text;
        }

        return $this->variant ? $this->variant->display_name.' - Variant Image' : 'Variant Image';
    }

    /**
     * Handle scopePrimary functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Handle scopeOrdered functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Handle scopeByVariant functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByVariant($query, int $variantId)
    {
        return $query->where('variant_id', $variantId);
    }

    /**
     * Handle registerMediaCollections functionality with proper error handling.
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('variant_images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
            ->singleFile();
    }

    /**
     * Handle registerMediaConversions functionality with proper error handling.
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
     * Set as primary image for the variant.
     */
    public function setAsPrimary(): bool
    {
        // Remove primary status from other images of the same variant
        self::where('variant_id', $this->variant_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        // Set this image as primary
        $this->is_primary = true;

        return $this->save();
    }

    /**
     * Get image dimensions.
     */
    public function getImageDimensions(): ?array
    {
        if (! $this->image_path || ! file_exists(storage_path('app/public/'.$this->image_path))) {
            return null;
        }

        $imagePath = storage_path('app/public/'.$this->image_path);
        $imageInfo = getimagesize($imagePath);

        if (! $imageInfo) {
            return null;
        }

        return [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'mime_type' => $imageInfo['mime'],
        ];
    }

    /**
     * Get image file size.
     */
    public function getImageFileSize(): ?int
    {
        if (! $this->image_path || ! file_exists(storage_path('app/public/'.$this->image_path))) {
            return null;
        }

        return filesize(storage_path('app/public/'.$this->image_path));
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedFileSize(): ?string
    {
        $fileSize = $this->getImageFileSize();

        if (! $fileSize) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($fileSize >= 1024 && $unitIndex < count($units) - 1) {
            $fileSize /= 1024;
            $unitIndex++;
        }

        return round($fileSize, 2).' '.$units[$unitIndex];
    }
}
