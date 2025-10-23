<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Export\ExportFormat;
use App\Services\Export\ExportStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property string $resource
 * @property string $model
 * @property ExportFormat $format
 * @property array<int, string> $columns
 * @property array<int, string>|null $selection
 * @property array<string, mixed>|null $filters
 * @property ExportStatus $status
 * @property User|null $user
 * @property string|null $path
 * @property int|null $total_rows
 * @property int $processed_rows
 * @property int $chunk_size
 * @property Carbon|null $available_until
 */
final class Export extends Model
{
    /** @use HasFactory<\Database\Factories\ExportFactory> */
    use HasFactory;

    use HasUlids;

    protected $fillable = [
        'name',
        'resource',
        'model',
        'format',
        'columns',
        'selection',
        'filters',
        'status',
        'path',
        'total_rows',
        'processed_rows',
        'chunk_size',
        'available_until',
    ];

    protected function casts(): array
    {
        return [
            'columns' => 'array',
            'selection' => 'array',
            'filters' => 'array',
            'format' => ExportFormat::class,
            'status' => ExportStatus::class,
            'available_until' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, Export>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markProcessing(int $totalRows): void
    {
        $this->forceFill([
            'status' => ExportStatus::Processing,
            'total_rows' => $totalRows,
            'processed_rows' => 0,
        ])->save();

        $this->status = ExportStatus::Processing;
        $this->total_rows = $totalRows;
        $this->processed_rows = 0;
    }

    public function incrementProcessedRows(int $amount): void
    {
        $this->forceFill([
            'processed_rows' => $this->processed_rows + $amount,
        ])->save();

        $this->processed_rows += $amount;
    }

    public function markCompleted(string $path): void
    {
        $retentionConfig = config('export.retention_hours');
        $hours = 48;
        if (is_int($retentionConfig)) {
            $hours = $retentionConfig;
        } elseif (is_string($retentionConfig) && is_numeric($retentionConfig)) {
            $hours = (int) $retentionConfig;
        }

        $availableUntil = now()->addHours($hours);

        $this->forceFill([
            'status' => ExportStatus::Completed,
            'path' => $path,
            'available_until' => $availableUntil,
        ])->save();

        $this->status = ExportStatus::Completed;
        $this->path = $path;
        $this->available_until = $availableUntil;
    }

    public function markFailed(): void
    {
        $this->forceFill([
            'status' => ExportStatus::Failed,
        ])->save();

        $this->status = ExportStatus::Failed;
    }

    public function isDownloadable(): bool
    {
        return $this->status === ExportStatus::Completed
            && (! $this->available_until || now()->lessThanOrEqualTo($this->available_until));
    }
}
