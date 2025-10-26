<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Livewire\Forms\LoginForm;
use App\Support\Security\Captcha\CaptchaManager;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.base')]
final class Login extends Component
{
    public LoginForm $loginForm;

    public function mount(CaptchaManager $captchaManager): void
    {
        $this->loginForm->reset();
        $this->loginForm->syncCaptchaState($captchaManager);
    }

    public function login(): void
    {
        $result = $this->loginForm->authenticate();

        if ($result->requiresTwoFactorChallenge()) {
            // Defer to the dedicated two-factor challenge component to finish authentication.
            $this->redirectRoute('two-factor.challenge');

            return;
        }

        Session::regenerate();

        $this->redirectIntended(default: route('account', absolute: false), navigate: true);
    }

    public function hydrate(CaptchaManager $captchaManager): void
    {
        $this->loginForm->syncCaptchaState($captchaManager);
    }

    public function refreshCaptcha(CaptchaManager $captchaManager): void
    {
        $this->loginForm->syncCaptchaState($captchaManager, true);
    }

    public function render(): View
    {
        return view('livewire.auth.login');
    }
}
