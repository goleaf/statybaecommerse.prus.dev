<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductVariantResource\Pages;

use App\Filament\Concerns\InteractsWithTranslationTabs;
use App\Filament\Resources\ProductVariantResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

final class CreateProductVariant extends CreateRecord
{
    use InteractsWithTranslationTabs;

    protected static string $resource = ProductVariantResource::class;

    protected function getTranslatableFields(): array
    {
        return ['name', 'description', 'seo_title', 'seo_description'];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('product_variants.messages.created_successfully'))
            ->body(__('product_variants.messages.created_successfully_description'));
    }

    protected function afterCreate(): void
    {
        $this->syncTranslationRecords($this->record, $this->languageTabsPayload);

        parent::afterCreate();

        $state = $this->form->getState();

        ProductVariantResource::syncVariantAttributeRelations(
            $this->record,
            data_get($state, 'variant_attribute_matrix', []),
            data_get($state, 'attributeValueSelections', []),
        );
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        [$data, $translations] = $this->extractTranslationsFromForm($data);
        $this->languageTabsPayload = $this->filterEmptyTranslations($translations);
        $data = $this->mutateMainDataWithDefaultLocale($data, $this->languageTabsPayload);
        unset($data['description'], $data['seo_title'], $data['seo_description']);

        $defaultLocale = $this->getDefaultLocale();
        if (filled($this->languageTabsPayload[$defaultLocale]['name'] ?? null)) {
            $data['name'] = $this->languageTabsPayload[$defaultLocale]['name'];
        }

        // Generate SKU if not provided
        if (empty($data['sku'])) {
            $data['sku'] = $this->generateSku($data);
        }

        // Set position if not provided
        if (! isset($data['position'])) {
            $data['position'] = $this->getNextPosition((int) $data['product_id']);
        }

        return $data;
    }

    private function generateSku(array $data): string
    {
        $product = \App\Models\Product::find($data['product_id']);
        $baseSku = $product ? $product->sku : 'VAR';
        $size = $data['size'] ?? '';
        $suffix = $data['variant_sku_suffix'] ?? '';

        $sku = $baseSku;
        if ($size) {
            $sku .= '-' . strtoupper($size);
        }
        if ($suffix) {
            $sku .= '-' . strtoupper($suffix);
        }

        // Ensure uniqueness
        $originalSku = $sku;
        $counter = 1;
        while (\App\Models\ProductVariant::where('sku', $sku)->exists()) {
            $sku = $originalSku . '-' . $counter;
            $counter++;
        }

        return $sku;
    }

    private function getNextPosition(int $productId): int
    {
        $maxPosition = \App\Models\ProductVariant::where('product_id', $productId)
            ->max('position');

        return ($maxPosition ?? 0) + 1;
    }
}
