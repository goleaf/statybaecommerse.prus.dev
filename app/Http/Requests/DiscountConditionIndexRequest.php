<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\DiscountCondition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * DiscountConditionIndexRequest
 *
 * Dedicated request object responsible for validating the filters that power
 * the discount condition listing endpoint.
 */
final class DiscountConditionIndexRequest extends FormRequest
{
    /**
     * Authorize the requesting user before hitting the controller action.
     */
    public function authorize(): bool
    {
        // Let the controller layer throw the 403 when necessary so browser
        // redirects render the authorization error consistently.
        return true;
    }

    /**
     * Provide the validation rules for the incoming request payload.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $allowedTypes = array_keys(DiscountCondition::getTypes());
        $allowedOperators = array_keys(DiscountCondition::getOperators());

        return [
            // Users can optionally filter by the discriminator key.
            'type' => ['sometimes', 'string', Rule::in($allowedTypes)],
            // Support narrowing by a concrete discount relationship.
            'discount_id' => ['sometimes', 'integer', 'exists:discounts,id'],
            // Allow matching a specific comparison operator when browsing.
            'operator' => ['sometimes', 'string', Rule::in($allowedOperators)],
            // Keep pagination under control to avoid accidental large payloads.
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            // Restrict sorting to trusted columns to mitigate SQL injection.
            'sort' => ['sometimes', 'string', Rule::in(['priority', 'created_at'])],
            // Limit direction to ASC/DESC semantics understood by the database.
            'direction' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
        ];
    }
}
