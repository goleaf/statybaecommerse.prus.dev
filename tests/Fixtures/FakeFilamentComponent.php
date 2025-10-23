<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Contracts\TranslatableContentDriver;
use Livewire\Component as LivewireComponent;

/**
 * Minimal Livewire component that satisfies Filament's HasSchemas contract for unit tests.
 */
#[\AllowDynamicProperties]
final class FakeFilamentComponent extends LivewireComponent implements HasSchemas
{
    /**
     * The test double never provides translated content drivers.
     */
    public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
    {
        return null;
    }

    /**
     * Allow Filament helpers to read any previously persisted state.
     */
    public function getOldSchemaState(string $statePath): mixed
    {
        return data_get($this, $statePath);
    }

    /**
     * Component lookup is not required for the unit tests, so we return null.
     */
    public function getSchemaComponent(
        string $key,
        bool $withHidden = false,
        ?Component $skipComponentChildContainersWhileSearching = null
    ): Component | Action | ActionGroup | null {
        return null;
    }

    /**
     * Unit tests do not request named schemas, therefore we return null.
     */
    public function getSchema(string $name): ?Schema
    {
        return null;
    }

    /**
     * Validation lifecycle hooks are unnecessary for these tests.
     */
    public function currentlyValidatingSchema(?Schema $schema): void
    {
        // No-op for the fake component.
    }

    /**
     * The fake Livewire component has no default testing schema name.
     */
    public function getDefaultTestingSchemaName(): ?string
    {
        return null;
    }
}
