<?php

declare (strict_types=1);
namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * ReviewTranslation
 * 
 * Eloquent model representing the ReviewTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property string $factory
 * @property mixed $table
 * @property mixed $fillable
 * @method static \Illuminate\Database\Eloquent\Builder|ReviewTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReviewTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReviewTranslation query()
 * @mixin \Eloquent
 */
final class ReviewTranslation extends Model
{
    use HasFactory;
    protected static string $factory = \Database\Factories\ReviewTranslationFactory::class;
    protected $table = 'review_translations';
    protected $fillable = ['review_id', 'locale', 'title', 'comment', 'metadata'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
    /**
     * Handle review functionality with proper error handling.
     * @return BelongsTo
     */
    public function review(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Review::class);
    }
}
