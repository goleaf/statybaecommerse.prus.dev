<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

final class ShowAuthenticatedUserRequest extends ApiRequest
{
    protected ?string $requiredAbility = 'profile.read';

    public function rules(): array
    {
        return [];
    }
}
