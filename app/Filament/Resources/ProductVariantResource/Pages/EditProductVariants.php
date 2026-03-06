<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductVariantResource\Pages;

use App\Filament\Resources\ProductVariantResource;
use App\Filament\Resources\ProductVariantResource\Schemas\ProductVariantForm;
use App\Models\ProductVariant;
use App\Services\ProductVariantAttributeMatrixService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Schema;

class EditProductVariants extends EditRecord
{
    protected static string $resource = ProductVariantResource::class;

    /**
     * @var array<int, array{attribute_id:int, attribute_value_id:int}>
     */
    private array $pendingAttributeSelections = [];

    /**
     * @var array<string, int>
     */
    private array $pendingVariantAttributeMatrix = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->record;

        if (! $record instanceof ProductVariant) {
            return $data;
        }

        $data[ProductVariantForm::ATTRIBUTE_SELECTIONS_FIELD] = ProductVariantForm::buildSelectionStateFromRecord($record);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $selectionsState = $data[ProductVariantForm::ATTRIBUTE_SELECTIONS_FIELD] ?? [];
        unset($data[ProductVariantForm::ATTRIBUTE_SELECTIONS_FIELD]);

        $this->pendingAttributeSelections = ProductVariantForm::normalizeAttributeSelections($selectionsState);
        $this->pendingVariantAttributeMatrix = ProductVariantForm::buildAttributeMatrixPayload($this->pendingAttributeSelections);
        $legacyAttributes = ProductVariantForm::buildLegacyAttributesPayload($this->pendingAttributeSelections);

        $table = (new ProductVariant)->getTable();

        if (Schema::hasTable($table) && Schema::hasColumn($table, 'variant_attribute_matrix')) {
            $data['variant_attribute_matrix'] = $this->pendingVariantAttributeMatrix !== []
                ? $this->pendingVariantAttributeMatrix
                : null;
        }

        if (Schema::hasTable($table) && Schema::hasColumn($table, 'attributes')) {
            $data['attributes'] = $legacyAttributes !== [] ? $legacyAttributes : null;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->record;

        if (! $record instanceof ProductVariant) {
            return;
        }

        if (
            ! Schema::hasTable('product_variant_attributes')
            || ! Schema::hasTable('variant_attribute_values')
            || ! Schema::hasTable('attribute_values')
        ) {
            return;
        }

        ProductVariantAttributeMatrixService::sync(
            $record,
            $this->pendingVariantAttributeMatrix,
            $this->pendingAttributeSelections,
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        $redirectUrl = request()->query('redirect');

        if (is_string($redirectUrl) && $redirectUrl !== '') {
            return $redirectUrl;
        }

        $parentRedirectUrl = parent::getRedirectUrl();

        if (is_string($parentRedirectUrl) && $parentRedirectUrl !== '') {
            return $parentRedirectUrl;
        }

        return ProductVariantResource::getUrl('index');
    }
}
