<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

final class NotificationShowRequest extends ApiRequest
{
    protected ?string $requiredAbility = 'notifications.read';

    public function rules(): array
    {
        return [];
    }
}
