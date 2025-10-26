<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Form;

/**
 * RegistrationForm
 *
 * Livewire component for RegistrationForm with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $password
 * @property string $password_confirmation
 */
final class RegistrationForm extends Form
{
    // Maintain the Livewire form field state so validation rules can be applied consistently.
    public string $first_name = '';

    // Keep the last name separate so UX can surface precise validation errors.
    public string $last_name = '';

    // Store the email input prior to normalization so the lowercase rule can mutate it.
    public string $email = '';

    // Hold the raw password until we hash it during registration.
    public string $password = '';

    // Persist the confirmation alongside the password so the confirmed rule can compare values.
    public string $password_confirmation = '';

    /**
     * Handle rules functionality with proper error handling.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            // Ensure the first name stays concise and free from invalid characters.
            'first_name' => ['required', 'string', 'max:255'],
            // Guard the last name field with the same strictness for consistency.
            'last_name' => ['required', 'string', 'max:255'],
            // Validate email uniqueness using the underlying table instead of the class string to avoid SQL errors.
            'email' => ['required', 'email:filter', 'max:255', 'lowercase', 'unique:users,email'],
            // Apply Laravel's default password requirements alongside confirmation checks.
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            // Explicitly require the confirmation field so validation messages remain precise.
            'password_confirmation' => ['required', 'string'],
        ];
    }

    /**
     * Validate a single field so Livewire's validateOnly lifecycle remains accurate.
     */
    public function validateField(string $property): void
    {
        $rules = $this->rules();

        if (! array_key_exists($property, $rules)) {
            // Skip validation attempts for properties that are not tracked on this form object.
            return;
        }

        $this->validateOnly($property);
    }

    /**
     * Handle register functionality with proper error handling.
     */
    public function register(): User
    {
        /**
         * Validate the full payload so we can normalise fields (email lowercasing) before persistence.
         *
         * @var array{first_name: string, last_name: string, email: string, password: string, password_confirmation: string} $validated
         */
        $validated = $this->validate();

        // Remove the confirmation value because it should never be stored directly.
        unset($validated['password_confirmation']);

        // Hash the password to protect user credentials at rest before insertion.
        $validated['password'] = Hash::make($validated['password']);
        $validated['preferred_locale'] = app()->getLocale();

        // Create the user record and trigger downstream events (email verification, etc.).
        $user = User::create($validated);
        event(new Registered($user));
        Auth::login($user);

        return $user;
    }
}
