<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final /**
 * NormalSettingTranslation
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class NormalSettingTranslation extends Model
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
        return $this->belongsTo(NormalSetting::class, 'enhanced_setting_id');
    }
}
