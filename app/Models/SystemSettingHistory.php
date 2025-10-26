<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use Illuminate\Database\Eloquent\Builder;
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
    use OrdersByName {
        getNameColumn as protected resolveNameColumnForOrdering;
        scopeOrderedByName as protected scopeOrderedByNameFromTrait;
    }

    /**
     * Sort history entries by the related system setting key via the shared
     * OrdersByName scope.
     */
    protected string $nameColumn = 'key';

    protected $fillable = ['system_setting_id', 'old_value', 'new_value', 'changed_by', 'change_reason', 'ip_address', 'user_agent', 'meta'];

    protected $casts = ['old_value' => 'string', 'new_value' => 'string', 'changed_by' => 'integer', 'meta' => 'array'];

    /**
     * Handle systemSetting functionality with proper error handling.
     */
    public function systemSetting(): BelongsTo
    {
        return $this->belongsTo(SystemSetting::class)->withoutGlobalScopes();
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
     * @param mixed $value
     */
    private function formatValue($value): string
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_PRETTY_PRINT);
        }

        return (string) $value;
    }

    /**
     * Order change records by the owning setting key with deterministic
     * tiebreakers.
     */
    public function scopeOrderedByName(Builder $query, string $direction = 'asc'): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        if ($this->resolveNameColumnForOrdering() !== 'key') {
            return $this->scopeOrderedByNameFromTrait($query, $direction);
        }

        $settingsTable = (new SystemSetting)->getTable();
        $keySelector = SystemSetting::query()
            ->select('key')
            ->whereColumn("{$settingsTable}.id", $this->qualifyColumn('system_setting_id'))
            ->limit(1);

        return $query
            ->orderBy($keySelector, $direction)
            ->orderBy($this->qualifyColumn($this->getKeyName()));
    }
}
