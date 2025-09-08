<?php declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;

final class CountryTranslation extends Model
{
    protected $table = 'country_translations';

    protected $fillable = [
        'country_id',
        'locale',
        'name',
        'name_official',
    ];

    public $timestamps = true;
}
