<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final /**
 * OrderTranslation
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class OrderTranslation extends Model
{
    protected $table = 'order_translations';

    protected $fillable = [
        'order_id',
        'locale',
        'notes',
        'billing_address',
        'shipping_address',
    ];

    protected function casts(): array
    {
        return [
            'billing_address' => 'json',
            'shipping_address' => 'json',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Order::class);
    }
}
