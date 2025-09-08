<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class EnhancedSettingTranslation extends Model
{
    protected $table = 'enhanced_settings_translations';

    protected $fillable = [
        'enhanced_setting_id',
        'locale',
        'description',
        'display_name',
        'help_text',
    ];

    public function enhancedSetting(): BelongsTo
    {
        return $this->belongsTo(EnhancedSetting::class);
    }
}
