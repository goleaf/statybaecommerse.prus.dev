<?php

declare(strict_types=1);

use Laravel\Sanctum\Sanctum;

it('unit: sanctum stateful domains returns a string list', function (): void {
    $stateful = config('sanctum.stateful');
    expect($stateful)->toBeArray()->toContain('127.0.0.1');
});

