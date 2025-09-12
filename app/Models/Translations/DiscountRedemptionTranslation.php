<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class DiscountRedemptionTranslation extends Model
{
    protected $table = 'discount_redemption_translations';

    protected $fillable = [
        'discount_redemption_id',
        'locale',
        'notes',
        'status_description',
        'metadata_description',
    ];

    protected function casts(): array
    {
        return [
            'metadata_description' => 'array',
        ];
    }

    /**
     * Get the discount redemption this translation belongs to
     */
    public function discountRedemption(): BelongsTo
    {
        return $this->belongsTo(\App\Models\DiscountRedemption::class);
    }
}
