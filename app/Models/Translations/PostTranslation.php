<?php

declare (strict_types=1);
namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * PostTranslation
 * 
 * Eloquent model representing the PostTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property string $factory
 * @property mixed $table
 * @property mixed $fillable
 * @method static \Illuminate\Database\Eloquent\Builder|PostTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PostTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PostTranslation query()
 * @mixin \Eloquent
 */
final class PostTranslation extends Model
{
    use HasFactory;
    protected static string $factory = \Database\Factories\PostTranslationFactory::class;
    protected $table = 'post_translations';
    protected $fillable = ['post_id', 'locale', 'title', 'slug', 'content', 'excerpt', 'meta_title', 'meta_description', 'tags', 'metadata'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['tags' => 'array', 'metadata' => 'array'];
    }
    /**
     * Handle post functionality with proper error handling.
     * @return BelongsTo
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Post::class);
    }
}