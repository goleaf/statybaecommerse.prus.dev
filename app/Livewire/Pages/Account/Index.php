<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Account;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.pages.account.index')
            ->layout('components.layouts.templates.account')
            ->title(__('Overview'));
    }
}
