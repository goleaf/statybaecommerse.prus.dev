<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DiscountRedemptionTranslation
 *
 * Eloquent model representing the DiscountRedemptionTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountRedemptionTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountRedemptionTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountRedemptionTranslation query()
 *
 * @mixin \Eloquent
 */
final class DiscountRedemptionTranslation extends Model
{
    protected $table = 'discount_redemption_translations';

    protected $fillable = ['discount_redemption_id', 'locale', 'notes', 'status_description', 'metadata_description'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['metadata_description' => 'array'];
    }

    /**
     * Handle discountRedemption functionality with proper error handling.
     */
    public function discountRedemption(): BelongsTo
    {
        return $this->belongsTo(\App\Models\DiscountRedemption::class);
    }
}
