<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantInventoryResource\Pages;

use App\Filament\Resources\VariantInventoryResource;
use App\Models\AuditTrail;
use App\Models\VariantInventory as VariantInventoryModel;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class EditVariantInventory extends EditRecord
{
    protected static string $resource = VariantInventoryResource::class;

    /** @var array<string, mixed> */
    private array $originalAuditData = [];

    /** @var array<string, mixed> */
    private array $pendingAuditValues = [];

    private ?string $auditReason = null;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        /** @var VariantInventoryModel $inventory */
        $inventory = $this->record;

        $this->originalAuditData = $inventory->only($this->auditedAttributes());
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = parent::mutateFormDataBeforeSave($data);

        $this->auditReason = isset($data['audit_reason']) && is_string($data['audit_reason'])
            ? trim($data['audit_reason'])
            : null;

        /** @var array<string, mixed> $audited */
        $audited = Arr::only($data, $this->auditedAttributes());
        $this->pendingAuditValues = $audited;

        unset($data['audit_reason']);

        return $data;
    }

    protected function beforeSave(): void
    {
        if ($this->pendingDiffRequiresReason() && blank($this->auditReason)) {
            throw ValidationException::withMessages([
                'audit_reason' => __('admin.audit_trails.validation.reason_required'),
            ]);
        }
    }

    protected function afterSave(): void
    {
        /** @var VariantInventoryModel $inventory */
        $inventory = $this->record;

        $fresh = $inventory->fresh();
        if (! $fresh instanceof VariantInventoryModel) {
            return;
        }

        $current = $fresh->only($this->auditedAttributes());
        $diff = AuditTrail::diff($this->originalAuditData, $current);

        if ($diff === []) {
            return;
        }

        AuditTrail::record($fresh, $diff, 'inventory.updated', $this->auditReason);

        $this->originalAuditData = $current;
        $this->pendingAuditValues = [];
        $this->auditReason = null;
    }

    /**
     * @return array<int, string>
     */
    private function auditedAttributes(): array
    {
        return [
            'stock',
            'reserved',
            'available',
            'incoming',
            'threshold',
            'reorder_point',
            'reorder_quantity',
            'max_stock_level',
            'status',
        ];
    }

    private function pendingDiffRequiresReason(): bool
    {
        if ($this->pendingAuditValues === []) {
            return false;
        }

        $after = array_replace($this->originalAuditData, $this->pendingAuditValues);

        return AuditTrail::diff($this->originalAuditData, $after) !== [];
    }
}
