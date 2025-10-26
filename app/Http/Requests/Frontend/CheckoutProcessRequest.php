<?php

declare(strict_types=1);

namespace App\Http\Requests\Frontend;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;

final class CheckoutProcessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name'      => $this->contactRequirement(['string', 'max:255']),
            'email'          => $this->contactRequirement(['string', $this->emailValidationRule(), 'max:255']),
            'phone'          => ['nullable', 'string', 'max:50'],
            'address_line_1' => $this->contactRequirement(['string', 'max:255']),
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city'           => $this->contactRequirement(['string', 'max:255']),
            'postal_code'    => $this->contactRequirement(['string', 'max:25']),
            'country'        => $this->contactRequirement(['string', 'max:120']),
            // Require clients to submit their calculated totals so we can cross-check them server-side.
            'totals'          => $this->totalsRequirement(['array']),
            'totals.subtotal' => ['nullable', 'numeric', 'min:0'],
            'totals.tax'      => ['nullable', 'numeric', 'min:0'],
            'totals.shipping' => ['nullable', 'numeric', 'min:0'],
            'totals.discount' => ['nullable', 'numeric', 'min:0'],
            'totals.total'    => ['nullable', 'numeric', 'min:0'],
            'totals.lines'    => ['nullable', 'array'],
            'totals.lines.*'  => ['numeric', 'min:0'],
            'payment_method'  => [
                'required',
                'string',
                'max:50',
                Rule::in([
                    'card',
                    'bank_transfer',
                    'cod',
                    PaymentMethod::CREDIT_CARD->value,
                    PaymentMethod::BANK_TRANSFER->value,
                    PaymentMethod::CASH_ON_DELIVERY->value,
                ]),
            ],
            'notes'   => ['nullable', 'string', 'max:2000'],
            'confirm' => ['sometimes', 'accepted'],
        ];
    }

    /**
     * Ensure downstream code works with canonical payment method values.
     *
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null)
    {
        /** @var array<string, mixed> $validated */
        $validated = parent::validated($key, $default);

        if (isset($validated['payment_method']) && is_string($validated['payment_method'])) {
            $validated['payment_method'] = $this->normalisePaymentMethod($validated['payment_method']);
        }

        return $validated;
    }

    /**
     * @param  array<int, string> $rules
     * @return array<int, string>
     */
    private function contactRequirement(array $rules): array
    {
        $presenceRule = $this->isJsonCheckout() ? 'nullable' : 'required';

        return array_merge([$presenceRule], $rules);
    }

    /**
     * Decide whether the totals array must be present based on the request format.
     *
     * @param  array<int, string> $rules
     * @return array<int, string>
     */
    private function totalsRequirement(array $rules): array
    {
        $presenceRule = $this->isJsonCheckout() ? 'required' : 'sometimes';

        return array_merge([$presenceRule], $rules);
    }

    private function normalisePaymentMethod(string $value): string
    {
        return match ($value) {
            'card', PaymentMethod::CREDIT_CARD->value => PaymentMethod::CREDIT_CARD->value,
            'bank_transfer', PaymentMethod::BANK_TRANSFER->value => PaymentMethod::BANK_TRANSFER->value,
            'cod', 'cash_on_delivery', PaymentMethod::CASH_ON_DELIVERY->value => PaymentMethod::CASH_ON_DELIVERY->value,
            default => $value,
        };
    }

    private function emailValidationRule(): string
    {
        if (App::runningUnitTests() || config('app.env') === 'testing') {
            return 'email:rfc';
        }

        return 'email:rfc,dns';
    }

    private function isJsonCheckout(): bool
    {
        return $this->expectsJson() || $this->wantsJson();
    }
}
