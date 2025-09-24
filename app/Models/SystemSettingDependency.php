<?php

declare(strict_types=1);

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
 *
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingDependency newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingDependency newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingDependency query()
 *
 * @mixin \Eloquent
 */
final class SystemSettingDependency extends Model
{
    use HasFactory;

    protected $fillable = ['setting_id', 'depends_on_setting_id', 'condition', 'is_active'];

    protected $casts = ['condition' => 'json', 'is_active' => 'boolean'];

    /**
     * Handle setting functionality with proper error handling.
     */
    public function setting(): BelongsTo
    {
        return $this->belongsTo(SystemSetting::class, 'setting_id');
    }

    /**
     * Handle dependsOn functionality with proper error handling.
     */
    public function dependsOn(): BelongsTo
    {
        return $this->belongsTo(SystemSetting::class, 'depends_on_setting_id');
    }

    /**
     * Handle dependsOnSetting functionality with proper error handling.
     */
    public function dependsOnSetting(): BelongsTo
    {
        return $this->belongsTo(SystemSetting::class, 'depends_on_setting_id');
    }

    /**
     * Handle scopeActive functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Handle scopeInactive functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Handle scopeForSetting functionality with proper error handling.
     *
     * @param  mixed  $query
     * @param  mixed  $settingId
     */
    public function scopeForSetting($query, $settingId)
    {
        return $query->where('setting_id', $settingId);
    }

    /**
     * Handle scopeDependsOnSetting functionality with proper error handling.
     *
     * @param  mixed  $query
     * @param  mixed  $settingId
     */
    public function scopeDependsOnSetting($query, $settingId)
    {
        return $query->where('depends_on_setting_id', $settingId);
    }

    /**
     * Handle scopeWithCondition functionality with proper error handling.
     *
     * @param  mixed  $query
     * @param  mixed  $condition
     */
    public function scopeWithCondition($query, $condition)
    {
        return $query->where('condition', 'like', "%{$condition}%");
    }

    /**
     * Handle scopeCreatedBetween functionality with proper error handling.
     *
     * @param  mixed  $query
     * @param  mixed  $from
     * @param  mixed  $to
     */
    public function scopeCreatedBetween($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * Handle scopeUpdatedBetween functionality with proper error handling.
     *
     * @param  mixed  $query
     * @param  mixed  $from
     * @param  mixed  $to
     */
    public function scopeUpdatedBetween($query, $from, $to)
    {
        return $query->whereBetween('updated_at', [$from, $to]);
    }

    /**
     * Handle scopeSearch functionality with proper error handling.
     *
     * @param  mixed  $query
     * @param  mixed  $search
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('condition', 'like', "%{$search}%")
                ->orWhereHas('setting', function ($q) use ($search) {
                    $q->where('key', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                })
                ->orWhereHas('dependsOnSetting', function ($q) use ($search) {
                    $q->where('key', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
        });
    }

    /**
     * Handle scopeOrderByCreatedAt functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeOrderByCreatedAt($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Handle scopeOrderByUpdatedAt functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeOrderByUpdatedAt($query)
    {
        return $query->orderBy('updated_at', 'desc');
    }

    /**
     * Handle scopeOrderByCondition functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeOrderByCondition($query)
    {
        return $query->orderBy('condition', 'asc');
    }

    /**
     * Handle scopeOrderByActiveStatus functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeOrderByActiveStatus($query)
    {
        return $query->orderBy('is_active', 'desc');
    }

    /**
     * Handle isConditionMet functionality with proper error handling.
     */
    public function isConditionMet(): bool
    {
        if (! $this->dependsOn) {
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
            'not_contains' => ! str_contains($dependencyValue, $condition['value']),
            'in' => in_array($dependencyValue, $condition['value'] ?? []),
            'not_in' => ! in_array($dependencyValue, $condition['value'] ?? []),
            default => false,
        };
    }
}
