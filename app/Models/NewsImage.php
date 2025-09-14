<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy([ActiveScope::class])]
final /**
 * NewsImage
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class NewsImage extends Model
{
    use HasFactory;

    protected $table = 'news_images';

    protected $fillable = [
        'news_id',
        'file_path',
        'alt_text',
        'caption',
        'is_featured',
        'sort_order',
        'file_size',
        'mime_type',
        'dimensions',
    ];

    protected function casts(): array
    {
        return [
            'news_id' => 'integer',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'file_size' => 'integer',
            'dimensions' => 'array',
        ];
    }

    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/'.$this->file_path);
    }

    public function getThumbnailUrlAttribute(): string
    {
        $pathInfo = pathinfo($this->file_path);
        $thumbnailPath = $pathInfo['dirname'].'/thumbnails/'.$pathInfo['filename'].'_thumb.'.$pathInfo['extension'];

        return asset('storage/'.$thumbnailPath);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
