<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Config;

/**
 * Service for managing feature toggles and their dependencies
 */
class FeatureToggleService
{
    /**
     * Check if a feature is enabled
     */
    public function isEnabled(string $feature): bool
    {
        return (bool) Config::get("app-features.features.{$feature}", false);
    }

    /**
     * Get all enabled features
     */
    public function getEnabledFeatures(): array
    {
        return array_filter(
            Config::get('app-features.features', []),
            fn ($enabled) => $enabled === true
        );
    }

    /**
     * Check if campaigns feature is enabled (for backward compatibility)
     *
     * @deprecated Campaigns feature has been permanently removed
     */
    public function isCampaignsEnabled(): bool
    {
        return false; // Campaigns feature permanently disabled
    }

    /**
     * Get features that depend on campaigns
     */
    public function getCampaignDependentFeatures(): array
    {
        return [
            'analytics'       => $this->isEnabled('analytics'),
            'discount'        => $this->isEnabled('discount'),
            'customer_groups' => $this->isEnabled('customer_groups'),
        ];
    }
}
