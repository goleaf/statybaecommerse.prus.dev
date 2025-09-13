<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PriceListItemTranslation extends Model
{
    protected $table = 'price_list_item_translations';

    protected $fillable = [
        'price_list_item_id',
        'locale',
        'name',
        'description',
        'notes',
    ];

    protected $casts = [
        'price_list_item_id' => 'integer',
    ];

    public $timestamps = true;

    public function priceListItem(): BelongsTo
    {
        return $this->belongsTo(\App\Models\PriceListItem::class);
    }
}
