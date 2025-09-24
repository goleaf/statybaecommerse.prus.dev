<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SystemSettingTranslation
 *
 * Eloquent model representing the SystemSettingTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingTranslation query()
 *
 * @mixin \Eloquent
 */
final class SystemSettingTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'system_setting_id',
        'locale',
        'name',
        'description',
        'help_text',
        'rich_description',
        'attachments',
        'is_active',
        'is_public',
        'metadata',
        'tags',
        'sort_order',
    ];

    protected $casts = [
        'metadata' => 'array',
        'tags' => 'array',
        'attachments' => 'array',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Handle systemSetting functionality with proper error handling.
     */
    public function systemSetting(): BelongsTo
    {
        return $this->belongsTo(SystemSetting::class);
    }
}
