<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ExportFormat;
use App\Enums\ExportStatus;
use App\Enums\ExportType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

final class Export extends Model
{
    use HasFactory;

    protected $fillable = [
        'requested_by',
        'type',
        'format',
        'status',
        'filters',
        'columns',
        'file_name',
        'file_path',
        'mime_type',
        'locale',
        'timezone',
        'total_rows',
        'completed_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'columns' => 'array',
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
            'type' => ExportType::class,
            'format' => ExportFormat::class,
            'status' => ExportStatus::class,
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function signedUrl(?Carbon $expiresAt = null): string
    {
        $expiry = $expiresAt ?? $this->expires_at ?? now()->addMinutes((int) config('exports.ttl_minutes', 1440));

        return URL::temporarySignedRoute('api.exports.download', $expiry, ['export' => $this->getKey()]);
    }

    public function columnLabels(): array
    {
        return Arr::pluck($this->columns ?? [], 'label', 'key');
    }
}
