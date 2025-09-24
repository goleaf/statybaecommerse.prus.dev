<?php

declare(strict_types=1);

use App\Services\EnumService;
use Tests\TestCase;

uses(TestCase::class);

it('returns enum class by name and options safely', function () {
    $svc = app(EnumService::class);

    $class = $svc->getEnum('order_statuses');
    expect(is_string($class) || $class === null)->toBeTrue();

    $options = $svc->getEnumOptions('order_statuses');
    expect($options)->toBeArray();
});

it('provides getForUseCase data without errors', function () {
    $svc = app(EnumService::class);
    $data = $svc->getForUseCase('api');

    expect($data)->toBeArray();
});
