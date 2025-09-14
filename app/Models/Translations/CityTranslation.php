<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;

final /**
 * CityTranslation
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class CityTranslation extends Model
{
    protected $table = 'city_translations';

    protected $fillable = [
        'city_id',
        'locale',
        'name',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'city_id' => 'integer',
        ];
    }

    public $timestamps = true;
}
