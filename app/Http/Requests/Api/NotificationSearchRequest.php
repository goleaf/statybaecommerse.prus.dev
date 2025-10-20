<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

final class NotificationSearchRequest extends ApiRequest
{
    protected ?string $requiredAbility = 'notifications.read';

    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'min:2'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'type' => ['sometimes', 'string'],
            'read' => ['sometimes', 'boolean'],
            'sort' => ['sometimes', 'in:created_at,type'],
            'direction' => ['sometimes', 'in:asc,desc'],
        ];
    }
}
