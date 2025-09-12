<?php declare(strict_types=1);

namespace App\Livewire\Pages\Location;

use App\Models\Location;
use Livewire\Component;

final class Show extends Component
{
    public Location $location;

    public function mount(int $id): void
    {
        $this->location = Location::where('id', $id)
            ->where('is_enabled', true)
            ->with(['country'])
            ->firstOrFail();
    }

    public function render()
    {
        return view('livewire.pages.location.show', [
            'location' => $this->location,
        ])->layout('components.layouts.base', [
            'title' => $this->location->name . ' - ' . __('translations.locations')
        ]);
    }
}
