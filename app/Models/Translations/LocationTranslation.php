<?php declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;

final class LocationTranslation extends Model
{
    protected $table = 'location_translations';
    protected $guarded = [];
    public $timestamps = true;
}

