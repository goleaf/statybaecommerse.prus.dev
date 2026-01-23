<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Campaign;
use Illuminate\Support\Collection;

/**
 * Service for handling legacy campaign analytics after click tracking removal.
 *
 * This service provides backward compatibility for existing code that expects
 * campaign analytics data, returning safe default values.
 */
final class LegacyCampaignAnalyticsService
{
    /**
     * Get legacy analytics data for a campaign.
     * Returns safe defaults since click tracking has been removed.
     */
    public function getCampaignAnalytics(Campaign $campaign): array
    {
        return [
            'total_views'        => $campaign->views()->count(),
            'total_clicks'       => 0, // Removed functionality
            'total_conversions'  => 0, // Removed functionality
            'click_through_rate' => 0.0,
            'conversion_rate'    => 0.0,
        ];
    }

    /**
     * Get campaign statistics for multiple campaigns.
     */
    public function getBulkCampaignStats(Collection $campaigns): array
    {
        return $campaigns->map(function (Campaign $campaign) {
            return [
                'id'        => $campaign->id,
                'name'      => $campaign->name,
                'analytics' => $this->getCampaignAnalytics($campaign),
            ];
        })->toArray();
    }

    /**
     * Handle legacy click tracking requests.
     * Returns success response without actually tracking anything.
     */
    public function handleLegacyClickTracking(array $data): array
    {
        // Log the attempt for monitoring purposes
        logger()->info('Legacy campaign click tracking attempt', [
            'campaign_id' => $data['campaign_id'] ?? null,
            'click_type'  => $data['click_type'] ?? null,
            'url'         => $data['url'] ?? null,
        ]);

        return [
            'success' => true,
            'message' => 'Click tracking has been deprecated',
            'tracked' => false,
        ];
    }
}
