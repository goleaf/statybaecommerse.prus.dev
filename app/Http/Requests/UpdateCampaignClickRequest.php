<?php declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCampaignClickRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Allow updates only for authenticated users and only for their own clicks
        if (!auth()->check()) {
            return false;
        }

        $campaignClick = $this->route('campaignClick');
        return $campaignClick && $campaignClick->customer_id === auth()->id();
    }

    public function rules(): array
    {
        return [
            'campaign_id' => ['sometimes', 'integer', 'exists:discount_campaigns,id'],
            'session_id' => ['sometimes', 'string', 'max:255'],
            'ip_address' => ['sometimes', 'ip', 'max:45'],
            'user_agent' => ['sometimes', 'string', 'max:500'],
            'click_type' => ['sometimes', 'string', 'in:cta,banner,link,button,image'],
            'clicked_url' => ['sometimes', 'url', 'max:500'],
            'customer_id' => ['sometimes', 'integer', 'exists:users,id'],
            'clicked_at' => ['sometimes', 'date'],
            'referer' => ['sometimes', 'url', 'max:500'],
            'device_type' => ['sometimes', 'string', 'in:desktop,mobile,tablet'],
            'browser' => ['sometimes', 'string', 'max:100'],
            'os' => ['sometimes', 'string', 'max:100'],
            'country' => ['sometimes', 'string', 'max:100'],
            'city' => ['sometimes', 'string', 'max:100'],
            'utm_source' => ['sometimes', 'string', 'max:100'],
            'utm_medium' => ['sometimes', 'string', 'max:100'],
            'utm_campaign' => ['sometimes', 'string', 'max:100'],
            'utm_term' => ['sometimes', 'string', 'max:100'],
            'utm_content' => ['sometimes', 'string', 'max:100'],
            'conversion_value' => ['sometimes', 'numeric', 'min:0'],
            'is_converted' => ['sometimes', 'boolean'],
            'conversion_data' => ['sometimes', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'campaign_id.exists' => __('campaign_clicks.validation.campaign_exists'),
            'click_type.in' => __('campaign_clicks.validation.click_type_invalid'),
            'ip_address.ip' => __('campaign_clicks.validation.ip_invalid'),
            'clicked_url.url' => __('campaign_clicks.validation.url_invalid'),
            'customer_id.exists' => __('campaign_clicks.validation.customer_exists'),
            'device_type.in' => __('campaign_clicks.validation.device_type_invalid'),
            'conversion_value.numeric' => __('campaign_clicks.validation.conversion_value_numeric'),
            'conversion_value.min' => __('campaign_clicks.validation.conversion_value_min'),
        ];
    }
}
