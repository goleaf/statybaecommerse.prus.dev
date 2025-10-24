<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Support\ApiErrorResponse;
use App\Support\ErrorCodes;
use App\Support\RequestContext;
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
        $reason = $this->requiredAbility
            ? sprintf('This action requires the [%s] ability.', $this->requiredAbility)
            : 'You are not authorized to perform this action.';

        $locale = RequestContext::resolveLocale($this);
        $detail = ErrorCodes::message(ErrorCodes::FORBIDDEN, $locale)
            ?? 'You do not have permission to perform this action.';

        $response = ApiErrorResponse::problem(
            request: $this,
            errorCode: ErrorCodes::FORBIDDEN,
            detail: $detail,
            status: 403,
            title: ApiErrorResponse::titleFor(ErrorCodes::FORBIDDEN, $locale),
            context: ['reason' => $reason],
            locale: $locale,
        );

        throw new HttpResponseException($response);
    }

    /**
     * Expose the required ability for documentation purposes.
     */
    public function requiredAbility(): ?string
    {
        return $this->requiredAbility;
    }

    /**
     * Customize validation failures to include both legacy and problem+json metadata.
     */
    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors()->toArray();
        $violations = collect($errors)
            ->map(static function (array $messages, string $field): array {
                $localizedMessages = array_values($messages);

                return [
                    'field'    => $field,
                    'messages' => $localizedMessages,
                    'reason'   => $localizedMessages[0] ?? 'Invalid value.',
                ];
            })
            ->values()
            ->all();

        $locale = RequestContext::resolveLocale($this);
        $detail = $validator->errors()->first()
            ?? (ErrorCodes::message(ErrorCodes::VALIDATION_FAILED, $locale) ?? __('The given data was invalid.'));

        $response = ApiErrorResponse::problem(
            request: $this,
            errorCode: ErrorCodes::VALIDATION_FAILED,
            detail: $detail,
            status: 422,
            title: ApiErrorResponse::titleFor(ErrorCodes::VALIDATION_FAILED, $locale),
            context: ['violations' => $violations],
            locale: $locale,
        );

        $payload = $response->getData(true);
        $payload['message'] = $detail;
        $payload['errors'] = $errors;

        $response->setData($payload);

        throw new HttpResponseException($response);
    }
}
