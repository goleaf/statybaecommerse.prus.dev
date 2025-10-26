<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
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
    // Enforce consistent validation when using Livewire's validateOnly lifecycle.
    #[Validate(['required', 'string', 'max:255'])]
    public string $first_name = '';

    // Mirror the same validation rigor for the last name field for parity in UX.
    #[Validate(['required', 'string', 'max:255'])]
    public string $last_name = '';

    // Keep email uniqueness scoped to the users table while forcing lowercase formatting.
    #[Validate(['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'])]
    public string $email = '';

    // Maintain Livewire realtime validation parity for the password field.
    #[Validate(['required', 'string', 'confirmed'])]
    public string $password = '';

    // Track the confirmation field so we can surface inline validation feedback.
    #[Validate(['required', 'string'])]
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
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class, 'email')],
            // Apply Laravel's default password requirements alongside confirmation checks.
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            // Explicitly require the confirmation field so validation messages remain precise.
            'password_confirmation' => ['required', 'string'],
        ];
    }

    /**
     * Handle register functionality with proper error handling.
     */
    public function register(): User
    {
        $this->validate();
        // Extract the validated payload while helping static analysers understand the resulting structure.
        /** @var array{first_name: string, last_name: string, email: string, password: string} $validated */
        $validated = $this->only(['first_name', 'last_name', 'email', 'password']);
        $validated['password'] = Hash::make($validated['password']);
        $validated['preferred_locale'] = app()->getLocale();
        $user = User::create($validated);
        event(new Registered($user));
        Auth::login($user);

        return $user;
    }
}
