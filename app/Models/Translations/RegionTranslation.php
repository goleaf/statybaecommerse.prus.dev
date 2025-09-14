<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final /**
 * RegionTranslation
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class RegionTranslation extends Model
{
    use HasFactory;

    protected static string $factory = \Database\Factories\RegionTranslationFactory::class;

    protected $table = 'region_translations';

    protected $fillable = [
        'region_id',
        'locale',
        'name',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'region_id' => 'integer',
        ];
    }

    public $timestamps = true;

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
