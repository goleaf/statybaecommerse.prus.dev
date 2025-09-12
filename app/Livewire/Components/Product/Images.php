<?php

declare(strict_types=1);

namespace App\Livewire\Components\Product;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class Images extends Component
{
    public ?string $thumbnail = null;

    public array $images = [];

    public int $active = 0;

    public function setActive(int $index): void
    {
        $this->active = max(0, min($index, count($this->images) - 1));
    }

    public function render(): View
    {
        return view('livewire.components.product.images');
    }
}
