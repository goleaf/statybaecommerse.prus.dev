<?php

declare (strict_types=1);
namespace App\Livewire\Auth;

use App\Livewire\Forms\RegistrationForm;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
/**
 * Register
 * 
 * Livewire component for Register with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property RegistrationForm $registrationForm
 */
#[Layout('components.layouts.templates.app')]
final class Register extends Component implements HasSchemas
{
    use InteractsWithSchemas;
    public RegistrationForm $registrationForm;
    /**
     * Initialize the Livewire component with parameters.
     * @return void
     */
    public function mount(): void
    {
        $this->registrationForm->reset();
    }
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('first_name')
                ->label(__('Firstname'))
                ->required()
                ->maxLength(255)
                ->autocomplete('given-name')
                ->autofocus()
                ->extraInputAttributes(['class' => 'rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500/20 transition-all duration-200'])
                ->prefixIcon('heroicon-o-user')
                ->placeholder(__('Enter your first name')),
            
            TextInput::make('last_name')
                ->label(__('Lastname'))
                ->required()
                ->maxLength(255)
                ->autocomplete('family-name')
                ->extraInputAttributes(['class' => 'rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500/20 transition-all duration-200'])
                ->prefixIcon('heroicon-o-user')
                ->placeholder(__('Enter your last name')),
            
            TextInput::make('email')
                ->label(__('E-mail Address'))
                ->email()
                ->required()
                ->maxLength(255)
                ->unique('users', 'email')
                ->autocomplete('email')
                ->extraInputAttributes(['class' => 'rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500/20 transition-all duration-200'])
                ->prefixIcon('heroicon-o-envelope')
                ->placeholder(__('Enter your email address')),
            
            TextInput::make('password')
                ->label(__('Password'))
                ->password()
                ->required()
                ->autocomplete('new-password')
                ->revealable()
                ->extraInputAttributes(['class' => 'rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500/20 transition-all duration-200'])
                ->prefixIcon('heroicon-o-lock-closed')
                ->placeholder(__('Create a strong password')),
            
            TextInput::make('password_confirmation')
                ->label(__('Confirm Password'))
                ->password()
                ->required()
                ->same('password')
                ->autocomplete('new-password')
                ->revealable()
                ->extraInputAttributes(['class' => 'rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500/20 transition-all duration-200'])
                ->prefixIcon('heroicon-o-lock-closed')
                ->placeholder(__('Confirm your password'))
        ])->statePath('form');
    }
    /**
     * Handle register functionality with proper error handling.
     * @return void
     */
    public function register(): void
    {
        $this->registrationForm->register();
        $this->redirect(route('account', absolute: false), navigate: true);
    }
    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    public function render(): View
    {
        return view('livewire.auth.register');
    }
}