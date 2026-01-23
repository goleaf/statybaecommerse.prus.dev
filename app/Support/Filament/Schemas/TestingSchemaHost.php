<?php

declare(strict_types=1);

namespace App\Support\Filament\Schemas;

use Filament\Forms\ComponentContainer;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Contracts\TranslatableContentDriver;
use Livewire\Component as LivewireComponent;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Lightweight Livewire host so schemas can operate in testing contexts without a full Filament component.
 */
final class TestingSchemaHost extends LivewireComponent implements HasForms, HasSchemas
{
    use InteractsWithSchemas;

    public function render(): string
    {
        return '';
    }

    public function dispatchFormEvent(mixed ...$args): void {}

    public function getActiveFormsLocale(): ?string
    {
        return null;
    }

    public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
    {
        return null;
    }

    public function getForm(string $name): ?Form
    {
        return null;
    }

    public function getFormComponentFileAttachment(string $statePath): ?TemporaryUploadedFile
    {
        return null;
    }

    public function getFormComponentFileAttachmentUrl(string $statePath): ?string
    {
        return null;
    }

    public function getFormSelectOptionLabels(string $statePath): array
    {
        return [];
    }

    public function getFormSelectOptionLabel(string $statePath): ?string
    {
        return null;
    }

    public function getFormSelectOptions(string $statePath): array
    {
        return [];
    }

    public function getFormSelectSearchResults(string $statePath, string $search): array
    {
        return [];
    }

    public function getFormUploadedFiles(string $statePath): ?array
    {
        return null;
    }

    public function getOldFormState(string $statePath): mixed
    {
        return null;
    }

    public function isCachingForms(): bool
    {
        return false;
    }

    public function removeFormUploadedFile(string $statePath, string $fileKey): void {}

    public function reorderFormUploadedFiles(string $statePath, array $fileKeys): void {}

    public function validate($rules = null, $messages = [], $attributes = []): array
    {
        return [];
    }

    public function currentlyValidatingForm(?ComponentContainer $form): void
    {
        // Intentionally left blank; validation orchestration is not needed in tests.
    }
}
