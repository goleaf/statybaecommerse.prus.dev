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
     * React to field updates so Livewire can run validateOnly on the nested form state.
     */
    public function updated(string $property): void
    {
        if (! str_starts_with($property, 'registrationForm.')) {
            // Ignore updates unrelated to the registration form payload.
            return;
        }

        $parts = explode('.', $property, 2);
        $field = $parts[1] ?? null;

        if ($field === null || $field === '') {
            // Bail out when Livewire reports an unexpected property structure.
            return;
        }

        if ($field === 'email') {
            // Email validation is handled via the debounced keyup hook for a snappier UX.
            return;
        }

        $this->registrationForm->validateField($field);
    }

    /**
     * Validate the email field on demand so unique checks can surface quickly.
     */
    public function validateEmailField(): void
    {
        $this->registrationForm->validateField('email');
    }

    public function render(): View
    {
        return view('livewire.auth.register');
    }
}
