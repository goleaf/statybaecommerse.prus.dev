<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * OrderTranslation
 *
 * Eloquent model representing the OrderTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|OrderTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderTranslation query()
 *
 * @mixin \Eloquent
 */
final class OrderTranslation extends Model
{
    protected $table = 'order_translations';

    protected $fillable = ['order_id', 'locale', 'notes', 'billing_address', 'shipping_address'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['billing_address' => 'json', 'shipping_address' => 'json'];
    }

    /**
     * Handle order functionality with proper error handling.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Order::class);
    }
}
