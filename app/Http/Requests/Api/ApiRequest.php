<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;

abstract class ApiRequest extends FormRequest
{
    /**
     * The Sanctum ability required to authorize the request.
     */
    protected ?string $requiredAbility = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        $ability = $this->requiredAbility;
        $token = $user->currentAccessToken();

        if ($ability === null || $token === null) {
            return true;
        }

        return $token->can($ability);
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization(): void
    {
        $message = $this->requiredAbility
            ? sprintf('This action requires the [%s] ability.', $this->requiredAbility)
            : 'You are not authorized to perform this action.';

        throw new AuthorizationException($message);
    }

    /**
     * Expose the required ability for documentation purposes.
     */
    public function requiredAbility(): ?string
    {
        return $this->requiredAbility;
    }
}
