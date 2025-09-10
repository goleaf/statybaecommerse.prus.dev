<?php declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;

final class News extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $table = 'news';

    protected $fillable = [
        'is_visible',
        'published_at',
        'author_name',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected string $translationModel = \App\Models\Translations\NewsTranslation::class;

    public function isPublished(): bool
    {
        return (bool) $this->is_visible && (bool) $this->published_at && $this->published_at <= now();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(NewsCategory::class, 'news_category_pivot', 'news_id', 'news_category_id');
    }
}
