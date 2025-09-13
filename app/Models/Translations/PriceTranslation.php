<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PriceTranslation extends Model
{
    use HasFactory;

    protected $table = 'price_translations';

    protected $fillable = [
        'price_id',
        'locale',
        'name',
        'description',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'price_id' => 'integer',
        ];
    }

    public function price(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Price::class);
    }
}
