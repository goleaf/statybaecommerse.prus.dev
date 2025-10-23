<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateCampaignConversionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'campaign_id' => ['required', 'exists:discount_campaigns,id'],
            'conversion_type' => ['required', 'string', 'max:255'],
            'conversion_value' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'max:255'],
            'converted_at' => ['required', 'date'],
            'source' => ['nullable', 'string', 'max:255'],
            'medium' => ['nullable', 'string', 'max:255'],
            'device_type' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'tags' => ['nullable', 'array'],
            'custom_attributes' => ['nullable', 'array'],
        ];
    }
}

