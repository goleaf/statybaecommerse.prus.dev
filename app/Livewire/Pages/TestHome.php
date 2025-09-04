<?php declare(strict_types=1);

namespace App\Livewire\Pages;

use Livewire\Component;

final class TestHome extends Component
{
    public function render()
    {
        return view('livewire.pages.test-home')->layout('components.layouts.base', [
            'title' => 'Test Home'
        ]);
    }
}
