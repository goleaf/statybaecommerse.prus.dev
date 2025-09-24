<?php

declare(strict_types=1);

namespace App\Models\Translations;

use App\Models\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CollectionTranslation
 *
 * Eloquent model representing the CollectionTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property string $factory
 * @property mixed $table
 * @property mixed $fillable
 * @property mixed $timestamps
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionTranslation query()
 *
 * @mixin \Eloquent
 */
final class CollectionTranslation extends Model
{
    use HasFactory;

    protected static string $factory = \Database\Factories\CollectionTranslationFactory::class;

    protected $table = 'collection_translations';

    protected $fillable = ['collection_id', 'locale', 'name', 'slug', 'description', 'meta_title', 'meta_description', 'meta_keywords'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['meta_keywords' => 'array'];
    }

    public $timestamps = true;

    /**
     * Handle collection functionality with proper error handling.
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }
}
