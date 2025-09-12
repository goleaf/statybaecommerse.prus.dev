<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;

final class CityTranslation extends Model
{
    protected $table = 'city_translations';

    protected $fillable = [
        'city_id',
        'locale',
        'name',
        'description',
    ];

    public $timestamps = true;
}
