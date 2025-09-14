<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Location;

use App\Models\Location;
use Livewire\Component;

final /**
 * Index
 * 
 * Livewire component for reactive frontend functionality.
 */
class Index extends Component
{
    public function getLocationsProperty()
    {
        return Location::where('is_enabled', true)
            ->with(['country'])
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.pages.location.index', [
            'locations' => $this->locations,
        ])->layout('components.layouts.base', [
            'title' => __('translations.locations'),
        ]);
    }
}
