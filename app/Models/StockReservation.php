<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

/**
 * @property int    $quantity
 * @property string $status
 */
final class StockReservation extends Model
{
    use HasFactory;

    public const STATUS_RESERVED = 'reserved';

    public const STATUS_RELEASED = 'released';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'product_id',
        'variant_inventory_id',
        'quantity',
        'status',
        'reserved_at',
        'expires_at',
        'released_at',
        'consumed_at',
        'meta',
        'reference_type',
        'reference_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity'    => 'integer',
            'reserved_at' => 'datetime',
            'expires_at'  => 'datetime',
            'released_at' => 'datetime',
            'consumed_at' => 'datetime',
            'meta'        => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variantInventory(): BelongsTo
    {
        return $this->belongsTo(VariantInventory::class);
    }

    /**
     * Scope active (non-expired) reservations.
     */
    public function scopeActive($query)
    {
        return $query
            ->where('status', self::STATUS_RESERVED)
            ->where(function ($query): void {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', Carbon::now());
            });
    }

    /**
     * Scope expired reservations.
     */
    public function scopeExpired($query)
    {
        return $query
            ->where('status', self::STATUS_RESERVED)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', Carbon::now());
    }

    /**
     * Release the reservation entirely or partially.
     */
    public function release(?int $quantity = null): void
    {
        $this->ensurePositiveQuantity($quantity);

        if ($quantity === null || $quantity >= $this->quantity) {
            $this->forceFill([
                'status'      => self::STATUS_RELEASED,
                'released_at' => Carbon::now(),
            ])->save();

            return;
        }

        $this->decrement('quantity', $quantity);

        $this->replicate()
            ->forceFill([
                'quantity'    => $quantity,
                'status'      => self::STATUS_RELEASED,
                'released_at' => Carbon::now(),
            ])
            ->save();

        $this->refresh();
    }

    /**
     * Consume a portion of the reservation as fulfilled stock.
     */
    public function consume(int $quantity): void
    {
        $this->ensurePositiveQuantity($quantity);

        if ($quantity >= $this->quantity) {
            $this->forceFill([
                'status'      => self::STATUS_COMPLETED,
                'consumed_at' => Carbon::now(),
            ])->save();

            return;
        }

        $this->decrement('quantity', $quantity);

        $this->replicate()
            ->forceFill([
                'quantity'    => $quantity,
                'status'      => self::STATUS_COMPLETED,
                'consumed_at' => Carbon::now(),
            ])
            ->save();

        $this->refresh();
    }

    /**
     * Mark reservation as expired when the expiration date passes.
     */
    public function expire(): void
    {
        $this->forceFill([
            'status'      => self::STATUS_EXPIRED,
            'released_at' => Carbon::now(),
        ])->save();
    }

    private function ensurePositiveQuantity(?int $quantity): void
    {
        if ($quantity !== null && $quantity <= 0) {
            throw new InvalidArgumentException('Reservation adjustments must use positive quantities.');
        }
    }
}
