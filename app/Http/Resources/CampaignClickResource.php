<?php declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignClickResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'campaign_id' => $this->campaign_id,
            'campaign' => [
                'id' => $this->campaign->id ?? null,
                'name' => $this->campaign->name ?? null,
                'slug' => $this->campaign->slug ?? null,
            ],
            'customer_id' => $this->customer_id,
            'customer' => [
                'id' => $this->customer->id ?? null,
                'name' => $this->customer->name ?? null,
                'email' => $this->customer->email ?? null,
            ],
            'session_id' => $this->session_id,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'click_type' => $this->click_type,
            'click_type_label' => $this->click_type_label,
            'clicked_url' => $this->clicked_url,
            'clicked_at' => $this->clicked_at?->toISOString(),
            'referer' => $this->referer,
            'device_type' => $this->device_type,
            'device_type_label' => $this->device_type_label,
            'browser' => $this->browser,
            'browser_label' => $this->browser_label,
            'os' => $this->os,
            'os_label' => $this->os_label,
            'country' => $this->country,
            'city' => $this->city,
            'utm_source' => $this->utm_source,
            'utm_medium' => $this->utm_medium,
            'utm_campaign' => $this->utm_campaign,
            'utm_term' => $this->utm_term,
            'utm_content' => $this->utm_content,
            'conversion_value' => $this->conversion_value,
            'is_converted' => $this->is_converted,
            'conversion_data' => $this->conversion_data,
            'conversions_count' => $this->whenLoaded('conversions', fn() => $this->conversions->count()),
            'total_conversion_value' => $this->getTotalConversionValue(),
            'conversion_rate' => $this->getConversionRate(),
            'utm_params' => $this->getUtmParams(),
            'location_info' => $this->getLocationInfo(),
            'device_info' => $this->getDeviceInfo(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
