<?php declare(strict_types=1);

namespace App\Models;

use App\Enums\ExportStatus;
use Database\Factories\ExportFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Export
 *
 * Eloquent model representing the Export entity for managing data exports
 * with comprehensive status tracking, file management, and user associations.
 *
 * @property int                             $id
 * @property string                          $uuid
 * @property string                          $name
 * @property string                          $format
 * @property ExportStatus                    $status
 * @property string                          $exportable_type
 * @property array<int, string>              $columns
 * @property array<string, mixed>|null       $exportable_options
 * @property int                             $total_rows
 * @property int                             $processed_rows
 * @property string|null                     $artifact_disk
 * @property string|null                     $artifact_path
 * @property string|null                     $artifact_filename
 * @property \Illuminate\Support\Carbon      $requested_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $failed_at
 * @property string|null                     $failure_reason
 * @property int|null                        $requested_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User|null $requestedBy
 * @property-read string $file_extension
 * @property-read int $progress_percentage
 * @property-read bool $is_completed
 * @property-read bool $is_failed
 * @property-read bool $is_processing
 * @property-read bool $is_downloadable
 *
 * @method static Builder<self> queued()
 * @method static Builder<self> processing()
 * @method static Builder<self> completed()
 * @method static Builder<self> failed()
 * @method static Builder<self> byUser(int $userId)
 * @method static Builder<self> recentFirst()
 * @method static Builder<self> ofType(string $exportableType)
 * @method static ExportFactory factory($count = null, $state = [])
 * @method static Builder<self> newModelQuery()
 * @method static Builder<self> newQuery()
 * @method static Builder<self> query()
 *
 * @mixin \Eloquent
 */
final class Export extends Model
{
    /** @use HasFactory<ExportFactory> */
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'columns' => 'array',
            'exportable_options' => 'array',
            'total_rows' => 'integer',
            'processed_rows' => 'integer',
            'requested_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
            'status' => ExportStatus::class,
        ];
    }

    /**
     * Bootstrap the model and its traits.
     */
    protected static function booted(): void
    {
        self::creating(function (self $export): void {
            if (!$export->getAttribute('uuid')) {
                $export->setAttribute('uuid', (string) Str::uuid());
            }

            if (!$export->getAttribute('requested_at')) {
                $export->setAttribute('requested_at', now());
            }

            // Set default values for counters
            if ($export->getAttribute('total_rows') === null) {
                $export->setAttribute('total_rows', 0);
            }

            if ($export->getAttribute('processed_rows') === null) {
                $export->setAttribute('processed_rows', 0);
            }
        });
    }

    // Relationships

    /**
     * Get the user who requested this export.
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    // Route Model Binding

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // Query Scopes

    /**
     * Scope a query to only include queued exports.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeQueued(Builder $query): Builder
    {
        return $query->where('status', ExportStatus::Queued);
    }

    /**
     * Scope a query to only include processing exports.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeProcessing(Builder $query): Builder
    {
        return $query->where('status', ExportStatus::Processing);
    }

    /**
     * Scope a query to only include completed exports.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', ExportStatus::Completed);
    }

    /**
     * Scope a query to only include failed exports.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', ExportStatus::Failed);
    }

    /**
     * Scope a query to filter exports by user.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('requested_by', $userId);
    }

    /**
     * Scope a query to order exports by most recent first.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeRecentFirst(Builder $query): Builder
    {
        return $query->orderBy('requested_at', 'desc');
    }

    /**
     * Scope a query to filter exports by exportable type.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeOfType(Builder $query, string $exportableType): Builder
    {
        return $query->where('exportable_type', $exportableType);
    }

    // Accessors

    /**
     * Get the file extension for this export.
     */
    protected function fileExtension(): Attribute
    {
        return Attribute::make(
            get: fn(): string => $this->format
        );
    }

    /**
     * Get the progress percentage of this export.
     */
    protected function progressPercentage(): Attribute
    {
        return Attribute::make(
            get: function (): int {
                if ($this->total_rows === 0) {
                    return 0;
                }

                return (int) round(($this->processed_rows / $this->total_rows) * 100);
            }
        );
    }

    /**
     * Check if the export is completed.
     */
    protected function isCompleted(): Attribute
    {
        return Attribute::make(
            get: fn(): bool => $this->status === ExportStatus::Completed
        );
    }

    /**
     * Check if the export has failed.
     */
    protected function isFailed(): Attribute
    {
        return Attribute::make(
            get: fn(): bool => $this->status === ExportStatus::Failed
        );
    }

    /**
     * Check if the export is currently processing.
     */
    protected function isProcessing(): Attribute
    {
        return Attribute::make(
            get: fn(): bool => $this->status === ExportStatus::Processing
        );
    }

    /**
     * Check if the export is downloadable.
     */
    protected function isDownloadable(): Attribute
    {
        return Attribute::make(
            get: fn(): bool => $this->status === ExportStatus::Completed &&
                $this->artifact_path !== null &&
                $this->artifact_filename !== null
        );
    }

    // Helper Methods

    /**
     * Mark the export as processing.
     */
    public function markAsProcessing(): bool
    {
        return $this->update([
            'status' => ExportStatus::Processing,
        ]);
    }

    /**
     * Mark the export as completed.
     */
    public function markAsCompleted(string $artifactPath, string $artifactFilename, ?string $artifactDisk = null): bool
    {
        return $this->update([
            'status' => ExportStatus::Completed,
            'completed_at' => now(),
            'artifact_path' => $artifactPath,
            'artifact_filename' => $artifactFilename,
            'artifact_disk' => $artifactDisk ?? $this->artifact_disk,
        ]);
    }

    /**
     * Mark the export as failed.
     */
    public function markAsFailed(string $reason): bool
    {
        return $this->update([
            'status' => ExportStatus::Failed,
            'failed_at' => now(),
            'failure_reason' => $reason,
        ]);
    }

    /**
     * Update the progress of the export.
     */
    public function updateProgress(int $processedRows, ?int $totalRows = null): bool
    {
        $data = ['processed_rows' => $processedRows];

        if ($totalRows !== null) {
            $data['total_rows'] = $totalRows;
        }

        return $this->update($data);
    }
}
