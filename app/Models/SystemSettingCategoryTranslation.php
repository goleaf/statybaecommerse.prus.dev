<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SystemSettingCategoryTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'system_setting_category_id',
        'locale',
        'name',
        'description',
    ];

    public function systemSettingCategory(): BelongsTo
    {
        return $this->belongsTo(SystemSettingCategory::class);
    }
}
