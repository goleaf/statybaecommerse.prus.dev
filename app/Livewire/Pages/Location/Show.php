<?php

declare (strict_types=1);
namespace App\Livewire\Pages\Location;

use App\Models\Location;
use Livewire\Component;
/**
 * Show
 * 
 * Livewire component for Show with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property Location $location
 */
final class Show extends Component
{
    public Location $location;
    /**
     * Initialize the Livewire component with parameters.
     * @param string $slug
     * @return void
     */
    public function mount(string $slug): void
    {
        $this->location = Location::where('code', $slug)->orWhere('name', $slug)->where('is_enabled', true)->with(['country'])->firstOrFail();
    }
    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        return view('livewire.pages.location.show', ['location' => $this->location])->layout('components.layouts.base', ['title' => $this->location->name . ' - ' . __('translations.locations')]);
    }
}