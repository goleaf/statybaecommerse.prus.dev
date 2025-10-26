<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Livewire\Forms\RegistrationForm;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.base')]
final class Register extends Component
{
    public RegistrationForm $registrationForm;

    public function mount(): void
    {
        $this->registrationForm->reset();
    }

    public function register(): void
    {
        $this->registrationForm->register();

        $this->redirect(route('account', absolute: false), navigate: true);
    }

    /**
     * React to property updates so real-time validation mirrors the frontend UX expectations.
     */
    public function updated(string $propertyName, mixed $value = null): void
    {
        // Only proxy validation for the nested registration form fields to avoid unnecessary work.
        if (! str_starts_with($propertyName, 'registrationForm.')) {
            return;
        }

        // Delegate to Livewire so only the targeted nested field is validated during the interaction cycle.
        $this->validateOnly($propertyName);
    }

    public function render(): View
    {
        return view('livewire.auth.register');
    }
}
