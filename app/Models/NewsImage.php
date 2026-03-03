<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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

    protected static function booted(): void
    {
        self::saving(static function (NewsImage $image): void {
            $image->normalizeSortOrder();
            $image->populateFileMetadata();
            $image->ensureSingleFeaturedImage();
        });
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
     * Handle scopeOrderedByName functionality with proper error handling.
     *
     * Ordering by caption keeps the listing predictable for administrators
     * who browse image records alphabetically, while falling back to the id
     * provides deterministic ordering for identical captions.
     */
    public function scopeOrderedByName(Builder $query): Builder
    {
        return $query->orderBy('caption')->orderBy('id');
    }

    /**
     * Handle getUrlAttribute functionality with proper error handling.
     */
    public function getUrlAttribute(): string
    {
        if (filter_var($this->file_path, FILTER_VALIDATE_URL)) {
            return $this->file_path;
        }

        return $this->buildAbsoluteUrl('storage/' . ltrim($this->file_path, '/'));
    }

    /**
     * Handle getThumbnailUrlAttribute functionality with proper error handling.
     */
    public function getThumbnailUrlAttribute(): string
    {
        $pathInfo = pathinfo($this->file_path);
        $directory = $pathInfo['dirname'] ?? '';
        $directory = $directory === '.' ? '' : trim($directory, '/');
        $thumbnailPath = ($directory !== '' ? $directory . '/' : '') . 'thumbnails/' . $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];

        return $this->buildAbsoluteUrl('storage/' . ltrim($thumbnailPath, '/'));
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

    private function normalizeSortOrder(): void
    {
        if (! is_numeric($this->news_id)) {
            return;
        }

        if (is_numeric($this->sort_order)) {
            return;
        }

        $maxSortOrder = self::withoutGlobalScopes()
            ->where('news_id', (int) $this->news_id)
            ->max('sort_order');

        $this->sort_order = is_numeric($maxSortOrder) ? ((int) $maxSortOrder + 1) : 0;
    }

    private function ensureSingleFeaturedImage(): void
    {
        if (! $this->is_featured || ! is_numeric($this->news_id)) {
            return;
        }

        $query = self::withoutGlobalScopes()
            ->where('news_id', (int) $this->news_id);

        if ($this->exists) {
            $query->whereKeyNot($this->getKey());
        }

        $query->update(['is_featured' => false]);
    }

    private function populateFileMetadata(): void
    {
        $path = (string) ($this->file_path ?? '');

        if ($path === '' || filter_var($path, FILTER_VALIDATE_URL) !== false || ! is_numeric($this->news_id)) {
            return;
        }

        $metadataAlreadyLoaded = ! blank($this->mime_type)
            && is_numeric($this->file_size)
            && is_array($this->dimensions);

        if (! $this->isDirty('file_path') && $metadataAlreadyLoaded) {
            return;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return;
        }

        $this->file_size = $disk->size($path) ?: $this->file_size;
        $this->mime_type = $disk->mimeType($path) ?: $this->mime_type;

        $absolutePath = $disk->path($path);

        if (! is_file($absolutePath)) {
            return;
        }

        $dimensions = @getimagesize($absolutePath);

        if (! is_array($dimensions) || ! isset($dimensions[0], $dimensions[1])) {
            return;
        }

        $this->dimensions = [
            'width'  => $dimensions[0],
            'height' => $dimensions[1],
        ];
    }

    private function buildAbsoluteUrl(string $path): string
    {
        $root = (string) config('app.url', '');

        if ($root === '') {
            $root = url('/');
        }

        return rtrim($root, '/') . '/' . ltrim($path, '/');
    }
}
