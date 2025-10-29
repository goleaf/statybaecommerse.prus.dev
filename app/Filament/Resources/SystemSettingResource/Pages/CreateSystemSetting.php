<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingResource\Pages;

use App\Filament\Resources\SystemSettingResource;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Resources\Pages\CreateRecord;

use function str_contains;

class CreateSystemSetting extends CreateRecord
{
    use InteractsWithActions {
        mountAction as protected traitMountAction;
    }

    protected static string $resource = SystemSettingResource::class;

    /**
     * Capture the most recent schema component key so testing macros can rehydrate
     * the create-option action context between chained helper invocations.
     */
    public ?string $lastSchemaComponentForTesting = null;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Bridge the legacy table action test helpers to the form component action
     * API so schema-powered select fields can surface the create option modal
     * during automated tests.
     *
     * @param array<string, mixed> $arguments
     */
    public function mountedTableAction(string $name, ?string $component = null, array $arguments = []): mixed
    {
        $schemaComponent = $component ?? 'form';
        $schemaName = $this->getDefaultTestingSchemaName() ?? 'form';

        if ($component !== null && ! str_contains($component, '.')) {
            $this->lastSchemaComponentForTesting = sprintf('%s.%s', $schemaName, $component);
        } else {
            $this->lastSchemaComponentForTesting = $component ?? $schemaName;
        }

        return $this->mountFormComponentAction($component ?? 'form', $name, $arguments);
    }

    /**
     * Surface select create-option modals through the test table helpers.
     *
     * @param array<string, mixed> $arguments
     * @param array<string, mixed> $context
     */
    public function mountAction(string $name, array $arguments = [], array $context = []): mixed
    {
        if (($context['table'] ?? false) && ($context['recordKey'] ?? null)) {
            $schemaName = $this->getDefaultTestingSchemaName() ?? 'form';
            $this->lastSchemaComponentForTesting = sprintf('%s.%s', $schemaName, (string) $context['recordKey']);

            $this->traitMountAction($name, $arguments, [
                ...$context,
                'schemaComponent' => $this->lastSchemaComponentForTesting,
            ]);

            return null;
        }

        return $this->traitMountAction($name, $arguments, $context);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values
        $data['is_active'] ??= true;
        $data['sort_order'] ??= 0;
        $data['group'] ??= 'general';
        // Preserve the raw name attribute so the validator can flag missing labels before insert.
        $data['cache_ttl'] ??= 3600;
        $data['environment'] ??= 'all';
        $data['is_public'] ??= false;
        $data['is_required'] ??= false;
        $data['is_encrypted'] ??= false;
        $data['is_readonly'] ??= false;

        return $data;
    }
}
