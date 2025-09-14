<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final /**
 * ProductRequest
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class ProductRequest extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'product_id',
        'user_id',
        'name',
        'email',
        'phone',
        'message',
        'requested_quantity',
        'status',
        'admin_notes',
        'responded_at',
        'responded_by',
    ];

    protected $casts = [
        'requested_quantity' => 'integer',
        'responded_at' => 'datetime',
    ];

    protected $table = 'product_requests';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'admin_notes', 'responded_at', 'responded_by'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Product Request {$eventName}")
            ->useLogName('product_request');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function respondedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function markAsInProgress(?int $respondedBy = null): void
    {
        $this->update([
            'status' => 'in_progress',
            'responded_at' => now(),
            'responded_by' => $respondedBy,
        ]);
    }

    public function markAsCompleted(?int $respondedBy = null, ?string $adminNotes = null): void
    {
        $this->update([
            'status' => 'completed',
            'responded_at' => now(),
            'responded_by' => $respondedBy,
            'admin_notes' => $adminNotes,
        ]);
    }

    public function markAsCancelled(?int $respondedBy = null, ?string $adminNotes = null): void
    {
        $this->update([
            'status' => 'cancelled',
            'responded_at' => now(),
            'responded_by' => $respondedBy,
            'admin_notes' => $adminNotes,
        ]);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => __('translations.status_pending'),
            'in_progress' => __('translations.status_in_progress'),
            'completed' => __('translations.status_completed'),
            'cancelled' => __('translations.status_cancelled'),
            default => __('translations.status_unknown'),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'in_progress' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }
}
