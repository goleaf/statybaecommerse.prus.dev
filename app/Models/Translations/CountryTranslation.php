<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final /**
 * CountryTranslation
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class CountryTranslation extends Model
{
    use HasFactory;

    protected $table = 'country_translations';

    protected $fillable = [
        'country_id',
        'locale',
        'name',
        'name_official',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'country_id' => 'integer',
        ];
    }

    public $timestamps = true;
}
