<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;

final /**
 * CurrencyTranslation
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class CurrencyTranslation extends Model
{
    protected $table = 'currency_translations';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'currency_id' => 'integer',
        ];
    }

    public $timestamps = true;
}
