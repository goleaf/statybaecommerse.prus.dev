<?php

declare(strict_types=1);

use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\View;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

uses(TestCase::class);

/**
 * Ensure the Livewire testing helpers stay operational even when only the
 * lightweight example placeholder component is available.
 */
it('renders and updates the example Livewire placeholder component', function (): void {
    $componentClass = new class extends Component
    {
        public int $count = 0;

        public function increment(): void
        {
            // Mutate state so the test can assert Livewire data binding works.
            $this->count++;
        }

        public function render(): ViewContract
        {
            // Point to the dedicated placeholder view shipped alongside the test.
            return View::make('livewire.example-placeholder');
        }
    };

    $component = Livewire::test($componentClass)
        ->call('increment');

    // Assert the component state mutates as expected after invoking the action.
    $component->assertSet('count', 1);
});
