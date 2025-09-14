<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Livewire\Forms\LoginForm;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.templates.app')]
final /**
 * Login
 * 
 * Livewire component for reactive frontend functionality.
 */
class Login extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    // Intentionally do not declare a `$form` property to allow Filament Schemas to manage it dynamically.

    public LoginForm $loginForm;

    public function mount(): void
    {
        $this->loginForm->reset();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label(__('E-mail'))
                    ->email()
                    ->required()
                    ->autocomplete('email')
                    ->autofocus(),

                TextInput::make('password')
                    ->label(__('Password'))
                    ->password()
                    ->required()
                    ->autocomplete('current-password')
                    ->revealable(),

                Checkbox::make('remember')
                    ->label(__('Remember me')),
            ])
            ->statePath('form');
    }

    public function login(): void
    {
        // Validate Filament schema fields
        $this->validate();

        // Sync schema state to Livewire Form object
        $this->loginForm->email = (string) ($this->form['email'] ?? '');
        $this->loginForm->password = (string) ($this->form['password'] ?? '');
        $this->loginForm->remember = (bool) ($this->form['remember'] ?? false);

        $this->loginForm->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('account', absolute: false), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.login');
    }

    // Let Filament's InteractsWithSchemas resolve the dynamic `form` schema.
}
