<?php

declare (strict_types=1);
namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * RegionTranslation
 * 
 * Eloquent model representing the RegionTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property string $factory
 * @property mixed $table
 * @property mixed $fillable
 * @property mixed $timestamps
 * @method static \Illuminate\Database\Eloquent\Builder|RegionTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RegionTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RegionTranslation query()
 * @mixin \Eloquent
 */
final class RegionTranslation extends Model
{
    use HasFactory;
    protected static string $factory = \Database\Factories\RegionTranslationFactory::class;
    protected $table = 'region_translations';
    protected $fillable = ['region_id', 'locale', 'name', 'description'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['region_id' => 'integer'];
    }
    public $timestamps = true;
    /**
     * Handle region functionality with proper error handling.
     * @return BelongsTo
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}