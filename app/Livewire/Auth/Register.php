<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Livewire\Forms\RegistrationForm;
use App\Services\Cart\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
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
        $previousSessionId = (string) Session::getId();
        $user = $this->registrationForm->register();

        app(CartService::class)->claimSessionCartForUser(
            userId: (int) $user->getAuthIdentifier(),
            previousSessionId: $previousSessionId,
            currentSessionId: (string) Session::getId(),
        );

        $this->redirect(route('account.index', absolute: false), navigate: true);
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
