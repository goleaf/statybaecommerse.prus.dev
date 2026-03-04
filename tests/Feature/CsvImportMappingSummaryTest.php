<?php

declare(strict_types=1);

use App\Filament\Imports\ProductImporter;
use App\Filament\Pages\Imports\CsvImportPage;

class MappingSummaryPage extends CsvImportPage
{
    protected static function getImporterClass(): string
    {
        return ProductImporter::class;
    }

    protected static function getImportLabel(): string
    {
        return 'Test Import';
    }

    public function mappingSummary(array $headers, array $columnMap): string
    {
        return $this->getMappingStatusContent($headers, $columnMap)->toHtml();
    }
}

it('shows missing required mappings', function () {
    $page = new MappingSummaryPage;
    $html = $page->mappingSummary(['Name', 'SKU'], [
        'name' => null,
        'sku'  => 'SKU',
    ]);

    expect($html)->toContain(__('admin.import_mapping_missing_required'));
});

it('shows duplicate mapping errors', function () {
    $page = new MappingSummaryPage;
    $html = $page->mappingSummary(['Name', 'SKU'], [
        'name' => 'Name',
        'sku'  => 'Name',
    ]);

    expect($html)->toContain(__('admin.import_mapping_duplicate_column', [
        'column' => 'Name',
    ]));
});

it('shows success when mappings are valid', function () {
    $page = new MappingSummaryPage;
    $html = $page->mappingSummary(['Name', 'SKU'], [
        'name' => 'Name',
        'sku'  => 'SKU',
    ]);

    expect($html)->toContain(__('admin.import_mapping_ok'));
});
