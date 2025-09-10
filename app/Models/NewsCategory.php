<?php declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;

final class NewsCategory extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $table = 'news_categories';

    protected $fillable = [
        'is_visible',
        'parent_id',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'parent_id' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    protected string $translationModel = \App\Models\Translations\NewsCategoryTranslation::class;

    public function news(): BelongsToMany
    {
        return $this->belongsToMany(News::class, 'news_category_pivot', 'news_category_id', 'news_id');
    }
}
