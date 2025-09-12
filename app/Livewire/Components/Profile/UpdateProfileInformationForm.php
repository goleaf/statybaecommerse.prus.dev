<?php

declare(strict_types=1);

namespace App\Livewire\Components\Profile;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class UpdateProfileInformationForm extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|email|max:255')]
    public string $email = '';

    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    public function updateProfileInformation(): void
    {
        $this->validate();

        Auth::user()->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        $this->dispatch('profile-updated');
        session()->flash('status', __('Profile updated successfully.'));
    }

    public function render(): View
    {
        return view('livewire.components.profile.update-profile-information-form');
    }
}
