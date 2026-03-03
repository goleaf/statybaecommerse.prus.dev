<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Storage\SecureStorage;
use Database\Factories\BrochureFileFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

final class BrochureFile extends Model
{
    /** @use HasFactory<BrochureFileFactory> */
    use HasFactory;

    protected $table = 'brochure_files';

    protected $fillable = [
        'brochure_id',
        'name',
        'file_path',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'brochure_id' => 'integer',
            'is_active'   => 'boolean',
            'sort_order'  => 'integer',
        ];
    }

    protected static function booted(): void
    {
        self::updating(static function (BrochureFile $brochureFile): void {
            if (! $brochureFile->isDirty('file_path')) {
                return;
            }

            $oldPath = trim((string) $brochureFile->getOriginal('file_path'));
            $newPath = trim((string) $brochureFile->file_path);

            if ($oldPath === '' || $oldPath === $newPath) {
                return;
            }

            $disk = SecureStorage::disk();
            if (Storage::disk($disk)->exists($oldPath)) {
                Storage::disk($disk)->delete($oldPath);
            }
        });

        self::deleting(static function (BrochureFile $brochureFile): void {
            $path = trim((string) $brochureFile->file_path);

            if ($path === '') {
                return;
            }

            $disk = SecureStorage::disk();
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        });
    }

    /**
     * @return BelongsTo<Brochure, $this>
     */
    public function brochure(): BelongsTo
    {
        return $this->belongsTo(Brochure::class, 'brochure_id');
    }

    /**
     * @param  Builder<static> $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function downloadUrl(bool $download = true): string
    {
        return SecureStorage::signedUrl((string) $this->file_path, $download);
    }

    protected static function newFactory(): BrochureFileFactory
    {
        return BrochureFileFactory::new();
    }
}
