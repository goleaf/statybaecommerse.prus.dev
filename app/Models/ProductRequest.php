<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\StatusScope;
use App\Models\Scopes\UserOwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * ProductRequest
 *
 * Eloquent model representing the ProductRequest entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 * @property mixed $table
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProductRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductRequest query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([UserOwnedScope::class, StatusScope::class])]
final class ProductRequest extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = ['product_id', 'user_id', 'name', 'email', 'phone', 'message', 'requested_quantity', 'status', 'admin_notes', 'responded_at', 'responded_by'];

    protected $casts = ['requested_quantity' => 'integer', 'responded_at' => 'datetime'];

    protected $table = 'product_requests';

    /**
     * Handle getActivitylogOptions functionality with proper error handling.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['status', 'admin_notes', 'responded_at', 'responded_by'])->logOnlyDirty()->dontSubmitEmptyLogs()->setDescriptionForEvent(fn (string $eventName) => "Product Request {$eventName}")->useLogName('product_request');
    }

    /**
     * Handle product functionality with proper error handling.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Handle user functionality with proper error handling.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Handle respondedBy functionality with proper error handling.
     */
    public function respondedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    /**
     * Handle scopePending functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Handle scopeInProgress functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Handle scopeCompleted functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Handle scopeCancelled functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Handle scopeByProduct functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Handle scopeByUser functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Handle isPending functionality with proper error handling.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Handle isInProgress functionality with proper error handling.
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Handle isCompleted functionality with proper error handling.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Handle isCancelled functionality with proper error handling.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Handle markAsInProgress functionality with proper error handling.
     */
    public function markAsInProgress(?int $respondedBy = null): void
    {
        $this->update(['status' => 'in_progress', 'responded_at' => now(), 'responded_by' => $respondedBy]);
    }

    /**
     * Handle markAsCompleted functionality with proper error handling.
     */
    public function markAsCompleted(?int $respondedBy = null, ?string $adminNotes = null): void
    {
        $this->update(['status' => 'completed', 'responded_at' => now(), 'responded_by' => $respondedBy, 'admin_notes' => $adminNotes]);
    }

    /**
     * Handle markAsCancelled functionality with proper error handling.
     */
    public function markAsCancelled(?int $respondedBy = null, ?string $adminNotes = null): void
    {
        $this->update(['status' => 'cancelled', 'responded_at' => now(), 'responded_by' => $respondedBy, 'admin_notes' => $adminNotes]);
    }

    /**
     * Handle getStatusLabelAttribute functionality with proper error handling.
     */
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

    /**
     * Handle getStatusColorAttribute functionality with proper error handling.
     */
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
