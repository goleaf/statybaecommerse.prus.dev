<?php

declare(strict_types=1);

use App\Filament\Imports\BrandImporter;
use App\Filament\Imports\CategoryImporter;
use App\Filament\Imports\DiscountImporter;
use App\Filament\Imports\OrderImporter;
use App\Filament\Imports\PartnerImporter;
use App\Filament\Imports\PriceImporter;
use App\Filament\Imports\ProductImporter;
use App\Filament\Imports\SubscriberImporter;
use App\Filament\Imports\UserImporter;
use Filament\Actions\Imports\Models\Import;

it('runs filament imports on the sync connection', function () {
    $import = new Import;

    $importers = [
        BrandImporter::class,
        CategoryImporter::class,
        DiscountImporter::class,
        OrderImporter::class,
        PartnerImporter::class,
        PriceImporter::class,
        ProductImporter::class,
        SubscriberImporter::class,
        UserImporter::class,
    ];

    foreach ($importers as $importerClass) {
        $importer = new $importerClass($import, [], []);

        expect($importer->getJobConnection())->toBe('sync');
    }
});
