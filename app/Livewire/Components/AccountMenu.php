<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Livewire\Actions\Logout;
use Livewire\Component;

/**
 * AccountMenu
 * 
 * Livewire component for reactive frontend functionality.
 */
class AccountMenu extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }

    public function render()
    {
        return view('livewire.components.account-menu');
    }
}
