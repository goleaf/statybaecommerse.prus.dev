<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Foundation\Http\FormRequest;

/**
 * OrderUpdateRequest
 *
 * API request object that guards which order attributes can be mutated through the lifecycle endpoints.
 */
final class OrderUpdateRequest extends FormRequest
{
    /**
     * Authorisation is handled within the controller via policy checks, so always allow the request here.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for the update endpoint.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Restrict incoming payloads to the whitelisted lifecycle attributes so we avoid accidental mass updates.
        return [
            'status'         => ['sometimes', 'string', 'in:' . implode(',', array_map(static fn (OrderStatus $status): string => $status->value, OrderStatus::cases()))],
            'payment_status' => ['sometimes', 'string', 'in:' . implode(',', array_map(static fn (PaymentStatus $status): string => $status->value, PaymentStatus::cases()))],
            'notes'          => ['sometimes', 'nullable', 'string'],
            'transactions'   => ['sometimes', 'array'],
            'transactions.*' => ['array'],
        ];
    }
}
