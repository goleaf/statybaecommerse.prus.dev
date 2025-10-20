<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

final class NotificationSearchRequest extends ApiRequest
{
    protected ?string $requiredAbility = 'notifications.read';

    public function rules(): array
    {
        return [
            'q' => ['required', 'string'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'type' => ['sometimes', 'string'],
            'read' => ['sometimes', 'boolean'],
        ];
    }
}
