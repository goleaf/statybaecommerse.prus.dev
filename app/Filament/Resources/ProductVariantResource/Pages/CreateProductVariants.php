<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductVariantResource\Pages;

use App\Filament\Resources\ProductVariantResource;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Filament\Resources\Pages\CreateRecord;

class CreateProductVariants extends CreateRecord
{
    protected static string $resource = ProductVariantResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $table = (new ProductVariant())->getTable();

        if (! Schema::hasTable($table)) {
            return $data;
        }

        $columns = array_flip(Schema::getColumnListing($table));

        // Prevent SQL errors in partially migrated environments by persisting only
        // columns that currently exist in the underlying product_variants table.
        $filteredData = array_intersect_key($data, $columns);

        foreach (['track_inventory', 'allow_backorder', 'is_enabled', 'is_default_variant', 'is_featured', 'is_new', 'is_bestseller'] as $booleanField) {
            if (! array_key_exists($booleanField, $filteredData)) {
                continue;
            }

            $normalizedValue = filter_var($filteredData[$booleanField], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
            $filteredData[$booleanField] = $normalizedValue ?? (bool) $filteredData[$booleanField];
        }

        if (array_key_exists('status', $columns) && ! array_key_exists('status', $filteredData)) {
            $filteredData['status'] = 'active';
        }

        if (array_key_exists('is_enabled', $columns) && ! array_key_exists('is_enabled', $filteredData)) {
            $filteredData['is_enabled'] = true;
        }

        if (array_key_exists('available_quantity', $columns) && ! array_key_exists('available_quantity', $filteredData)) {
            $stockQuantity = (int) ($filteredData['stock_quantity'] ?? 0);
            $reservedQuantity = (int) ($filteredData['reserved_quantity'] ?? 0);
            $filteredData['available_quantity'] = max(0, $stockQuantity - $reservedQuantity);
        }

        return $filteredData;
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
}
