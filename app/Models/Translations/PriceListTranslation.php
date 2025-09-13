<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PriceListTranslation extends Model
{
    protected $table = 'price_list_translations';

    protected $fillable = [
        'price_list_id',
        'locale',
        'name',
        'description',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected function casts(): array
    {
        return [
            'price_list_id' => 'integer',
            'meta_keywords' => 'array',
        ];
    }

    public $timestamps = true;

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(\App\Models\PriceList::class);
    }
}
