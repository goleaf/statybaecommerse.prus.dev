<?php

declare(strict_types=1);

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserPrivacySettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'privacy_settings'   => ['nullable', 'array'],
            'privacy_settings.*' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
