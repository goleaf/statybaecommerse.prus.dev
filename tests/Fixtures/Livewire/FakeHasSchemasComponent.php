<?php

declare(strict_types=1);

namespace Tests\Fixtures\Livewire;

use App\Support\Filament\Components\SearchableInput;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Component;

/**
 * Minimal HasSchemas Livewire component used to exercise SearchableInput macros in unit tests.
 */
final class FakeHasSchemasComponent extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    /**
     * Expose a single searchable input so tests can bootstrap the component container without
     * instantiating a full Filament resource.
     */
    public function schema(Schema $schema): Schema
    {
        return $schema->components([
            SearchableInput::make('lookup')
                ->placeholder('Lookup value')
                ->fallbackPayload([]),
        ]);
    }

    public function render(): string
    {
        return '';
    }
}
