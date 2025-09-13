<?php

declare(strict_types=1);

namespace App\Models\Translations;

use App\Models\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected function casts(): array
    {
        return [
            'meta_keywords' => 'array',
        ];
    }

    public $timestamps = true;

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }
}
