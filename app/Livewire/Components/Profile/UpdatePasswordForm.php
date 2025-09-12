<?php declare(strict_types=1);

namespace App\Livewire\Components\Profile;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class UpdatePasswordForm extends Component
{
    #[Validate('required|string|current_password')]
    public string $current_password = '';

    #[Validate('required|string|min:8|confirmed')]
    public string $password = '';

    #[Validate('required|string|min:8')]
    public string $password_confirmation = '';

    public function updatePassword(): void
    {
        $this->validate();

        Auth::user()->update([
            'password' => Hash::make($this->password),
        ]);

        $this->current_password = '';
        $this->password = '';
        $this->password_confirmation = '';

        $this->dispatch('password-updated');
        session()->flash('status', __('Password updated successfully.'));
    }

    public function render(): View
    {
        return view('livewire.components.profile.update-password-form');
    }
}
