<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final /**
 * PostTranslation
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class PostTranslation extends Model
{
    use HasFactory;
    
    protected static string $factory = \Database\Factories\PostTranslationFactory::class;
    
    protected $table = 'post_translations';

    protected $fillable = [
        'post_id',
        'locale',
        'title',
        'slug',
        'content',
        'excerpt',
        'meta_title',
        'meta_description',
        'tags',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'metadata' => 'array',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Post::class);
    }
}
