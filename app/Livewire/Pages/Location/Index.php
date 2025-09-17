<?php

declare (strict_types=1);
namespace App\Livewire\Pages\Location;

use App\Models\Location;
use Livewire\Component;
/**
 * Index
 * 
 * Livewire component for Index with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 */
final class Index extends Component
{
    /**
     * Handle getLocationsProperty functionality with proper error handling.
     */
    public function getLocationsProperty()
    {
        return Location::where('is_enabled', true)->with(['country'])->orderBy('name')->get();
    }
    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        return view('livewire.pages.location.index', ['locations' => $this->locations])->layout('components.layouts.base', ['title' => __('translations.locations')]);
    }
}