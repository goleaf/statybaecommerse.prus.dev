<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Application\DTOs\Notifications\NotificationFilterData;
use Illuminate\Foundation\Http\FormRequest;

final class ListNotificationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'type' => ['sometimes', 'string', 'max:255'],
            'read' => ['sometimes', 'boolean'],
        ];
    }

    public function toDto(): NotificationFilterData
    {
        $validated = $this->validated();

        return new NotificationFilterData(
            perPage: (int) ($validated['per_page'] ?? 25),
            type: $validated['type'] ?? null,
            read: array_key_exists('read', $validated) ? (bool) $validated['read'] : null,
        );
    }
}
