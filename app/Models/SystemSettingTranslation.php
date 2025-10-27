<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
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
    use OrdersByName;
    use SoftDeletes;

    /**
     * Sort translations by their name value to keep locale selectors intuitive
     * across the administration experience.
     */
    protected string $nameColumn = 'name';

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
        'rich_description',
        'attachments',
        'meta',
        'metadata',
        'tags',
        'is_active',
        'is_public',
        'sort_order',
    ];

    protected $casts = [
        'metadata'    => 'array',
        'meta'        => 'array',
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
