<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

test('language files are normalized to lowercase snake case php files', function () {
    $langRoot = lang_path();
    $localeDirs = File::directories($langRoot);

    expect($localeDirs)->not()->toBeEmpty();

    foreach ($localeDirs as $localeDir) {
        $subdirs = File::directories($localeDir);
        expect($subdirs)->toBeEmpty();

        $files = File::files($localeDir);
        foreach ($files as $file) {
            expect($file->getExtension())->toBe('php');
            expect($file->getFilename())->toMatch('/^[a-z0-9_]+\\.php$/');
        }
    }
});

test('product variants are merged into product group', function () {
    $localeDirs = File::directories(lang_path());

    foreach ($localeDirs as $localeDir) {
        expect(File::exists($localeDir . DIRECTORY_SEPARATOR . 'product_variants.php'))->toBeFalse();
        expect(File::exists($localeDir . DIRECTORY_SEPARATOR . 'productvariant.php'))->toBeFalse();

        $productPath = $localeDir . DIRECTORY_SEPARATOR . 'product.php';
        expect(File::exists($productPath))->toBeTrue();

        $product = require $productPath;
        expect($product)->toBeArray();
        expect($product)->toHaveKey('variants');
        expect($product['variants'])->toBeArray();
    }
});

test('resources lang directory contains no json files', function () {
    $jsonFiles = File::glob(resource_path('lang/*.json'));
    expect($jsonFiles)->toBeEmpty();
});
