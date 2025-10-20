<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * NewsImage
 *
 * Eloquent model representing the NewsImage entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|NewsImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsImage query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class NewsImage extends Model
{
    use HasFactory;

    protected $table = 'news_images';

    protected $fillable = ['news_id', 'file_path', 'alt_text', 'caption', 'is_featured', 'sort_order', 'file_size', 'mime_type', 'dimensions'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['news_id' => 'integer', 'is_featured' => 'boolean', 'sort_order' => 'integer', 'file_size' => 'integer', 'dimensions' => 'array'];
    }

    /**
     * Handle news functionality with proper error handling.
     */
    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class);
    }

    /**
     * Handle scopeFeatured functionality with proper error handling.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Handle scopeOrdered functionality with proper error handling.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Handle getUrlAttribute functionality with proper error handling.
     */
    public function getUrlAttribute(): string
    {
        if (filter_var($this->file_path, FILTER_VALIDATE_URL)) {
            return $this->file_path;
        }

        return url('storage/' . ltrim($this->file_path, '/'));
    }

    /**
     * Handle getThumbnailUrlAttribute functionality with proper error handling.
     */
    public function getThumbnailUrlAttribute(): string
    {
        $pathInfo = pathinfo($this->file_path);
        $thumbnailPath = $pathInfo['dirname'] . '/thumbnails/' . $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];

        return url('storage/' . ltrim($thumbnailPath, '/'));
    }

    /**
     * Handle isImage functionality with proper error handling.
     */
    public function isImage(): bool
    {
        $mime = (string) ($this->mime_type ?? '');

        return $mime !== '' && str_starts_with($mime, 'image/');
    }

    public function getIsImageAttribute(): bool
    {
        return $this->isImage();
    }

    /**
     * Handle getFileSizeFormattedAttribute functionality with proper error handling.
     */
    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = (float) ($this->file_size ?? 0);
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return number_format($bytes, 2, '.', '') . ' ' . $units[$i];
    }
}
