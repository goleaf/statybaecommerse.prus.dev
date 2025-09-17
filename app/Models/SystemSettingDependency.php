<?php

declare (strict_types=1);
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * SystemSettingDependency
 * 
 * Eloquent model representing the SystemSettingDependency entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $fillable
 * @property mixed $casts
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingDependency newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingDependency newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingDependency query()
 * @mixin \Eloquent
 */
final class SystemSettingDependency extends Model
{
    use HasFactory;
    protected $fillable = ['setting_id', 'depends_on_setting_id', 'condition', 'is_active'];
    protected $casts = ['condition' => 'json', 'is_active' => 'boolean'];
    /**
     * Handle setting functionality with proper error handling.
     * @return BelongsTo
     */
    public function setting(): BelongsTo
    {
        return $this->belongsTo(SystemSetting::class, 'setting_id');
    }
    /**
     * Handle dependsOn functionality with proper error handling.
     * @return BelongsTo
     */
    public function dependsOn(): BelongsTo
    {
        return $this->belongsTo(SystemSetting::class, 'depends_on_setting_id');
    }
    /**
     * Handle scopeActive functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    /**
     * Handle isConditionMet functionality with proper error handling.
     * @return bool
     */
    public function isConditionMet(): bool
    {
        if (!$this->dependsOn) {
            return false;
        }
        $dependencyValue = $this->dependsOn->value;
        $condition = $this->condition;
        return match ($condition['operator'] ?? 'equals') {
            'equals' => $dependencyValue == $condition['value'],
            'not_equals' => $dependencyValue != $condition['value'],
            'greater_than' => $dependencyValue > $condition['value'],
            'less_than' => $dependencyValue < $condition['value'],
            'contains' => str_contains($dependencyValue, $condition['value']),
            'not_contains' => !str_contains($dependencyValue, $condition['value']),
            'in' => in_array($dependencyValue, $condition['value'] ?? []),
            'not_in' => !in_array($dependencyValue, $condition['value'] ?? []),
            default => false,
        };
    }
}