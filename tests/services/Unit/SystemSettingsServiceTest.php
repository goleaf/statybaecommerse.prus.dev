<?php

declare(strict_types=1);

use App\Services\SystemSettingsService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

uses(TestCase::class);

it('gets values from cached settings and falls back to default', function () {
    Cache::shouldReceive('remember')->twice()->andReturn(['site_name' => 'Statyba']);

    $svc = app(SystemSettingsService::class);

    expect($svc->get('site_name'))->toBe('Statyba')
        ->and($svc->get('missing', 'x'))->toBe('x');
});

it('reads public settings via cached method', function () {
    // First call for public cache key
    Cache::shouldReceive('remember')->once()->andReturn(['public_key' => '1']);

    $svc = app(SystemSettingsService::class);

    expect($svc->getPublic('public_key'))->toBe('1');
});
