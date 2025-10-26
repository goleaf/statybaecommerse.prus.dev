<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * OrderRefundRequest
 *
 * API request wrapper to validate refund metadata before the controller mutates the order state.
 */
final class OrderRefundRequest extends FormRequest
{
    /**
     * Policy checks happen in the controller, therefore this request always authorizes.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for the refund payload.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Keep the payload intentionally small so finance automation can rely on structured, predictable data.
        return [
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'reason' => ['sometimes', 'nullable', 'string', 'max:500'],
            'notes'  => ['sometimes', 'nullable', 'string'],
        ];
    }
}
