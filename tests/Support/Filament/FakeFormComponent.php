<?php

declare(strict_types=1);

namespace Tests\Support\Filament;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Component;

/**
 * Tiny Livewire component that satisfies Filament's HasForms contract so tests can instantiate Form objects.
 */
final class FakeFormComponent extends Component implements HasForms
{
    use InteractsWithForms;

    /**
     * Render a minimal view to keep Livewire content drivers happy during unit tests.
     */
    public function render()
    {
        return view('filament::components.badge')->with(['badge' => '']);
    }
}
