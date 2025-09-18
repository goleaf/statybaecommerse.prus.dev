<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Livewire\Forms\RegistrationForm;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.templates.app')]
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

    public function render(): View
    {
        return view('livewire.auth.register');
    }
}
