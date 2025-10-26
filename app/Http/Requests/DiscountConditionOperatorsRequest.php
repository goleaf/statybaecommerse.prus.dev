<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\DiscountCondition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * DiscountConditionOperatorsRequest
 *
 * Small helper request for validating the optional type filter when
 * retrieving the operator list.
 */
final class DiscountConditionOperatorsRequest extends FormRequest
{
    /**
     * Authorize the requesting user before the controller handles it.
     */
    public function authorize(): bool
    {
        // Delegate the actual authorization decision to the controller so the
        // behaviour stays consistent across HTML and JSON contexts.
        return true;
    }

    /**
     * Define the validation rules for the request payload.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            // When present, constrain the type to the supported discriminators.
            'type' => ['sometimes', 'string', Rule::in(array_keys(DiscountCondition::getTypes()))],
        ];
    }
}
