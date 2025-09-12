<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Livewire\Forms\RegistrationForm;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.templates.app')]
final class Register extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public RegistrationForm $registrationForm;

    public function mount(): void
    {
        $this->registrationForm->reset();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('form.last_name')
                    ->label(__('Lastname'))
                    ->required()
                    ->maxLength(255)
                    ->autocomplete('family-name')
                    ->autofocus(),

                TextInput::make('form.first_name')
                    ->label(__('Firstname'))
                    ->required()
                    ->maxLength(255)
                    ->autocomplete('given-name'),

                TextInput::make('form.email')
                    ->label(__('E-mail Address'))
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique('users', 'email')
                    ->autocomplete('email'),

                TextInput::make('form.password')
                    ->label(__('Password'))
                    ->password()
                    ->required()
                    ->autocomplete('new-password')
                    ->revealable(),

                TextInput::make('form.password_confirmation')
                    ->label(__('Confirm Password'))
                    ->password()
                    ->required()
                    ->same('form.password')
                    ->autocomplete('new-password')
                    ->revealable(),
            ])
            ->statePath('form');
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
