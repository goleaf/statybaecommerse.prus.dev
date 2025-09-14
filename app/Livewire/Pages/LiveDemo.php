<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.base')]
final class LiveDemo extends Component
{
    public function render(): View
    {
        return view('livewire.pages.live-demo');
    }
}
