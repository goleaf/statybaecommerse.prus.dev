<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

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
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $this->requiredAbility
                ? sprintf('This action requires the [%s] ability.', $this->requiredAbility)
                : 'You are not authorized to perform this action.',
        ], 403));
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'The given data was invalid.',
            'errors' => $validator->errors()->toArray(),
        ], 422));
    }

    /**
     * Expose the required ability for documentation purposes.
     */
    public function requiredAbility(): ?string
    {
        return $this->requiredAbility;
    }
}
