<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\StatusScope;
use App\Observers\OrderObserver;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Carbon;
use Schema;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

/**
 * Order
 *
 * Eloquent model representing the Order entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property array $translatable
 * @property array|null $transactions
 * @property array|null $billing_address
 * @property array|null $shipping_address
 * @property mixed $fillable
 * @property mixed $appends
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|Order createdBetween(CarbonInterface|DateTimeInterface|string $start, CarbonInterface|DateTimeInterface|string $end)
 * @method static \Illuminate\Database\Eloquent\Builder|Order createdSince(CarbonInterface|DateTimeInterface|string $start)
 * @method static \Illuminate\Database\Eloquent\Builder|Order createdUntil(CarbonInterface|DateTimeInterface|string $end)
 * @method static \Illuminate\Database\Eloquent\Builder|Order createdOnDate(CarbonInterface|DateTimeInterface|string $date)
 *
 * @mixin \Eloquent
 */
#[ObservedBy([OrderObserver::class])]
#[ScopedBy([StatusScope::class])]
final class Order extends Model
{
    use HasFactory, HasTranslations, LogsActivity, SoftDeletes;

    public array $translatable = ['notes', 'billing_address', 'shipping_address'];

    protected $fillable = ['number', 'user_id', 'status', 'subtotal', 'tax_amount', 'shipping_amount', 'discount_amount', 'total', 'currency', 'billing_address', 'shipping_address', 'notes', 'shipped_at', 'delivered_at', 'channel_id', 'shipping_option_id', 'partner_id', 'coupon_id', 'payment_status', 'payment_method', 'payment_reference'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'billing_address' => 'array',
            'shipping_address' => 'array',
            'transactions' => 'array',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['total_items_count', 'formatted_total'];

    /**
     * Cache whether the created_at index is available per connection to avoid repeated schema lookups.
     *
     * @var array<string, bool>
     */
    private static array $createdAtIndexAvailable = [];

    /**
     * Handle getActivitylogOptions functionality with proper error handling.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['number', 'status', 'total', 'notes', 'tracking_number', 'fulfillment_status'])->logOnlyDirty()->dontSubmitEmptyLogs()->setDescriptionForEvent(fn (string $eventName) => "Order {$eventName}")->useLogName('order');
    }

    /**
     * Handle user functionality with proper error handling.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Handle items functionality with proper error handling.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Handle latestItem functionality with proper error handling.
     */
    public function latestItem(): HasOne
    {
        return $this->items()->one()->latestOfMany();
    }

    /**
     * Handle oldestItem functionality with proper error handling.
     */
    public function oldestItem(): HasOne
    {
        return $this->items()->one()->oldestOfMany();
    }

    /**
     * Handle highestValueItem functionality with proper error handling.
     */
    public function highestValueItem(): HasOne
    {
        return $this->items()->one()->ofMany('total', 'max');
    }

    /**
     * Handle lowestValueItem functionality with proper error handling.
     */
    public function lowestValueItem(): HasOne
    {
        return $this->items()->one()->ofMany('total', 'min');
    }

    /**
     * Handle shipping functionality with proper error handling.
     */
    public function shipping(): HasOne
    {
        return $this->hasOne(OrderShipping::class);
    }

    /**
     * Handle discountRedemptions functionality with proper error handling.
     */
    public function discountRedemptions(): HasMany
    {
        return $this->hasMany(DiscountRedemption::class);
    }

    /**
     * Handle latestDiscountRedemption functionality with proper error handling.
     */
    public function latestDiscountRedemption(): HasOne
    {
        return $this->discountRedemptions()->one()->latestOfMany();
    }

    /**
     * Handle highestValueDiscountRedemption functionality with proper error handling.
     */
    public function highestValueDiscountRedemption(): HasOne
    {
        return $this->discountRedemptions()->one()->ofMany('discount_amount', 'max');
    }

    /**
     * Handle shippingOption functionality with proper error handling.
     */
    public function shippingOption(): BelongsTo
    {
        return $this->belongsTo(ShippingOption::class);
    }

    /**
     * Handle channel functionality with proper error handling.
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    /**
     * Zone relation.
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * Handle partner functionality with proper error handling.
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * Handle documents functionality with proper error handling.
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Handle translations functionality with proper error handling.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(\App\Models\Translations\OrderTranslation::class);
    }

    /**
     * Handle latestTranslation functionality with proper error handling.
     */
    public function latestTranslation(): HasOne
    {
        return $this->translations()->one()->latestOfMany();
    }

    /**
     * Handle scopeByStatus functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Handle scopeRecent functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Handle scopeCompleted functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeCompleted($query)
    {
        return $query->whereIn('status', ['delivered', 'completed']);
    }

    /**
     * Constrain the query to orders created within the provided window so the
     * standalone orders_created_at_index can accelerate analytics workloads.
     */
    public function scopeCreatedBetween(Builder $query, CarbonInterface|DateTimeInterface|string $start, CarbonInterface|DateTimeInterface|string $end): Builder
    {
        [$startAt, $endAt] = self::normalizeRange($start, $end);
        $column = $this->qualifyCreatedAtColumn();

        // Hint the planner towards the standalone created_at index before applying the
        // date range constraint so SQLite/MySQL avoid falling back to composite scans.
        self::enforceCreatedAtIndex($query);

        return $query->whereBetween($query->qualifyColumn('created_at'), [$startAt, $endAt]);
    }

    /**
     * Constrain the query to orders created at or after the provided boundary while
     * still leaning on the created_at covering index for efficient scans.
     */
    public function scopeCreatedSince(Builder $query, CarbonInterface|DateTimeInterface|string $start): Builder
    {
        // Ensure partial window scans also leverage the dedicated created_at index for
        // analytics roll-ups and dashboard aggregations.
        self::enforceCreatedAtIndex($query);

        return $query->where($query->qualifyColumn('created_at'), '>=', self::toImmutableCarbon($start));
    }

    /**
     * Constrain the query to orders created up to the provided timestamp, relying on
     * the created_at index to keep descending windows fast.
     */
    public function scopeCreatedUntil(Builder $query, CarbonInterface|DateTimeInterface|string $end): Builder
    {
        // Apply the same index hint when only an upper bound is provided so sequential
        // scans remain fast even under heavy data volumes.
        self::enforceCreatedAtIndex($query);

        return $query->where($query->qualifyColumn('created_at'), '<=', self::toImmutableCarbon($end));
    }

    /**
     * Restrict the query to orders created on a specific day, which maps neatly to the
     * standalone created_at index when used across dashboard widgets.
     */
    public function scopeCreatedOn(Builder $query, CarbonInterface|DateTimeInterface|string $date): Builder
    {
        [$startOfDay, $endOfDay] = self::normalizeDayRange($date);

        // Keep day-level analytics aligned with the created_at index to avoid table
        // scans whenever widgets drill into a single date bucket.
        self::enforceCreatedAtIndex($query);

        return $query->whereBetween($query->qualifyColumn('created_at'), [$day->copy()->startOfDay(), $day->copy()->endOfDay()]);
    }

    public function scopeCreatedToday(Builder $query): Builder
    {
        return $this->scopeCreatedOn($query, Carbon::now());
    }

    public function scopeCreatedThisMonth(Builder $query): Builder
    {
        $now = Carbon::now();

        return $this->scopeCreatedBetween($query, $now->copy()->startOfMonth(), $now);
    }

    public function scopeCreatedLastMonth(Builder $query): Builder
    {
        $lastMonth = Carbon::now()->subMonth();

        return $this->scopeCreatedBetween($query, $lastMonth->copy()->startOfMonth(), $lastMonth->copy()->endOfMonth());
    }

    public function scopeCreatedInMonth(Builder $query, CarbonInterface|DateTimeInterface|string $date): Builder
    {
        $month = self::toImmutableCarbon($date);

        return $this->scopeCreatedBetween($query, $month->copy()->startOfMonth(), $month->copy()->endOfMonth());
    }

    // Consider orders that have been paid (preferred) or are in a paid-like lifecycle state

    /**
     * Handle scopePaid functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopePaid($query)
    {
        // Prefer explicit payment status when present and non-null
        if (Schema::hasColumn($this->getTable(), 'payment_status')) {
            $query = $query->where(function ($q): void {
                $q->whereNotNull('payment_status')->whereIn('payment_status', ['paid', 'captured', 'settled', 'authorized']);
            });
        }

        // Also include lifecycle statuses that imply payment captured
        return $query->orWhereIn('status', ['processing', 'confirmed', 'shipped', 'delivered', 'completed']);
    }
    public function scopeCreatedOnDate(Builder $query, CarbonInterface $date): Builder
    {
        $day = $date->toImmutable();

        return $query->createdBetween($day->startOfDay(), $day->endOfDay());
    }

    private function qualifyCreatedAtColumn(): string
    {
        return $this->qualifyColumn($this->getCreatedAtColumn());
    }

    /**
     * Handle isPaid functionality with proper error handling.
     */
    public function isPaid(): bool
    {
        return in_array($this->status, ['processing', 'shipped', 'delivered', 'completed']);
    }

    /**
     * Handle isShippable functionality with proper error handling.
     */
    public function isShippable(): bool
    {
        return in_array($this->status, ['processing', 'confirmed']);
    }

    /**
     * Handle canBeCancelled functionality with proper error handling.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    /**
     * Determine if the order can be returned.
     */
    public function canRequestReturn(): bool
    {
        return in_array($this->status, ['delivered', 'completed']);
    }

    /**
     * Handle getTotalItemsCountAttribute functionality with proper error handling.
     */
    public function getTotalItemsCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Handle getFormattedTotalAttribute functionality with proper error handling.
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format((float) $this->total, 2) . ' ' . $this->currency;
    }

