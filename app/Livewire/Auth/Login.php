<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Livewire\Forms\LoginForm;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.templates.app')]
final class Login extends Component
{
    public LoginForm $loginForm;

    public function mount(): void
    {
        $this->loginForm->reset();
    }

    public function login(): void
    {
        $this->loginForm->authenticate();
        Session::regenerate();

        $this->redirectIntended(default: route('account', absolute: false), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.login');
    }
}
