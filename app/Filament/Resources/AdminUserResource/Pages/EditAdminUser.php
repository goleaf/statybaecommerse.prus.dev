<?php

declare(strict_types=1);

namespace App\Filament\Resources\AdminUserResource\Pages;

use App\Filament\Resources\AdminUserResource;
use App\Models\AdminUser as AdminUserModel;
use App\Models\AuditTrail;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditAdminUser extends EditRecord
{
    protected static string $resource = AdminUserResource::class;

    /** @var array<int, int> */
    private array $originalRoleIds = [];

    /** @var array<int, string> */
    private array $originalRoleNames = [];

    /** @var array<int, int> */
    private array $pendingRoleIds = [];

    private ?string $auditReason = null;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        /** @var AdminUserModel $admin */
        $admin = $this->record;

        $originalRoleIds = $admin->roles()->pluck('id')->all();
        /** @var array<int, int|string> $originalRoleIds */
        $originalRoleIds = array_values($originalRoleIds);
        $this->originalRoleIds = $this->normalizeIds($originalRoleIds);

        $this->originalRoleNames = $this->normalizeRoleNames($admin->roles()->pluck('name')->all());
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->auditReason = isset($data['audit_reason']) && is_string($data['audit_reason'])
            ? trim($data['audit_reason'])
            : null;

        $roles = $data['roles'] ?? [];
        $this->pendingRoleIds = $this->normalizeIds(is_array($roles) ? $roles : []);

        unset($data['audit_reason']);

        return $data;
    }

    protected function beforeSave(): void
    {
        if ($this->rolesChanged($this->pendingRoleIds) && blank($this->auditReason)) {
            throw ValidationException::withMessages([
                'audit_reason' => __('admin.audit_trails.validation.reason_required'),
            ]);
        }
    }

    protected function afterSave(): void
    {
        /** @var AdminUserModel $admin */
        $admin = $this->record;

        $freshRoleIdsRaw = $admin->roles()->pluck('id')->all();
        /** @var array<int, int|string> $freshRoleIdsRaw */
        $freshRoleIdsRaw = array_values($freshRoleIdsRaw);
        $freshRoleIds = $this->normalizeIds($freshRoleIdsRaw);

        if (! $this->rolesChanged($freshRoleIds)) {
            return;
        }

        $freshRoleNames = $this->normalizeRoleNames($admin->roles()->pluck('name')->all());

        AuditTrail::record($admin, [
            'roles' => [
                'previous' => $this->originalRoleNames,
                'current' => $freshRoleNames,
            ],
        ], 'admin_user.roles.updated', $this->auditReason);

        $this->originalRoleIds = $freshRoleIds;
        $this->originalRoleNames = $freshRoleNames;
        $this->pendingRoleIds = [];
        $this->auditReason = null;
    }

    /**
     * @param  array<int, int>|null  $ids
     */
    private function rolesChanged(?array $ids = null): bool
    {
        $ids ??= $this->pendingRoleIds;

        return $ids !== $this->originalRoleIds;
    }

    /**
     * @param  array<mixed>  $ids
     * @return array<int, int>
     */
    private function normalizeIds(array $ids): array
    {
        $normalized = [];

        foreach ($ids as $id) {
            if (is_int($id) || is_string($id)) {
                $normalized[] = (int) $id;
            }
        }

        sort($normalized);

        return array_values(array_unique($normalized));
    }

    /**
     * @param  array<mixed>  $names
     * @return array<int, string>
     */
    private function normalizeRoleNames(array $names): array
    {
        $normalized = [];

        foreach ($names as $name) {
            if (is_string($name)) {
                $normalized[] = $name;
            }
        }

        sort($normalized);

        return array_values(array_unique($normalized));
    }
}
