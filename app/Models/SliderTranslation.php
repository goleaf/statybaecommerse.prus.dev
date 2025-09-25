<?php declare(strict_types=1);

namespace App\Models;

use Database\Factories\SliderTranslationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

final class SliderTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'slider_id',
        'locale',
        'title',
        'description',
        'button_text',
    ];

    public function slider(): BelongsTo
    {
        return $this->belongsTo(Slider::class);
    }

    protected static function newFactory(): SliderTranslationFactory
    {
        return SliderTranslationFactory::new();
    }
}
