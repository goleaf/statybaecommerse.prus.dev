<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final /**
 * SystemSettingHistory
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class SystemSettingHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'system_setting_id',
        'old_value',
        'new_value',
        'changed_by',
        'change_reason',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_value' => 'json',
        'new_value' => 'json',
        'changed_by' => 'integer',
    ];

    public function systemSetting(): BelongsTo
    {
        return $this->belongsTo(SystemSetting::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getFormattedOldValue(): string
    {
        return $this->formatValue($this->old_value);
    }

    public function getFormattedNewValue(): string
    {
        return $this->formatValue($this->new_value);
    }

    private function formatValue($value): string
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_PRETTY_PRINT);
        }

        return (string) $value;
    }
}
