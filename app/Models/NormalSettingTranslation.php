<?php

declare (strict_types=1);
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * NormalSettingTranslation
 * 
 * Eloquent model representing the NormalSettingTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $table
 * @property mixed $fillable
 * @method static \Illuminate\Database\Eloquent\Builder|NormalSettingTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NormalSettingTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NormalSettingTranslation query()
 * @mixin \Eloquent
 */
final class NormalSettingTranslation extends Model
{
    protected $table = 'enhanced_settings_translations';
    protected $fillable = ['enhanced_setting_id', 'locale', 'description', 'display_name', 'help_text'];
    /**
     * Handle enhancedSetting functionality with proper error handling.
     * @return BelongsTo
     */
    public function enhancedSetting(): BelongsTo
    {
        return $this->belongsTo(NormalSetting::class, 'enhanced_setting_id');
    }
}