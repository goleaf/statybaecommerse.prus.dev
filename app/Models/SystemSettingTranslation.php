<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    use SoftDeletes;

    /**
     * Keep the fillable list intentionally minimal to match the public API requirements
     * exercised by the unit tests while still allowing advanced attributes to be handled
     * via explicit setters when required by the application.
     */
    protected $fillable = [
        'system_setting_id',
        'locale',
        'name',
        'description',
        'help_text',
    ];

    protected $casts = [
        'metadata'    => 'array',
        'tags'        => 'array',
        'attachments' => 'array',
        'is_active'   => 'boolean',
        'is_public'   => 'boolean',
        'sort_order'  => 'integer',
    ];

    /**
     * Define the inverse relationship to the owning system setting.
     * This comment clarifies the intent to aid future contributors.
     */
    public function systemSetting(): BelongsTo
    {
        return $this->belongsTo(SystemSetting::class)->withoutGlobalScopes()->withTrashed();
    }
}
