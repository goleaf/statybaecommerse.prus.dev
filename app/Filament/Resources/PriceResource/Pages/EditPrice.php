<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceResource\Pages;

use App\Filament\Resources\PriceResource;
use App\Models\AuditTrail;
use App\Models\Price as PriceModel;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

final class EditPrice extends EditRecord
{
    protected static string $resource = PriceResource::class;

    /** @var array<string, mixed> */
    private array $originalAuditData = [];

    /** @var array<string, mixed> */
    private array $pendingAuditValues = [];

    private ?string $auditReason = null;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        /** @var PriceModel $price */
        $price = $this->record;

        $this->originalAuditData = $price->only($this->auditedAttributes());
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
        /** @var PriceModel $price */
        $price = $this->record;

        $fresh = $price->fresh();
        if (! $fresh instanceof PriceModel) {
            return;
        }

        $current = $fresh->only($this->auditedAttributes());
        $diff = AuditTrail::diff($this->originalAuditData, $current);

        if ($diff === []) {
            return;
        }

        AuditTrail::record($fresh, $diff, 'price.updated', $this->auditReason);

        $this->originalAuditData = $current;
        $this->auditReason = null;
        $this->pendingAuditValues = [];
    }

    /**
     * @return array<int, string>
     */
    private function auditedAttributes(): array
    {
        return ['amount', 'compare_amount', 'cost_amount', 'is_enabled', 'starts_at', 'ends_at'];
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
