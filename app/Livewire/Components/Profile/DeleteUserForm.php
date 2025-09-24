<?php

declare(strict_types=1);

namespace App\Livewire\Components\Profile;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * DeleteUserForm
 *
 * Livewire component for DeleteUserForm with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property string $password
 * @property bool $confirmUserDeletion
 */
final class DeleteUserForm extends Component
{
    #[Validate('required|string|current_password')]
    public string $password = '';

    public bool $confirmUserDeletion = false;

    /**
     * Handle confirmUserDeletion functionality with proper error handling.
     */
    public function confirmUserDeletion(): void
    {
        $this->resetErrorBag('password');
        $this->password = '';
        $this->confirmUserDeletion = true;
    }

    /**
     * Handle deleteUser functionality with proper error handling.
     */
    public function deleteUser(): void
    {
        $this->validate();
        $user = Auth::user();
        Auth::logout();
        $user->delete();
        session()->invalidate();
        session()->regenerateToken();
        $this->redirect('/');
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.components.profile.delete-user-form');
    }
}
