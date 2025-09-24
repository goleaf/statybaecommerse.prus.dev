<?php

declare(strict_types=1);

use App\Services\XmlCatalogService;
use Tests\TestCase;

uses(TestCase::class);

it('returns zero counts for missing XML file on import', function () {
    $svc = app(XmlCatalogService::class);
    $res = $svc->import('/non/existent/path/catalog.xml');

    expect($res)->toBeArray()
        ->and($res['categories']['created'])->toBe(0)
        ->and($res['categories']['updated'])->toBe(0)
        ->and($res['products']['created'])->toBe(0)
        ->and($res['products']['updated'])->toBe(0);
});
