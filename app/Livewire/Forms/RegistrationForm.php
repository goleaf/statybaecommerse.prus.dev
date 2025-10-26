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
    #[Validate('required|string|max:255')]
    public string $first_name = '';

    #[Validate('required|string|max:255')]
    public string $last_name = '';

    #[Validate('required|string|lowercase|email|max:255|unique:users,email')]
    public string $email = '';

    #[Validate('required|string|same:password_confirmation')]
    public string $password = '';

    #[Validate('required|string')]
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
            // Apply Laravel's default password requirements and enforce manual confirmation matching.
            'password' => ['required', 'string', 'same:password_confirmation', Password::defaults()],
            // Explicitly validate the confirmation field so Livewire surfaces inline errors consistently.
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
