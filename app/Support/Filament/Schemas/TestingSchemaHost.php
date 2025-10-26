<?php

declare(strict_types=1);

namespace App\Support\Filament\Schemas;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Contracts\TranslatableContentDriver;
use Livewire\Component as LivewireComponent;

/**
 * Lightweight Livewire host so schemas can operate in testing contexts without a full Filament component.
 */
final class TestingSchemaHost extends LivewireComponent implements HasSchemas
{
    public function render(): string
    {
        return '';
    }

    public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
    {
        return null;
    }

    public function getOldSchemaState(string $statePath): mixed
    {
        return null;
    }

    public function getSchemaComponent(string $key, bool $withHidden = false, ?Component $skipComponentChildContainersWhileSearching = null): Component|Action|ActionGroup|null
    {
        return null;
    }

    public function getSchema(string $name): ?Schema
    {
        return null;
    }

    public function currentlyValidatingSchema(?Schema $schema): void
    {
        // Intentionally left blank; validation orchestration is not needed in tests.
    }

    public function getDefaultTestingSchemaName(): ?string
    {
        return null;
    }
}
