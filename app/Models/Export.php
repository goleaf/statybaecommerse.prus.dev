<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ExportStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property array<int, string> $columns
 * @property array<string, mixed>|null $exportable_options
 * @property ExportStatus $status
 */
final class Export extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'format',
        'status',
        'exportable_type',
        'columns',
        'exportable_options',
        'total_rows',
        'processed_rows',
        'artifact_disk',
        'artifact_path',
        'artifact_filename',
        'requested_at',
        'completed_at',
        'failed_at',
        'failure_reason',
        'requested_by',
    ];

    protected $casts = [
        'columns' => 'array',
        'exportable_options' => 'array',
        'requested_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
        'status' => ExportStatus::class,
    ];

    protected static function booted(): void
    {
        self::creating(function (self $export): void {
            if (! $export->getAttribute('uuid')) {
                $export->setAttribute('uuid', (string) Str::uuid());
            }

            if (! $export->getAttribute('requested_at')) {
                $export->setAttribute('requested_at', now());
            }
        });
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeQueued(Builder $query): Builder
    {
        return $query->where('status', ExportStatus::Queued);
    }

    protected function fileExtension(): Attribute
    {
        return Attribute::make(get: fn (): string => $this->format);
    }
}
