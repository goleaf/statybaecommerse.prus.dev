<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductVariantResource\Pages;

use App\Filament\Resources\ProductVariantResource;
use App\Filament\Resources\ProductVariantResource\Schemas\ProductVariantForm;
use App\Models\Product;
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

        $data = $this->syncLocalizationFromParentProduct($data);

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

    private function syncLocalizationFromParentProduct(array $data): array
    {
        $productId = $data['product_id'] ?? $this->record->product_id ?? null;

        if (! is_numeric($productId)) {
            return $data;
        }

        $table = (new ProductVariant)->getTable();

        if (! Schema::hasTable($table)) {
            return $data;
        }

        $columns = [
            'variant_name_lt',
            'variant_name_en',
            'description_lt',
            'description_en',
        ];

        $availableColumns = array_filter(
            $columns,
            static fn (string $column): bool => Schema::hasColumn($table, $column),
        );

        if ($availableColumns === []) {
            return $data;
        }

        $product = Product::query()
            ->withoutGlobalScopes()
            ->find((int) $productId);

        if (! $product instanceof Product) {
            return $data;
        }

        $resolvedValues = [
            'variant_name_lt' => self::normalizeNullableString($product->getTranslatedName('lt')),
            'variant_name_en' => self::normalizeNullableString($product->getTranslatedName('en')),
            'description_lt' => self::normalizeNullableString($product->getTranslatedDescription('lt')),
            'description_en' => self::normalizeNullableString($product->getTranslatedDescription('en')),
        ];

        foreach ($availableColumns as $column) {
            $data[$column] = $resolvedValues[$column] ?? null;
        }

        return $data;
    }

    private static function normalizeNullableString(?string $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }
}
