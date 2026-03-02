<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Storage\SecureStorage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OrderInvoice extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_READY = 'ready';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUNDED = 'refunded';

    public const MODE_AUTO = 'auto';
    public const MODE_MANUAL = 'manual';
    public const MODE_BACKFILL = 'backfill';

    protected $fillable = [
        'order_id',
        'file_id',
        'external_invoice_id',
        'invoice_series',
        'invoice_number',
        'full_number',
        'invoice_type',
        'status',
        'is_current',
        'generation_mode',
        'provider_payload',
        'error_message',
        'generated_at',
        'failed_at',
    ];

    protected function casts(): array
    {
        return [
            'provider_payload' => 'array',
            'is_current'       => 'boolean',
            'generated_at'     => 'datetime',
            'failed_at'        => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function downloadUrl(bool $download = true): ?string
    {
        $path = $this->file?->path;

        if (! is_string($path) || $path === '') {
            return null;
        }

        return SecureStorage::temporarySignedUrl($path, now()->addMinutes(30), $download);
    }
}
