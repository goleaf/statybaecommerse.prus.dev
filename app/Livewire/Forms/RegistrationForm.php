<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
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
    private const FIRST_NAME_RULES = 'required|string|max:255';
    private const LAST_NAME_RULES = 'required|string|max:255';
    private const EMAIL_RULES = 'required|string|email:filter|max:255|lowercase|unique:users,email';
    private const PASSWORD_RULES = 'required|string|min:8|confirmed|same:password_confirmation';
    private const PASSWORD_LIVE_RULES = 'required|string|min:8';
    private const PASSWORD_CONFIRMATION_RULES = 'required|string|min:8';

    // Maintain the Livewire form field state so validation rules can be applied consistently.
    #[Validate(self::FIRST_NAME_RULES)]
    public string $first_name = '';

    // Keep the last name separate so UX can surface precise validation errors.
    #[Validate(self::LAST_NAME_RULES)]
    public string $last_name = '';

    // Store the email input prior to normalization so the lowercase rule can mutate it.
    #[Validate(self::EMAIL_RULES)]
    public string $email = '';

    // Hold the raw password until we hash it during registration.
    #[Validate(self::PASSWORD_RULES)]
    public string $password = '';

    // Persist the confirmation alongside the password so the confirmed rule can compare values.
    #[Validate(self::PASSWORD_CONFIRMATION_RULES)]
    public string $password_confirmation = '';

    /**
     * Validate a single field so Livewire's validateOnly lifecycle remains accurate.
     */
    public function validateField(string $property): void
    {
        $rule = match ($property) {
            'first_name' => self::FIRST_NAME_RULES,
            'last_name' => self::LAST_NAME_RULES,
            'email' => self::EMAIL_RULES,
            'password' => self::PASSWORD_LIVE_RULES,
            'password_confirmation' => self::PASSWORD_CONFIRMATION_RULES,
            default => null,
        };

        if ($rule === null) {
            // Skip validation attempts for properties that are not tracked on this form object.
            return;
        }

        if ($property === 'email') {
            $this->normalizeEmail();
        }

        $this->validateOnly($property, [
            $property => $rule,
        ]);
    }

    /**
     * Handle register functionality with proper error handling.
     */
    public function register(): User
    {
        // Normalize the email prior to validation so uppercase input passes lowercase checks.
        $this->normalizeEmail();

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

    /**
     * Convert the email input to lowercase to enforce consistent uniqueness checks.
     */
    private function normalizeEmail(): void
    {
        $email = trim($this->email);

        if ($email === '') {
            $this->email = '';

            return;
        }

        $this->email = Str::lower($email);
    }
}
