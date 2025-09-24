<?php

declare(strict_types=1);

namespace App\Livewire\Components\Profile;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * UpdateProfileInformationForm
 *
 * Livewire component for UpdateProfileInformationForm with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property string $name
 * @property string $email
 */
final class UpdateProfileInformationForm extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|email|max:255')]
    public string $email = '';

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Handle updateProfileInformation functionality with proper error handling.
     */
    public function updateProfileInformation(): void
    {
        $this->validate();
        Auth::user()->update(['name' => $this->name, 'email' => $this->email]);
        $this->dispatch('profile-updated');
        session()->flash('status', __('Profile updated successfully.'));
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.components.profile.update-profile-information-form');
    }
}
