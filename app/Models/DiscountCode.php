<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\DateRangeScope;
use App\Models\Scopes\StatusScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * DiscountCode
 *
 * Eloquent model representing the DiscountCode entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCode query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, DateRangeScope::class, StatusScope::class])]
final class DiscountCode extends Model
{
    use HasFactory;
    use OrdersByName;
    use SoftDeletes;

    /**
     * Expose the target column for alphabetical ordering so shared helpers can
     * consistently sort discount codes by their public string value.
     */
    protected string $nameColumn = 'code';

    protected $table = 'discount_codes';

    protected $fillable = [
        'discount_id',
        'code',
        'name',
        'description',
        'description_lt',
        'description_en',
        'type',
        'value',
        'minimum_amount',
        'maximum_discount',
        'starts_at',
        'expires_at',
        'valid_from',
        'valid_until',
        'usage_limit',
        'usage_limit_per_user',
        'usage_count',
        'is_active',
        'is_public',
        'is_auto_apply',
        'is_stackable',
        'is_first_time_only',
        'customer_group_id',
        'status',
        'metadata',
        'meta',
        'created_by',
        'updated_by',
        'created_by_name',
        'updated_by_name',
    ];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return [
            'starts_at'            => 'datetime',
            'expires_at'           => 'datetime',
            'valid_from'           => 'datetime',
            'valid_until'          => 'datetime',
            'usage_limit'          => 'integer',
            'usage_limit_per_user' => 'integer',
            'usage_count'          => 'integer',
            'is_active'            => 'boolean',
            'is_public'            => 'boolean',
            'is_auto_apply'        => 'boolean',
            'is_stackable'         => 'boolean',
            'is_first_time_only'   => 'boolean',
            'value'                => 'decimal:2',
            'minimum_amount'       => 'decimal:2',
            'maximum_discount'     => 'decimal:2',
            'metadata'             => 'array',
            'meta'                 => 'array',
        ];
    }

    /**
     * Handle discount functionality with proper error handling.
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    /**
     * Handle customerGroup functionality with proper error handling.
     */
    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    /**
     * Handle redemptions functionality with proper error handling.
     */
    public function redemptions(): HasMany
    {
        return $this->hasMany(DiscountRedemption::class, 'code_id');
    }

    /**
     * Handle creator functionality with proper error handling.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Handle updater functionality with proper error handling.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Handle documents functionality with proper error handling.
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Handle orders functionality with proper error handling.
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'discount_redemptions', 'code_id', 'order_id')
            // Ensure admin-side lookups ignore storefront-specific order scopes.
            ->withoutGlobalScopes();
    }

    /**
     * Handle users functionality with proper error handling.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'discount_redemptions', 'code_id', 'user_id');
    }

    /**
     * Quickly limit the query to codes that are currently valid based on the
     * expiration and activation metadata persisted alongside the record.
     */
    public function scopeValid(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('is_active', true)
            ->where(static function (Builder $builder) use ($now): void {
                $builder->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(static function (Builder $builder) use ($now): void {
                $builder->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
            });
    }

    /**
     * Provide a dedicated helper to isolate non-expired codes for UI dropdowns
     * while keeping business logic readable at the call site.
     */
    public function scopeNotExpired(Builder $query): Builder
    {
        $now = now();

        return $query->where(static function (Builder $builder) use ($now): void {
            $builder->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
        });
    }

    /**
     * Resolve a code by its public identifier without repeating column names
     * throughout services and controllers, reducing potential typos.
     */
    public function scopeWithCode(Builder $query, string $code): Builder
    {
        return $query->where('code', $code);
    }

    /**
     * Handle scopeActive functionality with proper error handling.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->where(function ($q): void {
            $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
        })->where(function ($q): void {
            $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
        });
    }

    /**
     * Handle scopeExpired functionality with proper error handling.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Handle scopeScheduled functionality with proper error handling.
     */
    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('starts_at', '>', now());
    }

    /**
     * Handle scopeUsageLimitReached functionality with proper error handling.
     */
    public function scopeUsageLimitReached(Builder $query): Builder
    {
        return $query->whereColumn('usage_count', '>=', 'usage_limit');
    }

    /**
     * Handle scopeByStatus functionality with proper error handling.
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Handle scopeCreatedBy functionality with proper error handling.
     */
    public function scopeCreatedBy(Builder $query, int $userId): Builder
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Handle hasReachedLimit functionality with proper error handling.
     */
    public function hasReachedLimit(): bool
    {
        if ($this->usage_limit !== null) {
            return $this->usage_count >= $this->usage_limit;
        }

        return false;
    }

    /**
     * Handle isValid functionality with proper error handling.
     */
    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        if ($this->hasReachedLimit()) {
            return false;
        }
        $now = now();
        if ($this->starts_at && $this->starts_at->gt($now)) {
            return false;
        }
        if ($this->expires_at && $this->expires_at->lt($now)) {
            return false;
        }

        return true;
    }

    /**
     * Handle incrementUsage functionality with proper error handling.
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Handle generateUniqueCode functionality with proper error handling.
     */
    public static function generateUniqueCode(int $length = 8): string
    {
        do {
            $code = strtoupper(str()->random($length));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Handle getDescriptionAttribute functionality with proper error handling.
     */
    public function getDescriptionAttribute(): string
    {
        $locale = app()->getLocale();

        return $this->{"description_{$locale}"} ?? $this->description_lt ?? '';
    }

    /**
     * Handle getStatusColorAttribute functionality with proper error handling.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active'    => 'success',
            'inactive'  => 'gray',
            'expired'   => 'danger',
            'scheduled' => 'warning',
            default     => 'gray',
        };
    }

    /**
     * Handle getUsagePercentageAttribute functionality with proper error handling.
     */
    public function getUsagePercentageAttribute(): float
    {
        if ($this->usage_limit === null || $this->usage_limit === 0) {
            return 0;
        }

        return $this->usage_count / $this->usage_limit * 100;
    }

    /**
     * Handle isExpiringSoon functionality with proper error handling.
     */
    public function isExpiringSoon(): bool
    {
        if (! $this->expires_at) {
            return false;
        }

        return $this->expires_at->diffInDays(now()) <= 7;
    }

    /**
     * Handle getRemainingUsesAttribute functionality with proper error handling.
     */
    public function getRemainingUsesAttribute(): ?int
    {
        if ($this->usage_limit === null) {
            return null;
        }

        return max(0, $this->usage_limit - $this->usage_count);
    }

    /**
     * Handle getTypeAttribute functionality with proper error handling.
     */
    public function getTypeAttribute(): string
    {
        return $this->attributes['type'] ?? 'percentage';
    }

    /**
     * Handle getValueAttribute functionality with proper error handling.
     */
    public function getValueAttribute(): float
    {
        return (float) ($this->attributes['value'] ?? 0);
    }
}
