<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class FeatureFlag extends Model
{
    protected $fillable = [
        'name',
        'key',
        'description',
        'is_active',
        'conditions',
        'rollout_percentage',
        'environment',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'conditions' => 'json',
        'rollout_percentage' => 'json',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

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

    public static function isFeatureEnabled(string $key, ?User $user = null): bool
    {
        $flag = self::where('key', $key)->first();

        return $flag?->isEnabled($user) ?? false;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeEnvironment($query, string $environment)
    {
        return $query->where('environment', $environment);
    }
}
