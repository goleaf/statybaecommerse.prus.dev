<?php

namespace DefStudio\SearchableInput\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Wrapper extends Component
{
    public function render(): View
    {
        return view('searchable-input::components.wrapper');
    }
}
