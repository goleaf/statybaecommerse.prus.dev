<?php

declare (strict_types=1);
namespace App\Livewire\Components;

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
/**
 * AccountMenu
 * 
 * Livewire component for AccountMenu with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 */
class AccountMenu extends Component
{
    /**
     * Handle isAuthenticated functionality with proper error handling.
     * @return bool
     */
    #[Computed]
    public function isAuthenticated(): bool
    {
        return Auth::check();
    }
    /**
     * Handle user functionality with proper error handling.
     * @return mixed
     */
    #[Computed]
    public function user(): mixed
    {
        return Auth::user();
    }
    /**
     * Handle logout functionality with proper error handling.
     * @param Logout $logout
     * @return void
     */
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        return view('livewire.components.account-menu');
    }
}