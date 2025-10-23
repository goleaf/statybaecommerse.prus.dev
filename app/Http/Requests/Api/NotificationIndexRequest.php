<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

final class NotificationIndexRequest extends ApiRequest
{
    protected ?string $requiredAbility = 'notifications.read';

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'type' => ['sometimes', 'string'],
            'read' => ['sometimes', 'boolean'],
        ];
    }
}
