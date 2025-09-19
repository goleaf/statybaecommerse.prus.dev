<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Account;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.templates.account')]
#[Title('Profile')]
final class Profile extends Component
{
    public function render(): View
    {
        return view('livewire.pages.account.profile');
    }
}
