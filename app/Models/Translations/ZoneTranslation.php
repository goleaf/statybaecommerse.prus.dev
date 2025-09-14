<?php

declare (strict_types=1);
namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * ZoneTranslation
 * 
 * Eloquent model representing the ZoneTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $table
 * @property mixed $fillable
 * @property mixed $timestamps
 * @method static \Illuminate\Database\Eloquent\Builder|ZoneTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ZoneTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ZoneTranslation query()
 * @mixin \Eloquent
 */
final class ZoneTranslation extends Model
{
    protected $table = 'zone_translations';
    protected $fillable = ['zone_id', 'locale', 'name', 'description', 'meta_title', 'meta_description', 'meta_keywords', 'short_description', 'long_description'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['meta_keywords' => 'array'];
    }
    public $timestamps = true;
    /**
     * Handle zone functionality with proper error handling.
     * @return BelongsTo
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
}