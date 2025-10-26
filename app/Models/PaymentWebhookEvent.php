<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentWebhookEventStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PaymentWebhookEvent persists incoming webhook metadata so repeated callbacks
 * can be detected and ignored without performing duplicate side effects.
 */
class PaymentWebhookEvent extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'provider',
        'event_id',
        'order_id',
        'status',
        'payload',
        'processed_at',
    ];

    /**
     * Cast the status and payload fields for easier consumption.
     */
    protected function casts(): array
    {
        return [
            'status'       => PaymentWebhookEventStatus::class,
            'payload'      => 'array',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * Link the webhook event back to the affected order when available.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
