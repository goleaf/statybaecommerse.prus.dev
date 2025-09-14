<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final /**
 * SystemSettingTranslation
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class SystemSettingTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'system_setting_id',
        'locale',
        'name',
        'description',
        'help_text',
    ];

    public function systemSetting(): BelongsTo
    {
        return $this->belongsTo(SystemSetting::class);
    }
}
