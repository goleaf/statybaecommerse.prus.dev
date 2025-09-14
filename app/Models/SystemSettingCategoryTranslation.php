<?php

declare (strict_types=1);
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * SystemSettingCategoryTranslation
 * 
 * Eloquent model representing the SystemSettingCategoryTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $fillable
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingCategoryTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingCategoryTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingCategoryTranslation query()
 * @mixin \Eloquent
 */
final class SystemSettingCategoryTranslation extends Model
{
    use HasFactory;
    protected $fillable = ['system_setting_category_id', 'locale', 'name', 'description'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['system_setting_category_id' => 'integer'];
    }
    /**
     * Handle systemSettingCategory functionality with proper error handling.
     * @return BelongsTo
     */
    public function systemSettingCategory(): BelongsTo
    {
        return $this->belongsTo(SystemSettingCategory::class);
    }
}