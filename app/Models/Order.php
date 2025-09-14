<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\StatusScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;
/**
 * Order
 * 
 * Eloquent model representing the Order entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property array $translatable
 * @property mixed $fillable
 * @property mixed $appends
 * @method static \Illuminate\Database\Eloquent\Builder|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, StatusScope::class])]
final class Order extends Model
{
    use HasFactory, HasTranslations, LogsActivity, SoftDeletes;
    public array $translatable = ['notes', 'billing_address', 'shipping_address'];
    protected $fillable = ['number', 'user_id', 'status', 'subtotal', 'tax_amount', 'shipping_amount', 'discount_amount', 'total', 'currency', 'billing_address', 'shipping_address', 'notes', 'shipped_at', 'delivered_at', 'channel_id', 'zone_id', 'partner_id', 'payment_status', 'payment_method', 'payment_reference'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['subtotal' => 'decimal:2', 'tax_amount' => 'decimal:2', 'shipping_amount' => 'decimal:2', 'discount_amount' => 'decimal:2', 'total' => 'decimal:2', 'billing_address' => 'json', 'shipping_address' => 'json', 'shipped_at' => 'datetime', 'delivered_at' => 'datetime'];
    }
    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['total_items_count', 'formatted_total'];
    /**
     * Handle getActivitylogOptions functionality with proper error handling.
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['number', 'status', 'total', 'notes', 'tracking_number', 'fulfillment_status'])->logOnlyDirty()->dontSubmitEmptyLogs()->setDescriptionForEvent(fn(string $eventName) => "Order {$eventName}")->useLogName('order');
    }
    /**
     * Handle user functionality with proper error handling.
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Handle items functionality with proper error handling.
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
    /**
     * Handle latestItem functionality with proper error handling.
     * @return HasOne
     */
    public function latestItem(): HasOne
    {
        return $this->items()->one()->latestOfMany();
    }
    /**
     * Handle oldestItem functionality with proper error handling.
     * @return HasOne
     */
    public function oldestItem(): HasOne
    {
        return $this->items()->one()->oldestOfMany();
    }
    /**
     * Handle highestValueItem functionality with proper error handling.
     * @return HasOne
     */
    public function highestValueItem(): HasOne
    {
        return $this->items()->one()->ofMany('total', 'max');
    }
    /**
     * Handle lowestValueItem functionality with proper error handling.
     * @return HasOne
     */
    public function lowestValueItem(): HasOne
    {
        return $this->items()->one()->ofMany('total', 'min');
    }
    /**
     * Handle shipping functionality with proper error handling.
     * @return HasOne
     */
    public function shipping(): HasOne
    {
        return $this->hasOne(OrderShipping::class);
    }
    /**
     * Handle discountRedemptions functionality with proper error handling.
     * @return HasMany
     */
    public function discountRedemptions(): HasMany
    {
        return $this->hasMany(DiscountRedemption::class);
    }
    /**
     * Handle latestDiscountRedemption functionality with proper error handling.
     * @return HasOne
     */
    public function latestDiscountRedemption(): HasOne
    {
        return $this->discountRedemptions()->one()->latestOfMany();
    }
    /**
     * Handle highestValueDiscountRedemption functionality with proper error handling.
     * @return HasOne
     */
    public function highestValueDiscountRedemption(): HasOne
    {
        return $this->discountRedemptions()->one()->ofMany('discount_amount', 'max');
    }
    /**
     * Handle zone functionality with proper error handling.
     * @return BelongsTo
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
    /**
     * Handle channel functionality with proper error handling.
     * @return BelongsTo
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }
    /**
     * Handle partner functionality with proper error handling.
     * @return BelongsTo
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
    /**
     * Handle documents functionality with proper error handling.
     * @return MorphMany
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
    /**
     * Handle translations functionality with proper error handling.
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(\App\Models\Translations\OrderTranslation::class);
    }
    /**
     * Handle latestTranslation functionality with proper error handling.
     * @return HasOne
     */
    public function latestTranslation(): HasOne
    {
        return $this->translations()->one()->latestOfMany();
    }
    /**
     * Handle scopeByStatus functionality with proper error handling.
     * @param mixed $query
     * @param string $status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
    /**
     * Handle scopeRecent functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
    /**
     * Handle scopeCompleted functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeCompleted($query)
    {
        return $query->whereIn('status', ['delivered', 'completed']);
    }
    // Consider orders that have been paid (preferred) or are in a paid-like lifecycle state
    /**
     * Handle scopePaid functionality with proper error handling.
     * @param mixed $query
     */
    public function scopePaid($query)
    {
        // Prefer explicit payment status when present and non-null
        if (\Schema::hasColumn($this->getTable(), 'payment_status')) {
            $query = $query->where(function ($q) {
                $q->whereNotNull('payment_status')->whereIn('payment_status', ['paid', 'captured', 'settled', 'authorized']);
            });
        }
        // Also include lifecycle statuses that imply payment captured
        return $query->orWhereIn('status', ['processing', 'confirmed', 'shipped', 'delivered', 'completed']);
    }
    /**
     * Handle isPaid functionality with proper error handling.
     * @return bool
     */
    public function isPaid(): bool
    {
        return in_array($this->status, ['processing', 'shipped', 'delivered', 'completed']);
    }
    /**
     * Handle isShippable functionality with proper error handling.
     * @return bool
     */
    public function isShippable(): bool
    {
        return in_array($this->status, ['processing', 'confirmed']);
    }
    /**
     * Handle canBeCancelled functionality with proper error handling.
     * @return bool
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }
    /**
     * Handle getTotalItemsCountAttribute functionality with proper error handling.
     * @return int
     */
    public function getTotalItemsCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }
    /**
     * Handle getFormattedTotalAttribute functionality with proper error handling.
     * @return string
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format((float) $this->total, 2) . ' ' . $this->currency;
    }
}