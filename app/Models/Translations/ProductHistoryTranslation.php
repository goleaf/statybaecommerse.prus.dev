<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;

final class ProductHistoryTranslation extends Model
{
    protected $fillable = [
        'product_history_id',
        'locale',
        'action',
        'description',
        'field_name',
    ];

    protected $casts = [
        'action' => 'string',
        'description' => 'string',
        'field_name' => 'string',
    ];

    protected $table = 'product_history_translations';

    public $timestamps = false;

    public function productHistory()
    {
        return $this->belongsTo(\App\Models\ProductHistory::class);
    }
}
