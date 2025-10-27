<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\StatusScope;
use App\Models\Scopes\UserOwnedScope;
use Database\Factories\ProductRequestFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
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
    /** @use HasFactory<ProductRequestFactory> */
    use HasFactory;

    use LogsActivity;
    use SoftDeletes;

    /**
     * Centralised status constants to avoid string duplication throughout the model logic.
     */
    public const STATUS_PENDING = 'pending';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    /**
     * The attributes that are mass assignable in order to protect against accidental overrides.
     */
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

    /**
     * Attribute casting rules keep frequently accessed fields strongly typed.
     */
    protected $casts = [
        'requested_quantity' => 'integer',
        'responded_at'       => 'datetime',
        'deleted_at'         => 'datetime',
    ];

    /**
     * Explicitly declare the backing table to avoid accidental renaming issues.
     */
    protected $table = 'product_requests';

    /**
     * Handle getActivitylogOptions functionality with proper error handling.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['status', 'admin_notes', 'responded_at', 'responded_by'])->logOnlyDirty()->dontSubmitEmptyLogs()->setDescriptionForEvent(fn (string $eventName): string => "Product Request {$eventName}")->useLogName('product_request');
    }

    /**
     * Handle product functionality with proper error handling.
     *
     * @return BelongsTo<Product, ProductRequest>
     */
    public function product(): BelongsTo
    {
        /** @var BelongsTo<Product, ProductRequest> $relation */
        $relation = $this->belongsTo(Product::class, 'product_id');

        return $relation;
    }

    /**
     * Handle user functionality with proper error handling.
     *
     * @return BelongsTo<User, ProductRequest>
     */
    public function user(): BelongsTo
    {
        /** @var BelongsTo<User, ProductRequest> $relation */
        $relation = $this->belongsTo(User::class, 'user_id');

        return $relation;
    }

    /**
     * Handle respondedBy functionality with proper error handling.
     *
     * @return BelongsTo<User, ProductRequest>
     */
    public function respondedBy(): BelongsTo
    {
        /** @var BelongsTo<User, ProductRequest> $relation */
        $relation = $this->belongsTo(User::class, 'responded_by');

        return $relation;
    }

    /**
     * Handle scopePending functionality with proper error handling.
     *
     * @param  Builder<ProductRequest> $query
     * @return Builder<ProductRequest>
     */
    public function scopePending(Builder $query): Builder
    {
        // Limit results to records that are awaiting a response.
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Handle scopeInProgress functionality with proper error handling.
     *
     * @param  Builder<ProductRequest> $query
     * @return Builder<ProductRequest>
     */
    public function scopeInProgress(Builder $query): Builder
    {
        // Limit results to records currently being handled by the support team.
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    /**
     * Handle scopeCompleted functionality with proper error handling.
     *
     * @param  Builder<ProductRequest> $query
     * @return Builder<ProductRequest>
     */
    public function scopeCompleted(Builder $query): Builder
    {
        // Limit results to requests that have already been fulfilled.
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Handle scopeCancelled functionality with proper error handling.
     *
     * @param  Builder<ProductRequest> $query
     * @return Builder<ProductRequest>
     */
    public function scopeCancelled(Builder $query): Builder
    {
        // Limit results to requests that were intentionally stopped.
        return $query
            ->withoutGlobalScope(StatusScope::class)
            ->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Handle scopeByProduct functionality with proper error handling.
     *
     * @param  Builder<ProductRequest> $query
     * @return Builder<ProductRequest>
     */
    public function scopeByProduct(Builder $query, int $productId): Builder
    {
        // Filter by the related product to inspect a specific catalogue item.
        return $query->where('product_id', $productId);
    }

    /**
     * Handle scopeByUser functionality with proper error handling.
     *
     * @param  Builder<ProductRequest> $query
     * @return Builder<ProductRequest>
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        // Filter by the originating customer to analyse their outstanding requests.
        return $query->where('user_id', $userId);
    }

    /**
     * Apply a consistent alphabetical ordering when displaying lists of requests by name.
     *
     * @param  Builder<ProductRequest> $query
     * @return Builder<ProductRequest>
     */
    public function scopeOrderedByName(Builder $query, string $direction = 'asc'): Builder
    {
        // Guard against invalid direction values by normalising the input before applying it.
        $safeDirection = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($query->qualifyColumn('name'), $safeDirection);
    }

    /**
     * Handle isPending functionality with proper error handling.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Handle isInProgress functionality with proper error handling.
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Handle isCompleted functionality with proper error handling.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Handle isCancelled functionality with proper error handling.
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Handle markAsInProgress functionality with proper error handling.
     */
    public function markAsInProgress(?int $respondedBy = null): void
    {
        $this->update([
            'status'       => self::STATUS_IN_PROGRESS,
            'responded_at' => now(),
            'responded_by' => $respondedBy,
        ]);
    }

    /**
     * Handle markAsCompleted functionality with proper error handling.
     */
    public function markAsCompleted(?int $respondedBy = null, ?string $adminNotes = null): void
    {
        $this->update([
            'status'       => self::STATUS_COMPLETED,
            'responded_at' => now(),
            'responded_by' => $respondedBy,
            'admin_notes'  => $adminNotes,
        ]);
    }

    /**
     * Handle markAsCancelled functionality with proper error handling.
     */
    public function markAsCancelled(?int $respondedBy = null, ?string $adminNotes = null): void
    {
        $this->update([
            'status'       => self::STATUS_CANCELLED,
            'responded_at' => now(),
            'responded_by' => $respondedBy,
            'admin_notes'  => $adminNotes,
        ]);
    }

    /**
     * Handle getStatusLabelAttribute functionality with proper error handling.
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            self::STATUS_PENDING     => __('translations.status_pending'),
            self::STATUS_IN_PROGRESS => __('translations.status_in_progress'),
            self::STATUS_COMPLETED   => __('translations.status_completed'),
            self::STATUS_CANCELLED   => __('translations.status_cancelled'),
        ];

        if (! array_key_exists($this->status, $labels)) {
            return __('translations.status_unknown');
        }

        return $labels[$this->status];
    }

    /**
     * Handle getStatusColorAttribute functionality with proper error handling.
     */
    public function getStatusColorAttribute(): string
    {
        $colors = [
            self::STATUS_PENDING     => 'warning',
            self::STATUS_IN_PROGRESS => 'info',
            self::STATUS_COMPLETED   => 'success',
            self::STATUS_CANCELLED   => 'danger',
        ];

        if (! array_key_exists($this->status, $colors)) {
            return 'secondary';
        }

        return $colors[$this->status];
    }
}
