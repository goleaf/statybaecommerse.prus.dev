<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final /**
 * SystemSettingCategoryTranslation
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class SystemSettingCategoryTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'system_setting_category_id',
        'locale',
        'name',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'system_setting_category_id' => 'integer',
        ];
    }

    public function systemSettingCategory(): BelongsTo
    {
        return $this->belongsTo(SystemSettingCategory::class);
    }
}
