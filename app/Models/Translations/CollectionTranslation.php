<?php declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;

final class CollectionTranslation extends Model
{
    protected $table = 'collection_translations';

    protected $fillable = [
        'collection_id',
        'locale',
        'name',
        'description',
        'seo_title',
        'seo_description',
    ];

    public $timestamps = true;
}
