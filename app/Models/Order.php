<?php

declare(strict_types=1);

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

#[ScopedBy([ActiveScope::class, StatusScope::class])]
final /**
 * Order
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class Order extends Model
{
    use HasFactory, HasTranslations, LogsActivity, SoftDeletes;

    public array $translatable = [
        'notes',
        'billing_address',
        'shipping_address',
    ];

    protected $fillable = [
        'number',
        'user_id',
        'status',
        'subtotal',
        'tax_amount',
        'shipping_amount',
        'discount_amount',
        'total',
        'currency',
        'billing_address',
        'shipping_address',
        'notes',
        'shipped_at',
        'delivered_at',
        'channel_id',
        'zone_id',
        'partner_id',
        'payment_status',
        'payment_method',
        'payment_reference',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'billing_address' => 'json',
            'shipping_address' => 'json',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'total_items_count',
        'formatted_total',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['number', 'status', 'total', 'notes', 'tracking_number', 'fulfillment_status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Order {$eventName}")
            ->useLogName('order');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the order's latest item.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestItem(): HasOne
    {
        return $this->items()->one()->latestOfMany();
    }

    /**
     * Get the order's highest value item.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function highestValueItem(): HasOne
    {
        return $this->items()->one()->ofMany('total', 'max');
    }

    public function shipping(): HasOne
    {
        return $this->hasOne(OrderShipping::class);
    }

    public function discountRedemptions(): HasMany
    {
        return $this->hasMany(DiscountRedemption::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(\App\Models\Translations\OrderTranslation::class);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeCompleted($query)
    {
        return $query->whereIn('status', ['delivered', 'completed']);
    }

    // Consider orders that have been paid (preferred) or are in a paid-like lifecycle state
    public function scopePaid($query)
    {
        // Prefer explicit payment status when present and non-null
        if (\Schema::hasColumn($this->getTable(), 'payment_status')) {
            $query = $query->where(function ($q) {
                $q
                    ->whereNotNull('payment_status')
                    ->whereIn('payment_status', ['paid', 'captured', 'settled', 'authorized']);
            });
        }

        // Also include lifecycle statuses that imply payment captured
        return $query->orWhereIn('status', ['processing', 'confirmed', 'shipped', 'delivered', 'completed']);
    }

    public function isPaid(): bool
    {
        return in_array($this->status, ['processing', 'shipped', 'delivered', 'completed']);
    }

    public function isShippable(): bool
    {
        return in_array($this->status, ['processing', 'confirmed']);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function getTotalItemsCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function getFormattedTotalAttribute(): string
    {
        return number_format((float) $this->total, 2).' '.$this->currency;
    }
}
