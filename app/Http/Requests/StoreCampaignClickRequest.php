<?php declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignClickRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Allow both authenticated and guest users
    }

    public function rules(): array
    {
        return [
            'campaign_id' => ['required', 'integer', 'exists:discount_campaigns,id'],
            'session_id' => ['nullable', 'string', 'max:255'],
            'ip_address' => ['nullable', 'ip', 'max:45'],
            'user_agent' => ['nullable', 'string', 'max:500'],
            'click_type' => ['required', 'string', 'in:cta,banner,link,button,image'],
            'clicked_url' => ['nullable', 'url', 'max:500'],
            'customer_id' => ['nullable', 'integer', 'exists:users,id'],
            'clicked_at' => ['nullable', 'date'],
            'referer' => ['nullable', 'url', 'max:500'],
            'device_type' => ['nullable', 'string', 'in:desktop,mobile,tablet'],
            'browser' => ['nullable', 'string', 'max:100'],
            'os' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'utm_source' => ['nullable', 'string', 'max:100'],
            'utm_medium' => ['nullable', 'string', 'max:100'],
            'utm_campaign' => ['nullable', 'string', 'max:100'],
            'utm_term' => ['nullable', 'string', 'max:100'],
            'utm_content' => ['nullable', 'string', 'max:100'],
            'conversion_value' => ['nullable', 'numeric', 'min:0'],
            'is_converted' => ['nullable', 'boolean'],
            'conversion_data' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'campaign_id.required' => __('campaign_clicks.validation.campaign_required'),
            'campaign_id.exists' => __('campaign_clicks.validation.campaign_exists'),
            'click_type.required' => __('campaign_clicks.validation.click_type_required'),
            'click_type.in' => __('campaign_clicks.validation.click_type_invalid'),
            'ip_address.ip' => __('campaign_clicks.validation.ip_invalid'),
            'clicked_url.url' => __('campaign_clicks.validation.url_invalid'),
            'customer_id.exists' => __('campaign_clicks.validation.customer_exists'),
            'device_type.in' => __('campaign_clicks.validation.device_type_invalid'),
            'conversion_value.numeric' => __('campaign_clicks.validation.conversion_value_numeric'),
            'conversion_value.min' => __('campaign_clicks.validation.conversion_value_min'),
        ];
    }

    protected function prepareForValidation(): void
    {
        // Set default values
        $this->merge([
            'clicked_at' => $this->clicked_at ?? now(),
            'ip_address' => $this->ip_address ?? request()->ip(),
            'user_agent' => $this->user_agent ?? request()->userAgent(),
            'session_id' => $this->session_id ?? session()->getId(),
        ]);

        // If user is authenticated, set customer_id
        if (auth()->check() && !$this->has('customer_id')) {
            $this->merge(['customer_id' => auth()->id()]);
        }
    }
}
