<?php

declare(strict_types=1);

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserNotificationPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'notification_preferences'   => ['nullable', 'array'],
            'notification_preferences.*' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
