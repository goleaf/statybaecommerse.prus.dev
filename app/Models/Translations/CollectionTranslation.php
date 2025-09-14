<?php

declare(strict_types=1);

namespace App\Models\Translations;

use App\Models\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final /**
 * CollectionTranslation
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class CollectionTranslation extends Model
{
    use HasFactory;
    protected static string $factory = \Database\Factories\CollectionTranslationFactory::class;
    protected $table = 'collection_translations';

    protected $fillable = [
        'collection_id',
        'locale',
        'name',
        'slug',
        'description',
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
