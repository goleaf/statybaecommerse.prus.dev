<?php

declare(strict_types=1);

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

final class ReferralCodeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'array'],
            'title.lt' => ['required', 'string', 'max:255'],
            'title.en' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.lt' => ['nullable', 'string'],
            'description.en' => ['nullable', 'string'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'reward_amount' => ['nullable', 'numeric', 'min:0'],
            'reward_type' => ['nullable', 'in:percentage,fixed,points'],
            'campaign_id' => ['nullable', 'exists:referral_campaigns,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}

