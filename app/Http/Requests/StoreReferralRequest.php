<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReferralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Ensure the payload uses a clean, normalized email value before validation.
     */
    protected function prepareForValidation(): void
    {
        // Trim whitespace so uniqueness and format checks behave predictably.
        $this->merge([
            'referred_email' => $this->sanitizeEmail($this->input('referred_email')),
        ]);
    }

    public function rules(): array
    {
        return [
            // Ensure the referred email belongs to an existing account so the
            // downstream controller can safely attach the relationship.
            'referred_email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'exists:users,email'],
            // Allow the referrer to include a short optional note.
            'message' => ['nullable', 'string', 'max:500'],
            // Accept optional marketing copy so referrers can personalise
            // the invitation that accompanies the referral.
            'title'       => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Normalize emails by trimming and lower-casing while tolerating null inputs.
     */
    private function sanitizeEmail(?string $value): ?string
    {
        // Casting to string would turn null into an empty string, so handle manually.
        if ($value === null) {
            return null;
        }

        return mb_strtolower(trim($value));
    }
}
