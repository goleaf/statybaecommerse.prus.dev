<?php

declare(strict_types=1);

use App\Services\VersionCompatibility\FileProcessor;
use App\Services\VersionCompatibilityService;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Filesystem\Filesystem;

describe('VersionCompatibilityService Isolated Tests', function () {
    it('can be instantiated with mocked dependencies', function () {
        $filesystem = Mockery::mock(Filesystem::class);
        $fileProcessor = new FileProcessor($filesystem);
        $cache = Mockery::mock(CacheRepository::class);
        $config = Mockery::mock(ConfigRepository::class);

        $config->shouldReceive('get')
            ->with('version-compatibility.cache.prefix', 'filament_transform')
            ->andReturn('filament_transform');

        $config->shouldReceive('get')
            ->with('version-compatibility.cache.ttl', 3600)
            ->andReturn(3600);

        $service = new VersionCompatibilityService(
            $fileProcessor,
            $filesystem,
            $cache,
            $config
        );

        expect($service)->toBeInstanceOf(VersionCompatibilityService::class);
    });

    it('can get transformation stats', function () {
        $filesystem = Mockery::mock(Filesystem::class);
        $fileProcessor = new FileProcessor($filesystem);
        $cache = Mockery::mock(CacheRepository::class);
        $config = Mockery::mock(ConfigRepository::class);

        $config->shouldReceive('get')
            ->with('version-compatibility.cache.prefix', 'filament_transform')
            ->andReturn('filament_transform');

        $config->shouldReceive('get')
            ->with('version-compatibility.cache.ttl', 3600)
            ->andReturn(3600);

        $service = new VersionCompatibilityService(
            $fileProcessor,
            $filesystem,
            $cache,
            $config
        );

        $stats = $service->getTransformationStats();

        expect($stats)->toBeArray()
            ->and($stats)->toHaveKeys(['available_strategies', 'cache_prefix', 'cache_ttl', 'strategies']);
    });
});
