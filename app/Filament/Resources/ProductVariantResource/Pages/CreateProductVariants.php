<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductVariantResource\Pages;

use App\Filament\Resources\ProductVariantResource;
use App\Filament\Resources\ProductVariantResource\RelationManagers\AttributesRelationManager;
use App\Filament\Resources\ProductVariantResource\Schemas\ProductVariantForm;
use App\Models\ProductVariant;
use App\Services\ProductVariantAttributeMatrixService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class CreateProductVariants extends CreateRecord
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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $selectionsState = $data[ProductVariantForm::ATTRIBUTE_SELECTIONS_FIELD] ?? [];
        unset($data[ProductVariantForm::ATTRIBUTE_SELECTIONS_FIELD]);

        $this->pendingAttributeSelections = ProductVariantForm::normalizeAttributeSelections($selectionsState);
        $this->pendingVariantAttributeMatrix = ProductVariantForm::buildAttributeMatrixPayload($this->pendingAttributeSelections);
        $legacyAttributes = ProductVariantForm::buildLegacyAttributesPayload($this->pendingAttributeSelections);

        if ($this->pendingVariantAttributeMatrix !== []) {
            $data['variant_attribute_matrix'] = $this->pendingVariantAttributeMatrix;
        } elseif (! array_key_exists('variant_attribute_matrix', $data)) {
            $data['variant_attribute_matrix'] = null;
        }

        if ($legacyAttributes !== []) {
            $data['attributes'] = $legacyAttributes;
        } elseif (! array_key_exists('attributes', $data)) {
            $data['attributes'] = null;
        }

        if (! is_numeric($data['product_id'] ?? null)) {
            $requestedProductId = request()->integer('product_id');

            if ($requestedProductId > 0) {
                $data['product_id'] = $requestedProductId;
            }
        }

        $table = (new ProductVariant)->getTable();

        if (! Schema::hasTable($table)) {
            return $data;
        }

        $columns = array_flip(Schema::getColumnListing($table));

        // Prevent SQL errors in partially migrated environments by persisting only
        // columns that currently exist in the underlying product_variants table.
        $filteredData = array_intersect_key($data, $columns);

        $booleanDefaults = [
            'track_inventory'    => true,
            'allow_backorder'    => false,
            'is_enabled'         => true,
            'is_default_variant' => false,
            'is_featured'        => false,
            'is_new'             => false,
            'is_bestseller'      => false,
        ];

        foreach ($booleanDefaults as $booleanField => $defaultValue) {
            if (! array_key_exists($booleanField, $columns)) {
                continue;
            }

            if (
                ! array_key_exists($booleanField, $filteredData)
                || $filteredData[$booleanField] === null
                || $filteredData[$booleanField] === ''
            ) {
                $filteredData[$booleanField] = $defaultValue;

                continue;
            }

            $normalizedValue = filter_var($filteredData[$booleanField], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
            $filteredData[$booleanField] = $normalizedValue ?? $defaultValue;
        }

        if (array_key_exists('status', $columns) && ! array_key_exists('status', $filteredData)) {
            $filteredData['status'] = 'active';
        }

        if (array_key_exists('available_quantity', $columns) && ! array_key_exists('available_quantity', $filteredData)) {
            $stockQuantity = (int) ($filteredData['stock_quantity'] ?? 0);
            $reservedQuantity = (int) ($filteredData['reserved_quantity'] ?? 0);
            $filteredData['available_quantity'] = max(0, $stockQuantity - $reservedQuantity);
        }

        return $filteredData;
    }

    protected function afterCreate(): void
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

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return parent::handleRecordCreation($data);
        } catch (UniqueConstraintViolationException $exception) {
            if (str_contains(strtolower($exception->getMessage()), 'sku')) {
                throw ValidationException::withMessages([
                    'sku' => __('validation.unique', [
                        'attribute' => __('messages.sku'),
                    ]),
                ]);
            }

            throw $exception;
        }
    }

    protected function getRedirectUrl(): string
    {
        $parameters = [
            'record' => $this->getRecord(),
        ];

        $redirectUrl = request()->query('redirect');

        if (is_string($redirectUrl) && $redirectUrl !== '') {
            $parameters['redirect'] = $redirectUrl;
        }

        $relationTabKey = $this->resolveAttributesRelationTabKey();

        if ($relationTabKey !== null) {
            $parameters['relation'] = $relationTabKey;
        }

        return ProductVariantResource::getUrl('edit', $parameters);
    }

    private function resolveAttributesRelationTabKey(): ?string
    {
        $relationKey = array_search(AttributesRelationManager::class, ProductVariantResource::getRelations(), true);

        if ($relationKey === false) {
            return null;
        }

        return (string) $relationKey;
    }
}
