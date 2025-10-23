<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use App\Observers\AttributionObserver;
use App\Observers\UserAttributionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * FeatureFlag
 *
 * Eloquent model representing the FeatureFlag entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 *
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureFlag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureFlag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureFlag query()
 *
 * @mixin \Eloquent
 */
#[ObservedBy([AttributionObserver::class])]
#[ScopedBy([ActiveScope::class, EnabledScope::class])]
#[ObservedBy([UserAttributionObserver::class])]
final class FeatureFlag extends Model
{
    use HasFactory;

    protected $with = ['creator', 'updater'];

    protected $fillable = ['name', 'key', 'description', 'is_active', 'is_enabled', 'is_global', 'conditions', 'rollout_percentage', 'environment', 'starts_at', 'ends_at', 'start_date', 'end_date', 'metadata', 'priority', 'category', 'impact_level', 'rollout_strategy', 'rollback_plan', 'success_metrics', 'approval_status', 'approval_notes', 'created_by', 'created_by_name', 'updated_by', 'updated_by_name', 'last_activated', 'last_deactivated'];

    protected $casts = ['is_active' => 'boolean', 'is_enabled' => 'boolean', 'is_global' => 'boolean', 'conditions' => 'json', 'rollout_percentage' => 'json', 'metadata' => 'json', 'success_metrics' => 'json', 'starts_at' => 'datetime', 'ends_at' => 'datetime', 'start_date' => 'datetime', 'end_date' => 'datetime', 'last_activated' => 'datetime', 'last_deactivated' => 'datetime', 'created_by' => 'integer', 'updated_by' => 'integer'];

    /**
     * Handle isEnabled functionality with proper error handling.
     */
    public function isEnabled(?User $user = null): bool
    {
        if (! $this->is_active) {
            return false;
        }
        // Check environment
        if ($this->environment && $this->environment !== app()->environment()) {
            return false;
        }
        // Check date range
        $now = now();
        if ($this->starts_at && $now->isBefore($this->starts_at)) {
            return false;
        }
        if ($this->ends_at && $now->isAfter($this->ends_at)) {
            return false;
        }
        // Check rollout percentage
        if ($this->rollout_percentage) {
            $percentage = $this->rollout_percentage['percentage'] ?? 100;
            if ($percentage < 100) {
                $hash = hash('sha256', $this->key.($user?->id ?? session()->getId()));
                $userPercentile = hexdec(substr($hash, 0, 8)) / 0xFFFFFFFF * 100;
                if ($userPercentile > $percentage) {
                    return false;
                }
            }
        }
        // Check conditions
        if ($this->conditions && $user) {
            return $this->evaluateConditions($this->conditions, $user);
        }

        return true;
    }

    /**
     * Handle evaluateConditions functionality with proper error handling.
     */
    private function evaluateConditions(array $conditions, User $user): bool
    {
        foreach ($conditions as $condition) {
            $type = $condition['type'] ?? '';
            $value = $condition['value'] ?? null;
            switch ($type) {
                case 'user_id':
                    if (! in_array($user->id, (array) $value)) {
                        return false;
                    }
                    break;
                case 'user_email':
                    if (! in_array($user->email, (array) $value)) {
                        return false;
                    }
                    break;
                case 'user_role':
                    if (! $user->hasAnyRole((array) $value)) {
                        return false;
                    }
                    break;
                case 'user_group':
                    if (! $user->customerGroups()->whereIn('name', (array) $value)->exists()) {
                        return false;
                    }
                    break;
                default:
                    // Unknown condition type, fail safe
                    return false;
            }
        }

        return true;
    }

    /**
     * Handle isFeatureEnabled functionality with proper error handling.
     */
    public static function isFeatureEnabled(string $key, ?User $user = null): bool
    {
        $flag = self::where('key', $key)->first();

        return $flag?->isEnabled($user) ?? false;
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
     * Handle scopeEnabled functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Handle scopeDisabled functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeDisabled($query)
    {
        return $query->where('is_enabled', false);
    }

    /**
     * Handle scopeGlobal functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeGlobal($query)
    {
        return $query->where('is_global', true);
    }

    /**
     * Handle scopeByKey functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    /**
     * Handle scopeEnvironment functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeEnvironment($query, string $environment)
    {
        return $query->where('environment', $environment);
    }

    /**
     * Handle users functionality with proper error handling.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'feature_flag_users');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected function createdByDisplay(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->creator?->name ?? $this->created_by_name);
    }

    protected function updatedByDisplay(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->updater?->name ?? $this->updated_by_name);
    }
}
