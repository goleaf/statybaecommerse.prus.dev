<?php declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Coupon
 *
 * Eloquent model representing the Coupon entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 *
 * @method static \Illuminate\Database\Eloquent\Builder<Coupon> newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<Coupon> newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<Coupon> query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class Coupon extends Model
{
    /** @use HasFactory<\Database\Factories\CouponFactory> */
    use HasFactory;
    use OrdersByName;
    use SoftDeletes;

    /**
     * Ensure alphabetical ordering defaults to the human-readable name column
     * so the shared OrdersByName trait applies consistent query clauses.
     */
    protected string $nameColumn = 'name';

    protected $fillable = ['code', 'name', 'description', 'type', 'value', 'minimum_amount', 'maximum_discount', 'usage_limit', 'usage_limit_per_user', 'used_count', 'is_active', 'is_public', 'is_auto_apply', 'is_stackable', 'is_first_time_only', 'customer_group_id', 'starts_at', 'expires_at', 'applicable_products', 'applicable_categories', 'meta'];

    protected $casts = ['value' => 'decimal:2', 'minimum_amount' => 'decimal:2', 'maximum_discount' => 'decimal:2', 'usage_limit' => 'integer', 'usage_limit_per_user' => 'integer', 'used_count' => 'integer', 'is_active' => 'boolean', 'is_public' => 'boolean', 'is_auto_apply' => 'boolean', 'is_stackable' => 'boolean', 'is_first_time_only' => 'boolean', 'customer_group_id' => 'integer', 'starts_at' => 'datetime', 'expires_at' => 'datetime', 'applicable_products' => 'array', 'applicable_categories' => 'array', 'meta' => 'array'];

    /**
     * Automatically hydrate missing codes using the unique generator so freshly
     * created coupons always ship with a collision-free identifier.
     */
    protected static function booted(): void
    {
        self::creating(static function (self $coupon): void {
            // Only attempt to backfill the code when one is not already provided.
            if (!$coupon->code) {
                $coupon->code = self::generateUniqueCode();
            }
        });
    }

    // Relationships

    /**
     * Handle products functionality with proper error handling.
     *
     * @return BelongsToMany<Product, Coupon>
     */
    public function products(): BelongsToMany
    {
        /** @var BelongsToMany<Product, Coupon> $relation */
        $relation = $this->belongsToMany(Product::class, 'coupon_products');

        return $relation;
    }

    /**
     * Handle categories functionality with proper error handling.
     *
     * @return BelongsToMany<Category, Coupon>
     */
    public function categories(): BelongsToMany
    {
        /** @var BelongsToMany<Category, Coupon> $relation */
        $relation = $this->belongsToMany(Category::class, 'coupon_categories');

        return $relation;
    }

    /**
     * @return BelongsTo<CustomerGroup, Coupon>
     */
    public function customerGroup(): BelongsTo
    {
        // Provide access to the owning customer group so filters and forms can reference it safely.
        /** @var BelongsTo<CustomerGroup, Coupon> $relation */
        $relation = $this->belongsTo(CustomerGroup::class);

        return $relation;
    }

    /**
     * Handle orders functionality with proper error handling.
     *
     * @return HasMany<Order, Coupon>
     */
    public function orders(): HasMany
    {
        /** @var HasMany<Order, Coupon> $relation */
        $relation = $this->hasMany(Order::class);

        return $relation;
    }

    /**
     * Handle usages functionality with proper error handling.
     *
     * @return HasMany<CouponUsage, Coupon>
     */
    public function usages(): HasMany
    {
        /** @var HasMany<CouponUsage, Coupon> $relation */
        $relation = $this->hasMany(CouponUsage::class);

        return $relation;
    }

    // Scopes

    /**
     * Handle scopeActive functionality with proper error handling.
     *
     * @param  Builder<Coupon> $query
     * @return Builder<Coupon>
     */
    public function scopeActive(Builder $query): Builder
    {
        // Ensure we only surface coupons explicitly marked as active in the database.
        return $query->where('is_active', true);
    }

    /**
     * Handle scopeValid functionality with proper error handling.
     *
     * @param  Builder<Coupon> $query
     * @return Builder<Coupon>
     */
    public function scopeValid(Builder $query): Builder
    {
        // Capture the current moment once so multiple comparisons use a consistent timestamp.
        $now = now();

        return $query
            ->where('is_active', true)
            ->where(static function (Builder $builder) use ($now): void {
                // Allow coupons without a start date or ones that have already started.
                $builder->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(static function (Builder $builder) use ($now): void {
                // Ensure the coupon is either timeless or still before its expiration boundary.
                $builder->whereNull('expires_at')->orWhere('expires_at', '>', $now);
            })
            ->where(static function (Builder $builder): void {
                // Prevent coupons that have exhausted their usage limit from being considered valid.
                $builder->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit');
            });
    }

    /**
     * Handle scopeExpired functionality with proper error handling.
     *
     * @param  Builder<Coupon> $query
     * @return Builder<Coupon>
     */
    public function scopeExpired(Builder $query): Builder
    {
        // Quickly locate coupons whose expiration timestamp has already passed.
        return $query->where('expires_at', '<', now());
    }

    /**
     * Handle scopeByType functionality with proper error handling.
     *
     * @param  Builder<Coupon> $query
     * @return Builder<Coupon>
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        // Provide an expressive shortcut for filtering coupons by discount type.
        return $query->where('type', $type);
    }

    /**
     * Handle scopeByCode functionality with proper error handling.
     *
     * @param  Builder<Coupon> $query
     * @return Builder<Coupon>
     */
    public function scopeByCode(Builder $query, string $code): Builder
    {
        // Locate a coupon by its public code value without repeating column names elsewhere.
        return $query->where('code', $code);
    }

    /**
     * Handle isValid functionality with proper error handling.
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }
        if ($this->starts_at && $this->starts_at > now()) {
            return false;
        }
        if ($this->expires_at && $this->expires_at < now()) {
            return false;
        }
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Handle isExpired functionality with proper error handling.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at < now();
    }

    /**
     * Handle isNotStarted functionality with proper error handling.
     */
    public function isNotStarted(): bool
    {
        return $this->starts_at && $this->starts_at > now();
    }

    /**
     * Handle canBeUsed functionality with proper error handling.
     */
    public function canBeUsed(float $orderTotal): bool
    {
        if (!$this->isValid()) {
            return false;
        }
        if ($this->minimum_amount !== null && $orderTotal < (float) $this->minimum_amount) {
            return false;
        }

        return true;
    }

    /**
     * Handle calculateDiscount functionality with proper error handling.
     */
    public function calculateDiscount(float $orderTotal): float
    {
        if (!$this->canBeUsed($orderTotal)) {
            return 0;
        }
        // Determine the raw discount value based on the configured coupon type.
        $discount = $this->type === 'percentage'
            ? $orderTotal * ((float) $this->value) / 100
            : (float) $this->value;

        // Respect any configured maximum discount to prevent over-application.
        if ($this->maximum_discount !== null) {
            $discount = min($discount, (float) $this->maximum_discount);
        }

        // Guard against scenarios where a fixed discount exceeds the current order total.
        return (float) min($discount, $orderTotal);
    }

    /**
     * Compute the remaining usage count without persisting a dedicated column.
     */
    public function getRemainingUsesAttribute(): ?int
    {
        if ($this->usage_limit === null) {
            // Mirror the behaviour of DiscountCode by signalling unlimited usage with null.
            return null;
        }

        // Clamp the remaining uses at zero so negative values are never exposed.
        return max(0, (int) ($this->usage_limit - $this->used_count));
    }

    /**
     * Generate a unique coupon code while respecting database-level uniqueness.
     */
    public static function generateUniqueCode(int $length = 10): string
    {
        // Retry code generation a limited number of times to avoid an infinite loop
        // should the random generator repeatedly collide with existing values.
        $attempts = 0;
        $maxAttempts = 25;

        do {
            $attempts++;
            $code = Str::upper(Str::random($length));

            // Leverage a case-insensitive lookup so codes remain unique regardless
            // of how they are entered in the admin panel or during checkout.
            $exists = self::query()->whereRaw('UPPER(code) = ?', [$code])->exists();
        } while ($exists && $attempts < $maxAttempts);

        // If the generator somehow exhausted the retry window, append a timestamp
        // fragment to guarantee uniqueness before handing the code back to callers.
        if ($exists) {
            $code = $code . strtoupper(now()->format('His'));
        }

        return $code;
    }
}
