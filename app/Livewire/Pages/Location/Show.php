<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Location;

use App\Models\Location;
use Livewire\Component;

final /**
 * Show
 * 
 * Livewire component for reactive frontend functionality.
 */
class Show extends Component
{
    public Location $location;

    public function mount(string $slug): void
    {
        $this->location = Location::where('code', $slug)
            ->orWhere('name', $slug)
            ->where('is_enabled', true)
            ->with(['country'])
            ->firstOrFail();
    }

    public function render()
    {
        return view('livewire.pages.location.show', [
            'location' => $this->location,
        ])->layout('components.layouts.base', [
            'title' => $this->location->name.' - '.__('translations.locations'),
        ]);
    }
}
