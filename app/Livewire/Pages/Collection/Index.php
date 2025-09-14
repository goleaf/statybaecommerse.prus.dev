<?php

declare (strict_types=1);
namespace App\Livewire\Pages\Collection;

use App\Models\Collection as CollectionModel;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
/**
 * Index
 * 
 * Livewire component for Index with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 */
#[Layout('layouts.templates.app')]
class Index extends Component
{
    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    public function render(): View
    {
        abort_if(!app_feature_enabled('collection'), 404);
        $collections = CollectionModel::query()->where('is_visible', true)->orderBy('name')->get();
        return view('livewire.pages.collection.index', ['collections' => $collections])->title(__('Collections'));
    }
}