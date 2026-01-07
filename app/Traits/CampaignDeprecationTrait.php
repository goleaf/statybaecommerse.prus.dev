<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

/**
 * Trait for handling deprecated campaign functionality
 */
trait CampaignDeprecationTrait
{
    /**
     * Scope to exclude campaign-related data when feature is disabled
     */
    public function scopeWithoutCampaignData(Builder $query): Builder
    {
        Log::warning('Campaign functionality is deprecated and will be removed', [
            'model'  => static::class,
            'method' => 'scopeWithoutCampaignData',
        ]);

        return $query;
    }

    /**
     * Check if campaigns feature is available
     */
    public function isCampaignFeatureAvailable(): bool
    {
        return false; // Feature removed
    }

    /**
     * Get campaign-related attributes with deprecation warning
     */
    public function getCampaignAttributes(): array
    {
        Log::warning('Accessing deprecated campaign attributes', [
            'model' => static::class,
            'id'    => $this->id ?? null,
        ]);

        return [];
    }
}
