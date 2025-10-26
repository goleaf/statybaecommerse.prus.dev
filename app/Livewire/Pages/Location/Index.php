<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Location;

use App\Models\Location;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Index
 *
 * Livewire component for Index with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property-read Collection<int, Location> $locations
 */
final class Index extends Component
{
    /**
     * Handle getLocationsProperty functionality with proper error handling.
     */
    /**
     * @return Collection<int, Location>
     */
    public function getLocationsProperty(): Collection
    {
        // Only return locations that include latitude/longitude coordinates so any
        // client consuming this accessor can reliably plot each entry on a map.
        return Location::where('is_enabled', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with(['country'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        /** @var View $view */
        $view = view('livewire.pages.location.index', ['locations' => $this->locations]);

        /** @var View $layoutView */
        $layoutView = $view->layout('components.layouts.base', ['title' => __('translations.locations')]);

        return $layoutView;
    }
}
