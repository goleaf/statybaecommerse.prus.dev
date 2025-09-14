<?php

declare (strict_types=1);
namespace App\Livewire\Components;

use App\Livewire\Actions\Logout;
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