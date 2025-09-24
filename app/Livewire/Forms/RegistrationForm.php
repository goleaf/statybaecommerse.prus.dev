<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

    #[Validate('required|string|confirmed')]
    public string $password = '';

    #[Validate('required|string')]
    public string $password_confirmation = '';

    /**
     * Handle rules functionality with proper error handling.
     */
    public function rules(): array
    {
        return ['first_name' => ['required', 'string', 'max:255'], 'last_name' => ['required', 'string', 'max:255'], 'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class], 'password' => ['required', 'string', 'confirmed', Password::defaults()]];
    }

    /**
     * Handle register functionality with proper error handling.
     */
    public function register(): User
    {
        $this->validate();
        $validated = $this->only(['first_name', 'last_name', 'email', 'password']);
        $validated['password'] = Hash::make($validated['password']);
        $validated['preferred_locale'] = app()->getLocale();
        $user = User::create($validated);
        event(new Registered($user));
        Auth::login($user);

        return $user;
    }
}
