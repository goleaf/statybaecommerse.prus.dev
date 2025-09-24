<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SystemSettingHistory
 *
 * Eloquent model representing the SystemSettingHistory entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 *
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingHistory query()
 *
 * @mixin \Eloquent
 */
final class SystemSettingHistory extends Model
{
    use HasFactory;

    protected $fillable = ['system_setting_id', 'old_value', 'new_value', 'changed_by', 'change_reason', 'ip_address', 'user_agent'];

    protected $casts = ['old_value' => 'json', 'new_value' => 'json', 'changed_by' => 'integer'];

    /**
     * Handle systemSetting functionality with proper error handling.
     */
    public function systemSetting(): BelongsTo
    {
        return $this->belongsTo(SystemSetting::class);
    }

    /**
     * Handle user functionality with proper error handling.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Handle getFormattedOldValue functionality with proper error handling.
     */
    public function getFormattedOldValue(): string
    {
        return $this->formatValue($this->old_value);
    }

    /**
     * Handle getFormattedNewValue functionality with proper error handling.
     */
    public function getFormattedNewValue(): string
    {
        return $this->formatValue($this->new_value);
    }

    /**
     * Handle formatValue functionality with proper error handling.
     *
     * @param  mixed  $value
     */
    private function formatValue($value): string
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_PRETTY_PRINT);
        }

        return (string) $value;
    }
}
