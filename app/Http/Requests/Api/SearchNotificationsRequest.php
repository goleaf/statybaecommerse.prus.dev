<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Application\DTOs\Notifications\NotificationSearchData;

final class SearchNotificationsRequest extends ListNotificationsRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'q' => ['required', 'string', 'min:1'],
        ]);
    }

    public function toDto(): NotificationSearchData
    {
        $validated = $this->validated();

        return new NotificationSearchData(
            query: (string) $validated['q'],
            perPage: (int) ($validated['per_page'] ?? 25),
            type: $validated['type'] ?? null,
            read: array_key_exists('read', $validated) ? (bool) $validated['read'] : null,
        );
    }
}
