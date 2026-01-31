<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

/**
 * File
 *
 * @property int                             $id
 * @property string                          $name
 * @property string                          $original_name
 * @property string                          $path
 * @property string                          $disk
 * @property string                          $mime_type
 * @property int                             $size
 * @property string|null                     $hash
 * @property string                          $fileable_type
 * @property int                             $fileable_id
 * @property int                             $uploaded_by
 * @property array|null                      $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static Builder|File byMimeType(string $mimeType)
 * @method static Builder|File images()
 * @method static Builder|File documents()
 */
final class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'original_name',
        'path',
        'disk',
        'mime_type',
        'size',
        'hash',
        'fileable_type',
        'fileable_id',
        'uploaded_by',
        'metadata',
    ];

    protected $casts = [
        'size'     => 'integer',
        'metadata' => 'array',
    ];

    // Relationships

    /**
     * The model this file belongs to (polymorphic).
     */
    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * User who uploaded the file.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Scopes

    public function scopeByMimeType(Builder $query, string $mimeType): Builder
    {
        return $query->where('mime_type', $mimeType);
    }

    public function scopeImages(Builder $query): Builder
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    public function scopeDocuments(Builder $query): Builder
    {
        return $query->whereIn('mime_type', [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function scopeByUploader(Builder $query, User $user): Builder
    {
        return $query->where('uploaded_by', $user->id);
    }

    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helper methods

    /**
     * Get file URL.
     */
    public function getUrl(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Get human readable file size.
     */
    public function getHumanSize(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if file is an image.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if file is a document.
     */
    public function isDocument(): bool
    {
        return in_array($this->mime_type, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
        ]);
    }

    /**
     * Delete file from storage.
     */
    public function deleteFromStorage(): bool
    {
        return Storage::disk($this->disk)->delete($this->path);
    }

    /**
     * Check if file exists in storage.
     */
    public function existsInStorage(): bool
    {
        return Storage::disk($this->disk)->exists($this->path);
    }
}