    /**
     * Normalise dynamic date inputs into an immutable Carbon instance so scope helpers behave consistently.
     */
    private static function toImmutableCarbon(CarbonInterface|DateTimeInterface|string $value): CarbonImmutable
    {
        if ($value instanceof CarbonImmutable) {
            return $value;
        }

        if ($value instanceof CarbonInterface) {
            return $value->toImmutable();
        }

        if ($value instanceof DateTimeInterface) {
            return CarbonImmutable::make($value)
                ?? CarbonImmutable::parse($value->format('Y-m-d H:i:s.u'), $value->getTimezone());
        }

        return CarbonImmutable::parse((string) $value);
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private static function normalizeRange(CarbonInterface|DateTimeInterface|string $start, CarbonInterface|DateTimeInterface|string $end): array
    {
        $startAt = self::toImmutableCarbon($start)->startOfSecond();
        $endAt = self::toImmutableCarbon($end)->endOfSecond();

        if ($startAt->greaterThan($endAt)) {
            [$startAt, $endAt] = [$endAt, $startAt];
        }

        return [$startAt, $endAt];
    }

    /**
     * Apply a portable index hint so different database drivers consistently favour the
     * dedicated orders_created_at_index during analytics queries.
     */
    private static function enforceCreatedAtIndex(Builder $query): void
    {
        if (! self::createdAtIndexExists($query)) {
            return;
        }

        $baseTable = $query->getModel()->getTable();
        $connection = $query->getConnection();
        $prefixedTable = $connection->getTablePrefix() . $baseTable;
        $indexName = 'orders_created_at_index';

        $from = $query->getQuery()->from;

        // Abort when the builder already carries a custom FROM clause (for example when
        // joining with aliases) or the hint has been applied previously.
        if ($from instanceof Expression) {
            $fromString = (string) $from;

            if (! str_contains($fromString, $prefixedTable) || str_contains($fromString, $indexName)) {
                return;
            }
        } elseif (is_string($from)) {
            $normalizedFrom = trim($from, '`" ');

            if ($normalizedFrom !== $prefixedTable && $normalizedFrom !== $baseTable) {
                return;
            }
        } elseif ($from !== null) {
            return;
        }

        $hint = match ($connection->getDriverName()) {
            'sqlite' => sprintf('%s INDEXED BY %s', $prefixedTable, $indexName),
            'mysql', 'mariadb' => sprintf('%s USE INDEX (%s)', $prefixedTable, $indexName),
            default => null,
        };

        if ($hint === null) {
            return;
        }

        // Swap the underlying FROM clause with the hint-aware variant so explain plans
        // acknowledge the standalone created_at index even when other covering indexes
        // are present.
        $query->fromRaw($hint);
    }

    /**
     * Determine if the created_at index is present for the current connection and table.
     */
    private static function createdAtIndexExists(Builder $query): bool
    {
        $model = $query->getModel();
        $table = $model->getTable();
        $connection = $query->getConnection();
        $connectionName = $connection->getName() ?? config('database.default');

        if (! is_string($connectionName) || $connectionName === '') {
            $connectionName = config('database.default');
        }

        $cacheKey = sprintf('%s:%s', $connectionName ?? 'default', $table);

        if (array_key_exists($cacheKey, self::$createdAtIndexAvailable)) {
            return self::$createdAtIndexAvailable[$cacheKey];
        }

        try {
            $schema = Schema::connection($connectionName);
        } catch (\Throwable) {
            return self::$createdAtIndexAvailable[$cacheKey] = false;
        }

        if (! $schema->hasTable($table)) {
            return self::$createdAtIndexAvailable[$cacheKey] = false;
        }

        return self::$createdAtIndexAvailable[$cacheKey] = $schema->hasIndex($table, 'orders_created_at_index');
    }
}
