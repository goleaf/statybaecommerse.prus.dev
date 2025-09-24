<?php

declare(strict_types=1);

use App\Services\ProductGalleryService;
use Illuminate\Support\Collection;
use Tests\TestCase;

uses(TestCase::class);

function sampleProducts(int $n = 6): Collection
{
    return collect(range(1, $n))->map(fn ($i) => ['id' => $i, 'name' => 'P'.$i]);
}

it('arranges products for gallery and responsive grid', function () {
    $svc = app(ProductGalleryService::class);
    $gallery = $svc->arrangeForGallery(sampleProducts(), 3);
    $grid = $svc->arrangeForResponsiveGrid(sampleProducts(), 'lg');

    expect($gallery)->toBeInstanceOf(Collection::class)
        ->and($grid)->toBeInstanceOf(Collection::class);
});
