<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * LocationTranslation
 *
 * Eloquent model representing the LocationTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property string $factory
 * @property mixed $table
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|LocationTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LocationTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LocationTranslation query()
 *
 * @mixin \Eloquent
 */
final class LocationTranslation extends Model
{
    use HasFactory;

    protected $table = 'location_translations';

    protected $fillable = ['location_id', 'locale', 'name', 'slug', 'description'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['location_id' => 'integer'];
    }

    /**
     * Handle location functionality with proper error handling.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
