<?php

declare(strict_types=1);

namespace App\Livewire\Components\Profile;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class DeleteUserForm extends Component
{
    #[Validate('required|string|current_password')]
    public string $password = '';

    public bool $confirmUserDeletion = false;

    public function confirmUserDeletion(): void
    {
        $this->resetErrorBag('password');
        $this->password = '';
        $this->confirmUserDeletion = true;
    }

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

    public function render(): View
    {
        return view('livewire.components.profile.delete-user-form');
    }
}
