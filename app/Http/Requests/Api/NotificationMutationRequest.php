<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

final class NotificationMutationRequest extends ApiRequest
{
    protected ?string $requiredAbility = 'notifications.manage';

    public function rules(): array
    {
        return [];
    }
}
