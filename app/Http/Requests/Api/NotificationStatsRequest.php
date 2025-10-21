<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

final class NotificationStatsRequest extends ApiRequest
{
    protected ?string $requiredAbility = 'notifications.read';

    public function rules(): array
    {
        return [];
    }
}
